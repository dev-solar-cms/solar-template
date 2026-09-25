<?php
/**
 * Created: 2026-09-25 15:11 CEST
 * Role: Header account/cart icon logic (Solar_Template\Header).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Resolve the header's account/cart icon URLs, the live cart item count, the AJAX
 *          cart-fragments callback that refreshes the cart badge, and enqueuing WooCommerce's own
 *          `wc-cart-fragments` script (not auto-loaded since the theme doesn't use WooCommerce's
 *          Cart widget). Account and cart are kept in a single class rather than split further,
 *          since both are the same "header account/cart icon" concern.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Header;

use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Header account/cart icon URLs, cart count, and cart-fragments wiring.
 */
final class Cart {

	/**
	 * Returns the URL the header's account icon should link to.
	 *
	 * Points at the WooCommerce "My account" page when WooCommerce is active, or the standard
	 * WordPress login screen otherwise, so the icon never links to a broken/non-existent page.
	 *
	 * @return string
	 */
	public static function account_url(): string {
		if ( WooCommerceStatus::is_active() && function_exists( 'wc_get_page_permalink' ) ) {
			return wc_get_page_permalink( 'myaccount' );
		}

		return wp_login_url();
	}

	/**
	 * Returns the URL the header's cart icon should link to.
	 *
	 * Points at the WooCommerce cart page when WooCommerce is active, or the site's front page
	 * otherwise, so the icon never links to a broken/non-existent page.
	 *
	 * @return string
	 */
	public static function cart_url(): string {
		if ( WooCommerceStatus::is_active() && function_exists( 'wc_get_cart_url' ) ) {
			return wc_get_cart_url();
		}

		return home_url( '/' );
	}

	/**
	 * Returns the number of items currently in the visitor's WooCommerce cart.
	 *
	 * @return int 0 when WooCommerce is missing/inactive or the cart is not available yet.
	 */
	public static function cart_count(): int {
		if ( ! WooCommerceStatus::is_active() || ! function_exists( 'WC' ) ) {
			return 0;
		}

		if ( null === WC()->cart ) {
			return 0;
		}

		return (int) WC()->cart->get_cart_contents_count();
	}

	/**
	 * Refreshes the header's cart-count badge through WooCommerce's own AJAX cart fragments
	 * mechanism, so adding a product to the cart updates the badge without a full page reload.
	 *
	 * WooCommerce enqueues its `wc-cart-fragments` script on the front end whenever at least one
	 * callback is hooked to this filter, and that script already listens for the `added_to_cart`
	 * event and swaps any DOM element matching a fragment's selector for the markup returned here.
	 * The selector below matches the exact markup template-parts/cart-badge.php renders in the
	 * header, which is why that badge is always present in the DOM, even at zero items: this
	 * fragment can only replace an element that already exists.
	 *
	 * @param array $fragments Existing fragments, keyed by CSS selector.
	 * @return array $fragments with the cart badge's selector added/replaced.
	 */
	public static function cart_fragments( array $fragments ): array {
		ob_start();
		get_template_part( 'template-parts/cart-badge' );
		$fragments['span.site-header__cart-count'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Enqueues WooCommerce's own `wc-cart-fragments` script on the front end.
	 *
	 * @return void
	 */
	public static function enqueue_cart_fragments(): void {
		if ( WooCommerceStatus::is_active() ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	}
}
