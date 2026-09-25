<?php
/**
 * Created: 2026-09-25 16:45 CEST
 * Role: Product page request handler (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The product page's WordPress-hook "controller" in the DECISIONS.md MVC sense: localizes
 *          the variation payload, the simple product's own base price, and WooCommerce's real price
 *          display settings for assets/js/product.js to resolve a Color/Size/engraving selection
 *          and recompute the displayed price against, on a single product page only.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the product page's variation/price behavior into WordPress/WooCommerce.
 */
final class ProductController {

	/**
	 * Localizes `window.solarTemplateProduct` with the store's real price display settings
	 * (decimals, separators, symbol position) and, for a variable product, its variation payload —
	 * so the price assets/js/product.js recomputes on every Color/Size change formats exactly like
	 * WooCommerce's own `wc_price()` would.
	 *
	 * @return void
	 */
	public static function enqueue_script(): void {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$product = wc_get_product( get_queried_object_id() );

		if ( ! $product ) {
			return;
		}

		$is_variable = ProductVariations::is_variable( $product );

		$data = array(
			'priceFormat' => array(
				'decimals'          => wc_get_price_decimals(),
				'decimalSeparator'  => wc_get_price_decimal_separator(),
				'thousandSeparator' => wc_get_price_thousand_separator(),
				'format'            => get_woocommerce_price_format(),
				'currencySymbol'    => get_woocommerce_currency_symbol(),
			),
			// Null for a variable product: its price is only known once a full Color/Size selection
			// resolves a real variation (see assets/js/product.js), never this base product itself.
			'basePrice'   => $is_variable ? null : (float) $product->get_price(),
			'variations'  => array(),
			'i18n'        => array(
				'unavailable' => __( 'This combination is currently unavailable.', 'solar-template' ),
				'outOfStock'  => __( 'This combination is currently out of stock.', 'solar-template' ),
			),
		);

		if ( $is_variable ) {
			$data['variations'] = ProductVariations::variations_payload( $product );
		}

		wp_localize_script( 'solar-template', 'solarTemplateProduct', $data );
	}
}
