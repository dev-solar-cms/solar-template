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

if ( ! function_exists( 'get_posts' ) ) {
	global $solar_template_test_get_posts_result;
	global $solar_template_test_get_posts_calls;
	$solar_template_test_get_posts_result = array();
	$solar_template_test_get_posts_calls  = 0;

	/**
	 * In-memory stand-in for WordPress' own get_posts(): always returns whatever the test registered
	 * in $GLOBALS['solar_template_test_get_posts_result'], ignoring the query args themselves (query
	 * correctness is unchanged, pre-existing production logic — this stand-in exists only to let a
	 * test count how many times the underlying query actually ran, see
	 * FrontPage\BlogPreviewTest/Blog\RelatedArticlesTest). Also counts its own calls in
	 * $GLOBALS['solar_template_test_get_posts_calls'].
	 *
	 * @param array $args Query args (ignored).
	 * @return array
	 */
	function get_posts( array $args = array() ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- stand-in mirrors the real function's signature.
		global $solar_template_test_get_posts_result, $solar_template_test_get_posts_calls;

		++$solar_template_test_get_posts_calls;

		return $solar_template_test_get_posts_result;
	}
}

if ( ! function_exists( 'wc_get_orders' ) ) {
	global $solar_template_test_orders_by_user;
	global $solar_template_test_wc_get_orders_calls;
	global $solar_template_test_wc_get_orders_last_args;
	$solar_template_test_orders_by_user          = array();
	$solar_template_test_wc_get_orders_calls     = 0;
	$solar_template_test_wc_get_orders_last_args = array();

	/**
	 * In-memory stand-in for WooCommerce's own order lookup, keyed by
	 * $GLOBALS['solar_template_test_orders_by_user'][$customer_id]. Also counts its own calls in
	 * $GLOBALS['solar_template_test_wc_get_orders_calls'] (so a test can assert a memoized caller only
	 * triggers it once, see Account\OrdersViewTest) and records the exact $args it was last called
	 * with in $GLOBALS['solar_template_test_wc_get_orders_last_args'] (so a test can assert a caller's
	 * own extra args reached it unchanged, see Account\CustomerOrdersTest).
	 *
	 * @param array $args Query args (only `customer` is read to select a result).
	 * @return WC_Order[]
	 */
	function wc_get_orders( array $args = array() ): array {
		global $solar_template_test_orders_by_user, $solar_template_test_wc_get_orders_calls, $solar_template_test_wc_get_orders_last_args;

		++$solar_template_test_wc_get_orders_calls;
		$solar_template_test_wc_get_orders_last_args = $args;

		$customer_id = (int) ( $args['customer'] ?? 0 );

		return $solar_template_test_orders_by_user[ $customer_id ] ?? array();
	}
}

