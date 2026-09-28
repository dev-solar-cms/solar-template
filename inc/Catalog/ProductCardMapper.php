<?php
/**
 * Created: 2026-09-25 15:30 CEST
 * Role: WC_Product -> product-card mapping (Solar_Template\Catalog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map a `WC_Product` to the `$args` shape expected by template-parts/product-card.php,
 *          shared by the catalog grid and the front page's featured products section.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Catalog;

use Solar_Template\Account\WishlistRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps a `WC_Product` to template-parts/product-card.php's `$args`.
 */
final class ProductCardMapper {

	/**
	 * Maps a list of products at once, resolving every product's wishlist state with a single batched
	 * query (Account\WishlistRepository::wishlisted_ids_for()) instead of the one-query-per-card
	 * pattern self::map() alone would produce across a grid.
	 *
	 * @param array<int, \WC_Product> $products Products to map.
	 * @return array<int, array> List of template-parts/product-card.php `$args` arrays, same order as
	 *                           $products.
	 */
	public static function map_many( array $products ): array {
		if ( empty( $products ) ) {
			return array();
		}

		$wishlisted_ids = array();

		if ( is_user_logged_in() ) {
			$product_ids    = array_map( static fn( \WC_Product $product ): int => $product->get_id(), $products );
			$wishlisted_ids = ( new WishlistRepository( $GLOBALS['wpdb'] ) )->wishlisted_ids_for( get_current_user_id(), $product_ids );
		}

		return array_map(
			static fn( \WC_Product $product ): array => self::map( $product, in_array( $product->get_id(), $wishlisted_ids, true ) ),
			$products
		);
	}

	/**
	 * @param \WC_Product $product     Product to map.
	 * @param bool        $in_wishlist Whether $product is on the current visitor's wishlist —
	 *                                 resolved once per page by the caller (self::map_many() for a
	 *                                 grid, or a value already known some other way, e.g. every
	 *                                 product on the Wishlist page itself is trivially `true`) rather
	 *                                 than queried again here, to avoid an unbatched query per card.
	 * @return array See template-parts/product-card.php's documented `$args` keys.
	 */
	public static function map( \WC_Product $product, bool $in_wishlist ): array {
		$image_id = $product->get_image_id();
		$price    = $product->get_price();
		$regular  = $product->get_regular_price();

		$discount_percent = null;
		$badge            = null;

		if ( $product->is_on_sale() && '' !== $regular && (float) $regular > 0 ) {
			$discount_percent = (int) round( ( ( (float) $regular - (float) $price ) / (float) $regular ) * 100 );
			$badge            = array(
				'type'  => 'sale',
				'label' => __( 'On Sale', 'solar-template' ),
			);
		}

		$categories = get_the_terms( $product->get_id(), 'product_cat' );
		$category   = ( $categories && ! is_wp_error( $categories ) ) ? reset( $categories )->name : '';

		return array(
			'product_id'       => $product->get_id(),
			'image_url'        => $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : null,
			'image_alt'        => $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '',
			'permalink'        => get_permalink( $product->get_id() ),
			'badge'            => $badge,
			'category'         => $category,
			'name'             => $product->get_name(),
			'price'            => '' !== $price ? (float) $price : null,
			'regular_price'    => '' !== $regular ? (float) $regular : null,
			'currency_symbol'  => get_woocommerce_currency_symbol(),
			'discount_percent' => $discount_percent,
			'in_wishlist'      => $in_wishlist,
			'swatches'         => array(),
		);
	}
}
