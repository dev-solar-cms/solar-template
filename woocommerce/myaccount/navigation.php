<?php
/**
 * Created: 2026-09-26 14:42 CEST
 * Role: My Account navigation override (woocommerce/myaccount/navigation.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the design handoff's sidebar (user info block + icon navigation) instead of
 *          core's plain `<ul>` list, delegating the actual markup to
 *          template-parts/account/sidebar.php with view-models built by
 *          Solar_Template\Account\AccountProfile/AccountNav.
 *
 * Based on WooCommerce core's own myaccount/navigation.php (template version 9.3.0).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\AccountNav;
use Solar_Template\Account\AccountProfile;

do_action( 'woocommerce_before_account_navigation' );

get_template_part(
	'template-parts/account/sidebar',
	null,
	array(
		'user'      => AccountProfile::view_model( wp_get_current_user() ),
		'nav_items' => AccountNav::items(),
	)
);

do_action( 'woocommerce_after_account_navigation' );
