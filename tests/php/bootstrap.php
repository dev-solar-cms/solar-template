<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: PHPUnit bootstrap for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Load the Composer autoloader and define the minimal set of WordPress functions/
 *          constants actually exercised by classes under `inc/` when no real WordPress install is
 *          available. Deliberately not a full WordPress core stub library: only what the current
 *          test suite needs. Real functional behaviour against WordPress itself is verified
 *          manually in the Docker environment for every step (see RELEASE.md/doc/*).
 *
 * @package Solar_Template
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	/**
	 * Minimal stand-in for WordPress' recursive directory creation helper.
	 *
	 * @param string $target Directory to create.
	 * @return bool True on success (or if it already exists).
	 */
	function wp_mkdir_p( string $target ): bool {
		return is_dir( $target ) || mkdir( $target, 0777, true );
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	global $solar_template_test_transients;
	$solar_template_test_transients = array();

	/**
	 * In-memory stand-in for WordPress' transients API, used only by TransientCacheTest.
	 *
	 * @param string $key Transient name.
	 * @return mixed The stored value, or false when absent.
	 */
	function get_transient( string $key ): mixed {
		global $solar_template_test_transients;

		return $solar_template_test_transients[ $key ] ?? false;
	}

	/**
	 * @param string $key   Transient name.
	 * @param mixed  $value Value to store.
	 * @param int    $ttl   Ignored in this in-memory stand-in.
	 * @return bool Always true.
	 */
	function set_transient( string $key, mixed $value, int $ttl = 0 ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $ttl mirrors WordPress' own signature.
		global $solar_template_test_transients;

		$solar_template_test_transients[ $key ] = $value;

		return true;
	}

	/**
	 * @param string $key Transient name.
	 * @return bool True when a value was removed.
	 */
	function delete_transient( string $key ): bool {
		global $solar_template_test_transients;

		$existed = array_key_exists( $key, $solar_template_test_transients );
		unset( $solar_template_test_transients[ $key ] );

		return $existed;
	}
}
