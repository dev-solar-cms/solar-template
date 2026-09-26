<?php
/**
 * Created: 2026-09-26 20:10 CEST
 * Role: Account deletion handler (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Handle the settings page's "danger zone" self-service account deletion — native
 *          WordPress/WooCommerce only (DECISIONS.md §2, no third-party GDPR/deletion plugin): the
 *          customer's own orders are force-deleted through WooCommerce's own CRUD
 *          (`WC_Order::delete( true )`, storage-agnostic between the classic post-type and HPOS),
 *          then the account itself through WordPress' own `wp_delete_user()`. Requires the settings
 *          page's own required confirmation checkbox (works with no JavaScript); a native `confirm()`
 *          dialog (assets/js/account.js, `initAccountDeletionConfirm()`) is a second, JS-only layer.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the account settings page's account-deletion form submission.
 */
final class AccountDeletion {

	public const NONCE_ACTION = 'solar_template_delete_account';

	/**
	 * Handles the deletion form's plain POST submission, hooked to `template_redirect`. Silently does
	 * nothing for any other request.
	 *
	 * @return void
	 */
	public static function maybe_handle(): void {
		if ( ! isset( $_POST['solar_template_delete_account'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		check_admin_referer( self::NONCE_ACTION );

		if ( empty( $_POST['solar_template_confirm_deletion'] ) ) {
			wc_add_notice( __( 'Please confirm you understand this action is irreversible.', 'solar-template' ), 'error' );

			return;
		}

		$user_id = get_current_user_id();

		foreach ( wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
			)
		) as $order ) {
			$order->delete( true );
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';

		wp_delete_user( $user_id );

		wp_logout();
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
}
