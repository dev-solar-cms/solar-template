<?php
/**
 * Created: 2026-09-26 14:05 CEST
 * Role: Checkout "place order" button text (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Replace WooCommerce's default "Place order" button text with the design handoff's own
 *          "🔒 Pay [amount]" copy, computed from the cart's real, currently-calculated total
 *          rather than a hardcoded string, so it always matches whatever the sidebar's own order
 *          summary total shows.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters the checkout page's order button text.
 */
final class CheckoutPayment {

	/**
	 * Returns "🔒 Pay [total]" using the cart's real, current total, or the given default text when
	 * no cart is available yet (e.g. a call outside of a normal checkout request).
	 *
	 * @param string $text Default WooCommerce button text.
	 * @return string
	 */
	public static function order_button_text( string $text ): string {
		if ( null === WC()->cart ) {
			return $text;
		}

		return sprintf(
			/* translators: %s: order total, formatted as a price. */
			__( '🔒 Pay %s', 'solar-template' ),
			wp_strip_all_tags( WC()->cart->get_total() )
		);
	}
}
