<?php
/**
 * Created: 2026-09-26 20:05 CEST
 * Role: Account settings custom fields (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Read/save the two extra "Settings" fields the design handoff adds on top of
 *          WooCommerce's own native edit-account form (phone, birthdate) — both plain user meta,
 *          submitted alongside the same form/nonce WooCommerce's own
 *          `WC_Form_Handler::save_account_details()` already processes natively for name/email/
 *          password.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads/saves the account settings page's phone/birthdate fields.
 */
final class AccountSettingsFields {

	public const META_PHONE     = '_solar_template_phone';
	public const META_BIRTHDATE = '_solar_template_birthdate';

	/**
	 * @param int $user_id Customer's user ID.
	 * @return string Saved phone number, or an empty string when none is set.
	 */
	public static function phone( int $user_id ): string {
		return (string) get_user_meta( $user_id, self::META_PHONE, true );
	}

	/**
	 * @param int $user_id Customer's user ID.
	 * @return string Saved birthdate formatted `Y-m-d`, or an empty string when none is set.
	 */
	public static function birthdate( int $user_id ): string {
		return (string) get_user_meta( $user_id, self::META_BIRTHDATE, true );
	}

	/**
	 * Persists the two fields from the current request. Hooked to `woocommerce_save_account_details`,
	 * which only ever fires after `WC_Form_Handler::save_account_details()` has already verified its
	 * own nonce and saved the native fields (name/email/password) — no separate nonce check needed
	 * here.
	 *
	 * @param int $user_id Customer's user ID, as passed by the `woocommerce_save_account_details`
	 *                      hook.
	 * @return void
	 */
	public static function save_from_request( int $user_id ): void {
		if ( isset( $_POST['solar_template_phone'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified by WC_Form_Handler::save_account_details() before this hook fires.
			update_user_meta( $user_id, self::META_PHONE, sanitize_text_field( wp_unslash( $_POST['solar_template_phone'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( isset( $_POST['solar_template_birthdate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.
			update_user_meta( $user_id, self::META_BIRTHDATE, self::sanitize_birthdate( wp_unslash( $_POST['solar_template_birthdate'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}

	/**
	 * Validates a `<input type="date">` submission (`Y-m-d`) against a real calendar date. Kept as
	 * pure logic, independent of any WordPress function, so it can be unit tested directly.
	 *
	 * @param string $raw Raw submitted value.
	 * @return string $raw when it is a real `Y-m-d` date, an empty string otherwise (the field stays
	 *                optional either way, per the design handoff).
	 */
	public static function sanitize_birthdate( string $raw ): string {
		$raw = trim( $raw );

		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $matches ) ) {
			return '';
		}

		if ( ! checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ) {
			return '';
		}

		return $raw;
	}
}
