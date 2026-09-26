<?php
/**
 * Created: 2026-09-26 11:20 CEST
 * Role: Product page "Related products" section data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the product page's "Related products" section header and its real WooCommerce
 *          related products (same category/tag matching WooCommerce's own native
 *          `wc_get_related_products()` uses), mapped for the existing product card template-part —
 *          same convention as Solar_Template\FrontPage\FeaturedProducts for the home page.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

use Solar_Template\Catalog\ProductCardMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides the product page's "Related products" section header + product query.
 */
final class ProductRelated {

	/**
	 * Returns the product page's "Related products" section header (eyebrow label, heading).
	 *
	 * @return array{eyebrow: string, heading: string}
	 */
	public static function heading(): array {
		$defaults = array(
			'eyebrow' => __( 'Suggestions', 'solar-template' ),
			'heading' => __( 'Related products', 'solar-template' ),
		);

		/**
		 * Filters the product page's "Related products" section header.
		 *
		 * @param array $defaults See this method's return type.
		 */
		return apply_filters( 'solar_template_related_products_heading', $defaults );
	}

	/**
	 * Returns the given product's related products, mapped for template-parts/product-card.php.
	 *
	 * Reads real WooCommerce data (`wc_get_related_products()`, the same category/tag matching
	 * WooCommerce's own native templates use) — returns an empty array when there is no related
	 * product at all, so the calling template-part can skip rendering the section entirely.
	 *
	 * @param \WC_Product $product Product to find related products for.
	 * @param int         $limit   Maximum number of related products to return.
	 * @return array<int, array> List of template-parts/product-card.php `$args` arrays.
	 */
	public static function products( \WC_Product $product, int $limit = 4 ): array {
		if ( ! function_exists( 'wc_get_related_products' ) ) {
			return array();
		}

		$related_ids = wc_get_related_products( $product->get_id(), $limit );
		$products    = array_filter( array_map( 'wc_get_product', $related_ids ) );

		return array_map( array( ProductCardMapper::class, 'map' ), $products );
	}
}
