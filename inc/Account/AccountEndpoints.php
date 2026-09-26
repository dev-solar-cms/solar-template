<?php
/**
 * Created: 2026-09-26 14:05 CEST
 * Role: My Account custom endpoints/menu controller (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Register the theme's two custom My Account endpoints ("wishlist", "sav" — see
 *          Solar_Template\Account\WishlistController/SupportRequestController for their content,
 *          added in later steps) and rebuild the account menu in the design handoff's own order
 *          (Dashboard, Orders, Wishlist, Addresses, Downloads, S.A.V., Settings, Logout), dropping
 *          WooCommerce's default "Payment methods" item (not part of the design). Also supplies the
 *          page `<title>`/heading for the two custom endpoints via `woocommerce_endpoint_title`.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the theme's custom My Account endpoints and reorders the account menu.
 */
final class AccountEndpoints {

	/**
	 * Registers the "wishlist" and "sav" (support request) endpoints. Safe to call on every request;
	 * WordPress only needs a rewrite flush once for a newly-registered endpoint to resolve
	 * (Solar_Template\Database\Installer::install() flushes rewrite rules on theme activation).
	 *
	 * @return void
	 */
	public static function register_endpoints(): void {
		add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'sav', EP_ROOT | EP_PAGES );
	}

	/**
	 * Rebuilds the account menu in the design handoff's own order and wording (its own translation
	 * catalog throughout, rather than WooCommerce core's default labels for the same items — e.g.
	 * "Settings" rather than core's "Account details"). WooCommerce keeps "dashboard" as a
	 * pseudo-endpoint pointing at the My Account page itself, and always appends "customer-logout" as
	 * a link to `wp_logout_url()`.
	 *
	 * @param array $items Default WooCommerce account menu items (endpoint slug => label), ignored:
	 *                     this theme supplies every label itself.
	 * @return array Reordered items, with "wishlist"/"sav" inserted and "payment-methods" dropped.
	 */
	public static function menu_items( array $items ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- $items mirrors the `woocommerce_account_menu_items` filter's own signature.
		return array(
			'dashboard'       => __( 'Dashboard', 'solar-template' ),
			'orders'          => __( 'Orders', 'solar-template' ),
			'wishlist'        => __( 'Wishlist', 'solar-template' ),
			'edit-address'    => __( 'Addresses', 'solar-template' ),
			'downloads'       => __( 'Downloads', 'solar-template' ),
			'sav'             => __( 'Support request', 'solar-template' ),
			'edit-account'    => __( 'Settings', 'solar-template' ),
			'customer-logout' => __( 'Logout', 'solar-template' ),
		);
	}

	/**
	 * Supplies the page title/heading for the theme's two custom endpoints.
	 *
	 * @param string $title    Default endpoint title.
	 * @param string $endpoint Current endpoint slug.
	 * @return string
	 */
	public static function endpoint_title( string $title, string $endpoint ): string {
		if ( 'wishlist' === $endpoint ) {
			return __( 'Wishlist', 'solar-template' );
		}

		if ( 'sav' === $endpoint ) {
			return __( 'Support request', 'solar-template' );
		}

		return $title;
	}
}
