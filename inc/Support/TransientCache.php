<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Default implementation of Solar_Template\Contracts\CacheInterface.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Store cached values as WordPress transients, without any dependency beyond WordPress
 *          core. All keys are namespaced to avoid colliding with transients from other themes
 *          or plugins. The default TTL (used whenever set() is called without one of its own) is
 *          read from the theme's own `.env` (`SOLAR_TEMPLATE_CACHE_TTL`, see .env.example — set
 *          up back at the theme's `.env` step, but never actually consumed anywhere until now),
 *          via Solar_Template\Support\Env, falling back to one hour when unset.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

use Solar_Template\Contracts\CacheInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caches values with the WordPress transients API (`get_transient`/`set_transient`).
 */
final class TransientCache implements CacheInterface {

	/**
	 * Prefix applied to every transient key managed by this cache.
	 *
	 * @var string
	 */
	private const PREFIX = 'solar_template_';

	/**
	 * Default time to live, in seconds, used when set() is called with $ttl = 0 and the theme's
	 * `.env` does not configure `SOLAR_TEMPLATE_CACHE_TTL`.
	 *
	 * @var int
	 */
	private const FALLBACK_DEFAULT_TTL = HOUR_IN_SECONDS;

	/**
	 * Reads a cached value.
	 *
	 * @param string $key     Cache key.
	 * @param mixed  $default_value Value returned when the key is absent or expired.
	 * @return mixed The cached value, or $default_value.
	 */
	public function get( string $key, mixed $default_value = null ): mixed {
		$value = get_transient( $this->prefixed_key( $key ) );

		return ( false === $value ) ? $default_value : $value;
	}

	/**
	 * Stores a value in the cache.
	 *
	 * @param string $key   Cache key.
	 * @param mixed  $value Value to store.
	 * @param int    $ttl   Time to live in seconds. 0 falls back to self::default_ttl().
	 * @return bool True on success.
	 */
	public function set( string $key, mixed $value, int $ttl = 0 ): bool {
		return set_transient( $this->prefixed_key( $key ), $value, $ttl > 0 ? $ttl : self::default_ttl() );
	}

	/**
	 * Removes a single cached value.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success.
	 */
	public function delete( string $key ): bool {
		return delete_transient( $this->prefixed_key( $key ) );
	}

	/**
	 * Removes every transient stored by this cache.
	 *
	 * @return bool True on success.
	 */
	public function flush(): bool {
		global $wpdb;

		$like         = $wpdb->esc_like( '_transient_' . self::PREFIX ) . '%';
		$timeout_like = $wpdb->esc_like( '_transient_timeout_' . self::PREFIX ) . '%';

		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$like,
				$timeout_like
			)
		);

		return false !== $result;
	}

	/**
	 * Namespaces a cache key to avoid collisions with unrelated transients.
	 *
	 * @param string $key Raw cache key.
	 * @return string Prefixed transient name.
	 */
	private function prefixed_key( string $key ): string {
		return self::PREFIX . $key;
	}

	/**
	 * Resolves the default TTL from the theme's `.env` (`SOLAR_TEMPLATE_CACHE_TTL`), falling back
	 * to self::FALLBACK_DEFAULT_TTL when unset or not a positive number.
	 *
	 * @return int
	 */
	private static function default_ttl(): int {
		$configured_ttl = (int) Env::get( 'SOLAR_TEMPLATE_CACHE_TTL', self::FALLBACK_DEFAULT_TTL );

		return $configured_ttl > 0 ? $configured_ttl : self::FALLBACK_DEFAULT_TTL;
	}
}
