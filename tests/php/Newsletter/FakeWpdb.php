<?php
/**
 * Created: 2026-09-25 12:30 CEST
 * Role: Test double standing in for WordPress' `$wpdb`, used only by
 *       Solar_Template\Newsletter\SubscriberRepository's tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide a minimal in-memory implementation of the handful of `$wpdb` methods
 *          SubscriberRepository relies on, so that class can be unit tested without a real
 *          WordPress/MySQL install. Not a general-purpose SQL engine: it only understands the
 *          specific queries SubscriberRepository issues.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Newsletter;

/**
 * In-memory stand-in for `$wpdb`, scoped to what SubscriberRepository needs.
 */
final class FakeWpdb {

	/**
	 * Table prefix, mirroring the real `$wpdb->prefix`.
	 *
	 * @var string
	 */
	public string $prefix = 'wp_';

	/**
	 * Subscribed email addresses.
	 *
	 * @var string[]
	 */
	private array $subscribers = array();

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
	 * Mimics `$wpdb->get_var()` for the "is this email already subscribed" lookup.
	 *
	 * @param string $query Already-prepared SQL.
	 * @return int Number of matching rows (0 or 1).
	 */
	public function get_var( string $query ): int {
		if ( preg_match( "/email = '([^']*)'/", $query, $matches ) ) {
			return in_array( $matches[1], $this->subscribers, true ) ? 1 : 0;
		}

		return 0;
	}

	/**
	 * Mimics `$wpdb->insert()`.
	 *
	 * @param string $table  Table name (with prefix).
	 * @param array  $data   Column => value.
	 * @param array  $format Ignored.
	 * @return int 1 on success.
	 */
	public function insert( string $table, array $data, array $format = array() ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $format mirrors $wpdb's own signature.
		if ( str_contains( $table, 'solar_template_newsletter_subscribers' ) ) {
			$this->subscribers[] = $data['email'];

			return 1;
		}

		return 0;
	}
}
