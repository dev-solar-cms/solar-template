<?php
/**
 * Created: 2026-09-26 16:20 CEST
 * Role: My Account orders list override (woocommerce/myaccount/orders.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the design handoff's status tabs + order cards instead of core's plain table,
 *          delegating the actual markup to template-parts/account/orders-list.php with a
 *          view-model built by Solar_Template\Account\OrdersView. A plain GET request (tab link/
 *          pagination link) is enough to change what's shown — no JavaScript required.
 *
 * Based on WooCommerce core's own myaccount/orders.php (template version 9.5.0). $has_orders/
 * $customer_orders/$current_page (core's own pagination variables) are provided by WooCommerce
 * (WC_Shortcode_My_Account::orders()) before this template loads, but are intentionally unused:
 * this override runs its own query through Solar_Template\Account\OrdersView instead, so it can
 * apply its own status-tab filtering on top.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\OrdersView;

$user_id        = get_current_user_id();
$valid_tabs     = array( 'all', 'in_progress', 'delivered', 'returns' );
$requested_tab  = isset( $_GET['orders-tab'] ) ? sanitize_key( wp_unslash( $_GET['orders-tab'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab filter, not a state-changing request.
$active_tab     = in_array( $requested_tab, $valid_tabs, true ) ? $requested_tab : 'all';
$requested_page = isset( $_GET['orders-page'] ) ? absint( $_GET['orders-page'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination, not a state-changing request.

$result = OrdersView::orders_for_tab( $user_id, $active_tab, max( 1, $requested_page ) );

get_template_part(
	'template-parts/account/orders-list',
	null,
	array(
		'tabs'         => OrdersView::tabs( $user_id, $active_tab ),
		'orders'       => $result['orders'],
		'current_page' => $result['current_page'],
		'max_pages'    => $result['max_pages'],
		'base_url'     => add_query_arg( 'orders-tab', $active_tab, wc_get_account_endpoint_url( 'orders' ) ),
	)
);
