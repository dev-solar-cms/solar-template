<?php
/**
 * Created: 2026-09-28 16:10 CEST
 * Role: Test double standing in for WordPress' `$wpdb`, used only by
 *       Solar_Template\Admin\SettingsRepository's tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide a minimal in-memory implementation of the handful of `$wpdb` methods
 *          SettingsRepository relies on, so that class can be unit tested without a real
 *          WordPress/MySQL install. Not a general-purpose SQL engine: it only understands the
 *          specific queries SettingsRepository issues.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

/**
 * In-memory stand-in for `$wpdb`, scoped to what SettingsRepository needs.
 */
final class FakeWpdb {

	/**
	 * Table prefix, mirroring the real `$wpdb->prefix`.
	 *
	 * @var string
	 */
	public string $prefix = 'wp_';

	/**
	 * Stored settings, keyed by `setting_key`.
	 *
	 * @var array<string, string>
	 */
	private array $settings = array();

	/**
	 * Mimics `$wpdb->prepare()`: substitutes `%s` (quoted) placeholders.
	 *
	 * @param string $query SQL with placeholders.
	 * @param mixed  ...$args Values to substitute, in order.
	 * @return string The SQL with placeholders replaced.
	 */
	public function prepare( string $query, ...$args ): string {
		$i = 0;

		return preg_replace_callback(
			'/%s/',
			static function () use ( &$i, $args ): string {
				return "'" . addslashes( (string) $args[ $i++ ] ) . "'";
			},
			$query
		);
	}

	/**
	 * Mimics `$wpdb->get_var()` for the "read one setting" lookup.
	 *
	 * @param string $query Already-prepared SQL.
	 * @return string|null The stored value, or null when the key was never saved (matching the real
	 *                     function's own "not found" return value).
	 */
	public function get_var( string $query ): ?string {
		if ( preg_match( "/setting_key = '([^']*)'/", $query, $matches ) ) {
			return $this->settings[ $matches[1] ] ?? null;
		}

		return null;
	}

	/**
	 * Mimics `$wpdb->replace()`.
	 *
	 * @param string $table  Table name (with prefix).
	 * @param array  $data   Column => value.
	 * @param array  $format Ignored.
	 * @return int 1 on success.
	 */
	public function replace( string $table, array $data, array $format = array() ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $format mirrors $wpdb's own signature.
		if ( ! str_contains( $table, 'solar_template_settings' ) ) {
			return 0;
		}

		$this->settings[ $data['setting_key'] ] = $data['setting_value'];

		return 1;
	}
}
