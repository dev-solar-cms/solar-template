<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Minimal WooCommerce/user-meta function stand-ins for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as bootstrap.php's own WordPress function stand-ins — an in-memory
 *          wc_get_product()/user-meta registry plus the handful of WordPress/WooCommerce helper
 *          functions the Product-domain classes under test actually call. Not a WooCommerce test
 *          framework: real functional behaviour against a live WooCommerce install is verified
 *          manually in Docker for every step (see RELEASE.md).
 *
 * @package Solar_Template
 */

if ( ! function_exists( 'wc_get_product' ) ) {
	global $solar_template_test_products;
	$solar_template_test_products = array();

	/**
	 * In-memory stand-in for WooCommerce's own product lookup: returns whatever the test registered
	 * in $GLOBALS['solar_template_test_products'][$id], false otherwise (matching the real function's
	 * "not found" return value).
	 *
	 * @param int $id Product/variation ID to look up.
	 * @return WC_Product|false
	 */
	function wc_get_product( int $id ) {
		global $solar_template_test_products;

		return $solar_template_test_products[ $id ] ?? false;
	}
}

if ( ! function_exists( 'wp_generate_uuid4' ) ) {
	/**
	 * Deterministic-enough stand-in for WordPress' own UUID generator — the classes under test only
	 * ever use its result as an opaque cart item key, never parse it.
	 *
	 * @return string
	 */
	function wp_generate_uuid4(): string {
		return 'test-uuid-' . bin2hex( random_bytes( 8 ) );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * Stand-in for WordPress' own wp_unslash(): this test environment never adds magic-quote-style
	 * slashes to superglobals in the first place, so it's a pure identity function here — enough to
	 * exercise the calling code's own logic, not WordPress' slashing behaviour.
	 *
	 * @param mixed $value Raw value.
	 * @return mixed $value, unchanged.
	 */
	function wp_unslash( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'wc_format_decimal' ) ) {
	/**
	 * Close-enough stand-in for WooCommerce's own wc_format_decimal(): normalizes a comma decimal
	 * separator and returns a numeric string, skipping the real function's locale/rounding options
	 * (not exercised by the current test suite).
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	function wc_format_decimal( $value ): string {
		$value = str_replace( ',', '.', (string) $value );

		return is_numeric( $value ) ? (string) $value : '';
	}
}

if ( ! function_exists( 'get_user_meta' ) ) {
	global $solar_template_test_user_meta;
	$solar_template_test_user_meta = array();

	/**
	 * In-memory stand-in for WordPress' own user meta API, scoped per user ID/meta key.
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Ignored (this stand-in only ever stores a single value per key).
	 * @return mixed
	 */
	function get_user_meta( int $user_id, string $key, bool $single = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real function's signature.
		global $solar_template_test_user_meta;

		return $solar_template_test_user_meta[ $user_id ][ $key ] ?? '';
	}

	/**
	 * @param int    $user_id User ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 * @return bool True.
	 */
	function update_user_meta( int $user_id, string $key, $value ): bool {
		global $solar_template_test_user_meta;

		$solar_template_test_user_meta[ $user_id ][ $key ] = $value;

		return true;
	}
}
