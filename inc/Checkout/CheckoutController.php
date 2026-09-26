<?php
/**
 * Created: 2026-09-26 09:05 CEST
 * Role: Sales tunnel routing/state controller (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Swap in the theme's own top-level template (checkout.php, its minimal header/footer)
 *          for the cart, checkout and order-received pages instead of the generic page template,
 *          and resolve which of the tunnel's 5 steps is the current one server-side, so the step
 *          indicator renders in the right initial state on every request (assets/js/checkout.js
 *          then moves between the login/shipping/payment sub-steps client-side, since those three
 *          live on the single real checkout page). Also detaches three of WooCommerce's own
 *          default hooks so the theme's own templates can place that same content in their own
 *          step instead: the login form/coupon box normally rendered above the whole checkout page
 *          (woocommerce/checkout/form-checkout.php places the login form itself, inside its own
 *          step panel, and the coupon box is already offered on the cart step), the payment
 *          method list/place-order button normally appended straight after the order review table
 *          (woocommerce/checkout/form-checkout.php now renders it in its own "payment" step panel
 *          instead, via a direct call to `woocommerce_checkout_payment()`), and the default order
 *          details table normally appended below the confirmation page (redundant with
 *          woocommerce/checkout/thankyou.php's own styled recap of the same real order data).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes the tunnel's real pages to the theme's own template and resolves the current step.
 */
final class CheckoutController {

	/**
	 * Detaches WooCommerce's default login form/coupon box from the top of the checkout page, its
	 * default payment method list/place-order button from the end of the order review table, and its
	 * default order details table from the end of the confirmation page.
	 *
	 * @return void
	 */
	public static function detach_default_checkout_hooks(): void {
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
	}

	/**
	 * Serves the theme's own checkout.php for the cart/checkout/order-received pages, leaving every
	 * other request's template resolution untouched.
	 *
	 * @param string $template Template path WordPress resolved for the current request.
	 * @return string
	 */
	public static function template_include( string $template ): string {
		if ( ! WooCommerceStatus::is_active() ) {
			return $template;
		}

		if ( is_cart() || is_checkout() ) {
			$flow_template = get_template_directory() . '/checkout.php';

			if ( file_exists( $flow_template ) ) {
				return $flow_template;
			}
		}

		return $template;
	}

	/**
	 * Resolves the tunnel step the current request should render as active.
	 *
	 * The login/shipping/payment steps all live on the single WooCommerce checkout page: the value
	 * returned here is only the step that should be visible first on a fresh page load (a guest
	 * sees the login step, an already logged-in customer or a returning guest sees the shipping
	 * step directly); moving between those three afterward is handled client-side.
	 *
	 * @return string One of 'cart', 'login', 'shipping', 'payment', 'confirmation'.
	 */
	public static function current_step(): string {
		if ( is_cart() ) {
			return 'cart';
		}

		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
			return 'confirmation';
		}

		if ( is_checkout() ) {
			return is_user_logged_in() ? 'shipping' : 'login';
		}

		return 'cart';
	}
}
