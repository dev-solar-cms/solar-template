<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: PHPUnit bootstrap for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Load the Composer autoloader and define the minimal set of WordPress functions/
 *          constants actually exercised by classes under `inc/` and by template-parts when no
 *          real WordPress install is available. Deliberately not a full WordPress core stub
 *          library: only what the current test suite needs. Real functional behaviour against
 *          WordPress itself is verified manually in the Docker environment for every step (see
 *          RELEASE.md/doc/*).
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

if ( ! function_exists( '__' ) ) {
	/**
	 * Identity stand-in for WordPress' translation function: tests exercise markup/structure, not
	 * actual translation, which is covered separately by Solar_Template\I18n\DatabaseTranslator.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain (ignored here).
	 * @return string $text, unchanged.
	 */
	function __( string $text, string $domain = 'default' ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WordPress.WP.I18n.MissingTranslatorsComment -- stand-in for the real WordPress function, not a real translatable string.
		return $text;
	}

	/**
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain (ignored here).
	 * @return string Escaped $text.
	 */
	function esc_html__( string $text, string $domain = 'default' ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WordPress.WP.I18n.MissingTranslatorsComment -- stand-in for the real WordPress function, not a real translatable string.
		return htmlspecialchars( $text, ENT_QUOTES );
	}

	/**
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain (ignored here).
	 * @return string Escaped $text.
	 */
	function esc_attr__( string $text, string $domain = 'default' ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WordPress.WP.I18n.MissingTranslatorsComment -- stand-in for the real WordPress function, not a real translatable string.
		return htmlspecialchars( $text, ENT_QUOTES );
	}

	/**
	 * @param string $text   Text to translate and echo.
	 * @param string $domain Text domain (ignored here).
	 * @return void
	 */
	function esc_html_e( string $text, string $domain = 'default' ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WordPress.WP.I18n.MissingTranslatorsComment -- stand-in for the real WordPress function, not a real translatable string.
		echo htmlspecialchars( $text, ENT_QUOTES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this *is* the escaping stand-in.
	}

	/**
	 * @param string $text Text to escape.
	 * @return string Escaped $text.
	 */
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES );
	}

	/**
	 * @param string $text Text to escape.
	 * @return string Escaped $text.
	 */
	function esc_attr( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES );
	}

	/**
	 * @param string $text   Text to translate, escape and echo.
	 * @param string $domain Text domain (ignored here).
	 * @return void
	 */
	function esc_attr_e( string $text, string $domain = 'default' ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WordPress.WP.I18n.MissingTranslatorsComment -- stand-in for the real WordPress function, not a real translatable string.
		echo htmlspecialchars( $text, ENT_QUOTES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this *is* the escaping stand-in.
	}

	/**
	 * @param string $url Raw URL.
	 * @return string Escaped $url.
	 */
	function esc_url( string $url ): string {
		return htmlspecialchars( $url, ENT_QUOTES );
	}

	/**
	 * @param string $content Markup to sanitize.
	 * @return string $content, unchanged (this stand-in trusts test fixtures).
	 */
	function wp_kses_post( string $content ): string {
		return $content;
	}

	/**
	 * @param string $content Markup to sanitize.
	 * @param array  $allowed_html Ignored in this stand-in.
	 * @return string $content, unchanged (this stand-in trusts test fixtures).
	 */
	function wp_kses( string $content, array $allowed_html = array() ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real function's signature.
		return $content;
	}

	/**
	 * @param array $args     Provided arguments.
	 * @param array $defaults Default values.
	 * @return array Merged arguments.
	 */
	function wp_parse_args( array $args, array $defaults = array() ): array {
		return array_merge( $defaults, $args );
	}

	/**
	 * Identity stand-in for WordPress' plural translation function: picks the singular or plural
	 * source text based on $number, no actual translation (see the __() stand-in above).
	 *
	 * @param string $single Singular source text.
	 * @param string $plural Plural source text.
	 * @param int    $number Number determining which form to use.
	 * @param string $domain Text domain (ignored here).
	 * @return string $single or $plural, unchanged.
	 */
	function _n( string $single, string $plural, int $number, string $domain = 'default' ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WordPress.WP.I18n.MissingTranslatorsComment -- stand-in for the real WordPress function, not a real translatable string.
		return 1 === $number ? $single : $plural;
	}

	/**
	 * Plain stand-in for WordPress' locale-aware number formatter (no real locale support here).
	 *
	 * @param float $number   Number to format.
	 * @param int   $decimals Number of decimal points.
	 * @return string Formatted number.
	 */
	function number_format_i18n( float $number, int $decimals = 0 ): string {
		return number_format( $number, $decimals );
	}

	/**
	 * Close enough stand-in for WordPress' own sanitize_text_field(): strips tags and trims,
	 * skipping the extra whitespace-collapsing/invalid-UTF8 handling the real function also does
	 * (not exercised by the current test suite).
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	function sanitize_text_field( string $text ): string {
		return trim( strip_tags( $text ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- stand-in for the real WordPress function, not real output sanitization.
	}

	/**
	 * @param string $text Raw text.
	 * @return string Same stand-in as sanitize_text_field(); the real function also normalizes
	 *                line breaks, not exercised by the current test suite.
	 */
	function sanitize_textarea_field( string $text ): string {
		return trim( strip_tags( $text ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- stand-in for the real WordPress function, not real output sanitization.
	}

	/**
	 * Stand-in for WordPress' own sanitize_key(): lowercase, only [a-z0-9_-] kept.
	 *
	 * @param string $key Raw key.
	 * @return string
	 */
	function sanitize_key( string $key ): string {
		return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $key ) );
	}

	/**
	 * Minimal stand-in for WordPress' own esc_url_raw(): trims and rejects anything not starting
	 * with a small allow-list of schemes/a relative path (the real function's full behaviour —
	 * entity-encoding, a configurable protocol allow-list — isn't exercised by the current test
	 * suite).
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	function esc_url_raw( string $url ): string {
		$url = trim( $url );

		if ( '' === $url ) {
			return '';
		}

		return preg_match( '~^(https?://|/|#)~i', $url ) ? $url : '';
	}

	/**
	 * Stand-in for WordPress' own sanitize_hex_color(): '' or a real #rgb/#rrggbb value, null
	 * otherwise (matching the real function's "invalid" return value).
	 *
	 * @param string $color Raw color value.
	 * @return string|null
	 */
	function sanitize_hex_color( string $color ): ?string {
		if ( '' === $color ) {
			return '';
		}

		return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : null;
	}

	/**
	 * Stand-in for WordPress' own absint(): absolute value, cast to a non-negative integer.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	function absint( $value ): int {
		return abs( (int) $value );
	}

	/**
	 * Stand-in for WordPress' own checked()/selected(): echoes ` checked="checked"`/
	 * ` selected="selected"` when both values are loosely equal, used by their respective
	 * matching function pointers below (`checked()` mirrors this file's other bare-function-name
	 * pattern; `selected()` is a distinct function per WordPress' own API).
	 *
	 * @param mixed $checked_value Value being tested.
	 * @param mixed $current       Value it's compared against.
	 * @param bool  $should_echo   Whether to also echo the resulting attribute.
	 * @return string
	 */
	function checked( $checked_value, $current = true, bool $should_echo = true ): string {
		return solar_template_test_checked_selected_helper( $checked_value, $current, $should_echo, 'checked' );
	}

	/**
	 * @param mixed $selected_value Value being tested.
	 * @param mixed $current        Value it's compared against.
	 * @param bool  $should_echo    Whether to also echo the resulting attribute.
	 * @return string
	 */
	function selected( $selected_value, $current = true, bool $should_echo = true ): string {
		return solar_template_test_checked_selected_helper( $selected_value, $current, $should_echo, 'selected' );
	}

	/**
	 * Shared implementation behind the checked()/selected() stand-ins above, matching WordPress'
	 * own internal `__checked_selected_helper()`.
	 *
	 * @param mixed  $value   Value being tested.
	 * @param mixed  $current Value it's compared against.
	 * @param bool   $echo    Whether to also echo the resulting attribute.
	 * @param string $type    'checked' or 'selected'.
	 * @return string
	 */
	function solar_template_test_checked_selected_helper( $value, $current, bool $should_echo, string $type ): string {
		if ( (string) $value !== (string) $current ) {
			return '';
		}

		$result = " {$type}='{$type}'";

		if ( $should_echo ) {
			echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this *is* the stand-in for the real escaping function.
		}

		return $result;
	}

	/**
	 * Plain stand-in for WordPress' permalink lookup (no real post/routing available here).
	 *
	 * @param int $post_id Post/product ID.
	 * @return string A deterministic fake URL.
	 */
	function get_permalink( int $post_id = 0 ): string {
		return "https://example.test/?p={$post_id}";
	}

	/**
	 * Plain stand-in for WordPress' site URL helper, used by Solar_Template\Support\StoreLinks'
	 * fallbacks (no real site available here).
	 *
	 * @param string $path Path appended to the site URL.
	 * @return string A deterministic fake URL.
	 */
	function home_url( string $path = '' ): string {
		return "https://example.test{$path}";
	}

	/**
	 * Plain stand-in for WooCommerce's price formatter (no real WooCommerce install available
	 * here): tests exercise markup/structure, not real locale-aware formatting.
	 *
	 * @param float $price Amount to format.
	 * @return string A deterministic, HTML-wrapped price string.
	 */
	function wc_price( float $price ): string {
		return '<span class="amount">' . number_format( $price, 2 ) . '</span>';
	}
}

if ( ! function_exists( 'get_template_part' ) ) {
	/**
	 * Minimal stand-in for WordPress' template-part loader: requires the theme file directly with
	 * $args available in its scope, exactly like WordPress' own `load_template()` does (no `-
	 * {$name}` variant lookup, not needed by the current test suite).
	 *
	 * @param string      $slug Template part slug, relative to the theme root.
	 * @param string|null $name Ignored in this stand-in.
	 * @param array       $args Arguments made available to the included file as $args.
	 * @return void
	 */
	function get_template_part( string $slug, ?string $name = null, array $args = array() ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real function's signature.
		require ABSPATH . "{$slug}.php";
	}
}
