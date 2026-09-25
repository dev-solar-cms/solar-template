<?php
/**
 * Created: 2026-09-25 16:45 CEST
 * Role: Product panel "Add to cart" form data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Shape the data template-parts/single-product/panel.php needs to render the price block,
 *          Color/Size selectors and the "Add to cart" form, for both simple and variable products.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the product panel's price/cart-form data.
 */
final class ProductCartForm {

	/**
	 * @param \WC_Product $product Product to build cart-form data for.
	 * @return array{
	 *     product_id: int,
	 *     is_variable: bool,
	 *     price_html: string,
	 *     can_add_to_cart: bool,
	 *     variation_groups: array,
	 * }
	 */
	public static function for_product( \WC_Product $product ): array {
		$is_variable = ProductVariations::is_variable( $product );

		return array(
			'product_id'       => $product->get_id(),
			'is_variable'      => $is_variable,
			'price_html'       => $product->get_price_html(),
			'can_add_to_cart'  => ! $is_variable && $product->is_purchasable() && $product->is_in_stock(),
			'variation_groups' => $is_variable ? ProductVariations::attribute_groups( $product ) : array(),
		);
	}
}
