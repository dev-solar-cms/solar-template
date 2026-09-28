<?php
/**
 * Created: 2026-09-28 11:20 CEST
 * Role: Minimal WordPress AJAX-handler function stand-ins for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as bootstrap.php's own WordPress function stand-ins — current_user_can()/
 *          check_ajax_referer() read a configurable global so a test can simulate an unauthorized/
 *          unauthenticated request, and wp_send_json_error()/wp_send_json_success() throw
 *          Solar_Template\Tests\Support\WpDieException to simulate the real functions' own
 *          `wp_die()`-based halt — letting a test assert that no code past the halting call ever ran.
 *
 * @package Solar_Template
 */

use Solar_Template\Tests\Support\WpDieException;

if ( ! function_exists( 'current_user_can' ) ) {
	global $solar_template_test_current_user_can;
	$solar_template_test_current_user_can = true;

	/**
	 * @param string $capability Capability being checked (ignored — this stand-in has a single
	 *                            global switch, not a per-capability map).
	 * @return bool
	 */
	function current_user_can( string $capability ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- stand-in mirrors the real function's signature (a single global switch, not a per-capability map).
		global $solar_template_test_current_user_can;

		return $solar_template_test_current_user_can;
	}
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
	global $solar_template_test_valid_nonce;
	$solar_template_test_valid_nonce = true;

	/**
	 * @param string      $action     Nonce action (ignored).
	 * @param string|bool $query_arg  Ignored.
	 * @param bool        $should_die Whether an invalid nonce halts the request, matching the real
	 *                                function's own default.
	 * @return int|bool
	 */
	function check_ajax_referer( string $action, $query_arg = false, bool $should_die = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real function's signature.
		global $solar_template_test_valid_nonce;

		if ( $solar_template_test_valid_nonce ) {
			return 1;
		}

		if ( $should_die ) {
			throw new WpDieException( 'check_ajax_referer: invalid nonce' );
		}

		return false;
	}
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
	global $solar_template_test_last_json_error;
	$solar_template_test_last_json_error = null;

	/**
	 * @param mixed $data Response payload.
	 * @return never
	 */
	function wp_send_json_error( $data = null ) {
		global $solar_template_test_last_json_error;
		$solar_template_test_last_json_error = $data;

		throw new WpDieException( 'wp_send_json_error' );
	}
}

if ( ! function_exists( 'wp_send_json_success' ) ) {
	global $solar_template_test_last_json_success;
	$solar_template_test_last_json_success = null;

	/**
	 * @param mixed $data Response payload.
	 * @return never
	 */
	function wp_send_json_success( $data = null ) {
		global $solar_template_test_last_json_success;
		$solar_template_test_last_json_success = $data;

		throw new WpDieException( 'wp_send_json_success' );
	}
}

if ( ! function_exists( 'wp_cache_flush' ) ) {
	global $solar_template_test_wp_cache_flush_calls;
	$solar_template_test_wp_cache_flush_calls = 0;

	/**
	 * @return bool True.
	 */
	function wp_cache_flush(): bool {
		global $solar_template_test_wp_cache_flush_calls;
		++$solar_template_test_wp_cache_flush_calls;

		return true;
	}
}
