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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps a `WC_Product` to template-parts/product-card.php's `$args`.
 */
final class ProductCardMapper {

	/**
	 * @param \WC_Product $product Product to map.
	 * @return array See template-parts/product-card.php's documented `$args` keys.
	 */
	public static function map( \WC_Product $product ): array {
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
			'in_wishlist'      => false,
			'swatches'         => array(),
		);
	}
}
