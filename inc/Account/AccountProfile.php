<?php
/**
 * Created: 2026-09-26 14:15 CEST
 * Role: My Account sidebar user-info view-model (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the small "name/email/avatar initial" block shown at the top of the My Account
 *          sidebar, from the currently logged-in user.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the sidebar's user-info view-model.
 */
final class AccountProfile {

	/**
	 * @param \WP_User $user Currently logged-in user.
	 * @return array{name: string, email: string, initial: string}
	 */
	public static function view_model( \WP_User $user ): array {
		$name = $user->display_name ? $user->display_name : $user->user_login;

		return array(
			'name'    => $name,
			'email'   => $user->user_email,
			'initial' => mb_strtoupper( mb_substr( $name, 0, 1 ) ),
		);
	}
}
