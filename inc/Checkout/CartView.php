<?php
/**
 * Created: 2026-09-26 10:30 CEST
 * Role: Cart page view model (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map WooCommerce's real cart contents into the plain view model
 *          woocommerce/cart/cart.php renders, so that override template only displays what this
 *          class already computed — same convention as Catalog\ProductCardMapper.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

use WC_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the cart page's line item view models and heading label.
 */
final class CartView {

	/**
	 * Returns one view model per visible cart line item, in cart order.
	 *
	 * @return array<int, array{
	 *     key: string,
	 *     name: string,
	 *     permalink: string,
	 *     thumbnail: string,
	 *     meta: string,
	 *     quantity: int,
	 *     quantity_input: string,
	 *     unit_price_html: string,
	 *     subtotal_html: string,
	 *     remove_url: string,
	 *     remove_label: string,
	 * }>
	 */
	public static function items(): array {
		if ( null === WC()->cart ) {
			return array();
		}

		$items = array();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			/** @var WC_Product $product */
			$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! $product instanceof WC_Product || ! $product->exists() || $cart_item['quantity'] <= 0 ) {
				continue;
			}

			if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}

			$name      = apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key );
			$permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

			$quantity_input = woocommerce_quantity_input(
				array(
					'input_name'   => "cart[{$cart_item_key}][qty]",
					'input_value'  => $cart_item['quantity'],
					'max_value'    => $product->is_sold_individually() ? 1 : $product->get_max_purchase_quantity(),
					'min_value'    => $product->is_sold_individually() ? 1 : 0,
					'product_name' => $name,
				),
				$product,
				false
			);

			$items[] = array(
				'key'             => $cart_item_key,
				'name'            => $name,
				'permalink'       => $permalink,
				'thumbnail'       => apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image( 'thumbnail' ), $cart_item, $cart_item_key ),
				'meta'            => wc_get_formatted_cart_item_data( $cart_item ),
				'quantity'        => (int) $cart_item['quantity'],
				'quantity_input'  => apply_filters( 'woocommerce_cart_item_quantity', $quantity_input, $cart_item_key, $cart_item ),
				'unit_price_html' => wc_price( wc_get_price_to_display( $product ) ),
				'subtotal_html'   => apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ), $cart_item, $cart_item_key ),
				'remove_url'      => wc_get_cart_remove_url( $cart_item_key ),
				/* translators: %s: product name. */
				'remove_label'    => sprintf( __( 'Remove %s from cart', 'solar-template' ), wp_strip_all_tags( $name ) ),
			);
		}

		return $items;
	}

	/**
	 * Returns the "My cart (N items)" heading label, singular/plural aware.
	 *
	 * @return string
	 */
	public static function heading_label(): string {
		$count = null !== WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

		return sprintf(
			/* translators: %d: number of items in the cart. */
			_n( '%d item', '%d items', $count, 'solar-template' ),
			$count
		);
	}
}
