<?php
/**
 * Created: 2026-09-26 14:44 CEST
 * Role: My Account dashboard override (woocommerce/myaccount/dashboard.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the design handoff's dashboard (greeting, stat cards, recent orders, quick links)
 *          instead of core's plain welcome text, delegating the actual markup to
 *          template-parts/account/dashboard.php with a view-model built by
 *          Solar_Template\Account\Dashboard.
 *
 * Based on WooCommerce core's own myaccount/dashboard.php (template version 4.4.0). $current_user
 * is provided by WooCommerce (WC_Shortcode_My_Account::my_account()) before this template loads.
 *
 * @package Solar_Template
 * @var \WP_User $current_user
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\Dashboard;

get_template_part(
	'template-parts/account/dashboard',
	null,
	array(
		'greeting_name' => $current_user->first_name ? $current_user->first_name : $current_user->display_name,
		'stats'         => Dashboard::stats( $current_user->ID ),
		'orders_url'    => wc_get_account_endpoint_url( 'orders' ),
		'wishlist_url'  => wc_get_account_endpoint_url( 'wishlist' ),
		'addresses_url' => wc_get_account_endpoint_url( 'edit-address' ),
		'downloads_url' => wc_get_account_endpoint_url( 'downloads' ),
		'sav_url'       => wc_get_account_endpoint_url( 'sav' ),
		'settings_url'  => wc_get_account_endpoint_url( 'edit-account' ),
		'recent_orders' => Dashboard::recent_orders( $current_user->ID ),
	)
);

/**
 * My Account dashboard (kept for third-party plugin compatibility, e.g. a payment gateway adding
 * its own dashboard notice).
 */
do_action( 'woocommerce_account_dashboard' );