if ( ! function_exists( 'wc_get_account_endpoint_url' ) ) {
	/**
	 * Deterministic stand-in for WooCommerce's own My Account endpoint URL lookup.
	 *
	 * @param string $endpoint Endpoint slug.
	 * @return string
	 */
	function wc_get_account_endpoint_url( string $endpoint ): string {
		return "https://example.test/my-account/{$endpoint}/";
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	/**
	 * Close-enough stand-in for WordPress' own add_query_arg( $key, $value, $url ) three-argument
	 * form — the only one the classes under test actually use.
	 *
	 * @param string $key   Query arg name.
	 * @param string $value Query arg value.
	 * @param string $url   Base URL.
	 * @return string
	 */
	function add_query_arg( string $key, string $value, string $url ): string {
		$separator = str_contains( $url, '?' ) ? '&' : '?';

		return $url . $separator . rawurlencode( $key ) . '=' . rawurlencode( $value );
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

if ( ! function_exists( 'is_user_logged_in' ) ) {
	global $solar_template_test_logged_in_user_id;
	$solar_template_test_logged_in_user_id = 0;

	/**
	 * In-memory stand-in for WordPress' own login state, driven by
	 * $GLOBALS['solar_template_test_logged_in_user_id'] (0 = logged out).
	 *
	 * @return bool
	 */
	function is_user_logged_in(): bool {
		global $solar_template_test_logged_in_user_id;

		return $solar_template_test_logged_in_user_id > 0;
	}

	/**
	 * @return int
	 */
	function get_current_user_id(): int {
		global $solar_template_test_logged_in_user_id;

		return $solar_template_test_logged_in_user_id;
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Stand-in for WordPress' own is_wp_error(): this test suite never constructs a real WP_Error.
	 *
	 * @param mixed $thing Value to check.
	 * @return bool Always false.
	 */
	function is_wp_error( $thing ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- stand-in mirrors the real function's signature.
		return false;
	}
}

if ( ! function_exists( 'get_the_terms' ) ) {
	global $solar_template_test_terms;
	$solar_template_test_terms = array();

	/**
	 * In-memory stand-in for WordPress' own get_the_terms(), keyed by
	 * $GLOBALS['solar_template_test_terms'][$post_id][$taxonomy] (an array of term-like objects with
	 * at least a `name` property).
	 *
	 * @param int    $post_id  Post/product ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return array|false
	 */
	function get_the_terms( int $post_id, string $taxonomy ) {
		global $solar_template_test_terms;

		return $solar_template_test_terms[ $post_id ][ $taxonomy ] ?? false;
	}
}

if ( ! function_exists( 'wp_get_attachment_image_url' ) ) {
	/**
	 * Deterministic stand-in for WordPress' own attachment image URL lookup.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $size          Registered image size.
	 * @return string
	 */
	function wp_get_attachment_image_url( int $attachment_id, string $size = 'thumbnail' ): string {
		return "https://example.test/uploads/{$attachment_id}-{$size}.jpg";
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	global $solar_template_test_post_meta;
	$solar_template_test_post_meta = array();

	/**
	 * In-memory stand-in for WordPress' own get_post_meta(), keyed by
	 * $GLOBALS['solar_template_test_post_meta'][$post_id][$key].
	 *
	 * @param int    $post_id Post/attachment ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Ignored (this stand-in only ever stores a single value per key).
	 * @return mixed
	 */
	function get_post_meta( int $post_id, string $key = '', bool $single = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real function's signature.
		global $solar_template_test_post_meta;

		return $solar_template_test_post_meta[ $post_id ][ $key ] ?? '';
	}
}

if ( ! function_exists( 'get_terms' ) ) {
	global $solar_template_test_terms_by_taxonomy;
	$solar_template_test_terms_by_taxonomy = array();

	/**
	 * In-memory stand-in for WordPress' own get_terms(), keyed by
	 * $GLOBALS['solar_template_test_terms_by_taxonomy'][$taxonomy] (a flat array of WP_Term-like
	 * objects). Only understands the `taxonomy`/`include` args actually used by the classes under
	 * test — not a general-purpose term query engine.
	 *
	 * @param array $args Query args.
	 * @return array<int, WP_Term>
	 */
	function get_terms( array $args = array() ): array {
		global $solar_template_test_terms_by_taxonomy;

		$taxonomy = (string) ( $args['taxonomy'] ?? '' );
		$all      = $solar_template_test_terms_by_taxonomy[ $taxonomy ] ?? array();

		if ( empty( $args['include'] ) ) {
			return array_values( $all );
		}

		$include = array_map( 'intval', (array) $args['include'] );

		return array_values( array_filter( $all, static fn( WP_Term $term ): bool => in_array( $term->term_id, $include, true ) ) );
	}
}

if ( ! function_exists( 'get_term_link' ) ) {
	/**
	 * Deterministic stand-in for WordPress' own get_term_link().
	 *
	 * @param WP_Term|int $term Term or term ID.
	 * @return string
	 */
	function get_term_link( $term ): string {
		$term_id = $term instanceof WP_Term ? $term->term_id : (int) $term;

		return "https://example.test/product-category/{$term_id}/";
	}
}

if ( ! function_exists( 'get_term_meta' ) ) {
	global $solar_template_test_term_meta;
	$solar_template_test_term_meta = array();

	/**
	 * In-memory stand-in for WordPress' own get_term_meta(), keyed by
	 * $GLOBALS['solar_template_test_term_meta'][$term_id][$key].
	 *
	 * @param int    $term_id Term ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Ignored (this stand-in only ever stores a single value per key).
	 * @return mixed
	 */
	function get_term_meta( int $term_id, string $key = '', bool $single = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real function's signature.
		global $solar_template_test_term_meta;

		return $solar_template_test_term_meta[ $term_id ][ $key ] ?? '';
	}
}

if ( ! function_exists( 'get_woocommerce_currency_symbol' ) ) {
	/**
	 * Deterministic stand-in for WooCommerce's own currency symbol lookup.
	 *
	 * @return string
	 */
	function get_woocommerce_currency_symbol(): string {
		return '€';
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
