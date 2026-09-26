<?php
/**
 * Created: 2026-09-26 14:25 CEST
 * Role: "Reorder" action handler (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Handle the dashboard/orders list "Reorder" link: a plain, nonce-protected GET request
 *          that adds every line item of a past order back into the current cart, then redirects to
 *          the cart page. Hooked on `template_redirect` (Solar_Template\Theme::boot()) so it runs
 *          before any page output starts.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a past order's items back into the cart ("Reorder").
 */
final class ReorderHandler {

	private const QUERY_VAR    = 'solar_template_reorder';
	private const NONCE_ACTION = 'solar_template_reorder';

	/**
	 * Builds the nonce-protected "Reorder" URL for a given order, pointing at the My Account orders
	 * page.
	 *
	 * @param int $order_id Order to reorder.
	 * @return string
	 */
	public static function url( int $order_id ): string {
		return wp_nonce_url(
			add_query_arg( self::QUERY_VAR, $order_id, wc_get_account_endpoint_url( 'orders' ) ),
			self::NONCE_ACTION
		);
	}

	/**
	 * Handles a "Reorder" request: verifies the nonce and that the order belongs to the current
	 * customer, empties nothing (existing cart contents are kept), adds every line item's product/
	 * variation/quantity back into the cart, then redirects to the cart page with a confirmation
	 * notice. Silently does nothing for any other request.
	 *
	 * @return void
	 */
	public static function maybe_handle(): void {
		if ( ! isset( $_GET[ self::QUERY_VAR ], $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$order_id = absint( $_GET[ self::QUERY_VAR ] );
		$order    = wc_get_order( $order_id );

		if ( ! $order || $order->get_customer_id() !== get_current_user_id() ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$product      = $item->get_product();

			if ( ! $product || ! $product->is_purchasable() ) {
				continue;
			}

			WC()->cart->add_to_cart( $product_id, $item->get_quantity(), $variation_id );
		}

		wc_add_notice( __( 'The items from your past order have been added to your cart.', 'solar-template' ) );

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}
}
