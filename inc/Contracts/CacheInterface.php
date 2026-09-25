<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Part of the theme's dependency-inversion layer (Solar_Template\Contracts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define the contract used by the theme for short-lived caching, so the storage backend
 *          (currently WordPress transients) can be swapped later (e.g. for a real object cache)
 *          without changing any code that reads/writes cached values.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A minimal key/value cache with expiration.
 */
interface CacheInterface {

	/**
	 * Reads a cached value.
	 *
	 * @param string $key     Cache key.
	 * @param mixed  $default_value Value returned when the key is absent or expired.
	 * @return mixed The cached value, or $default_value.
	 */
	public function get( string $key, mixed $default_value = null ): mixed;

	/**
	 * Stores a value in the cache.
	 *
	 * @param string $key   Cache key.
	 * @param mixed  $value Value to store.
	 * @param int    $ttl   Time to live in seconds. 0 means "use the implementation's default".
	 * @return bool True on success.
	 */
	public function set( string $key, mixed $value, int $ttl = 0 ): bool;

	/**
	 * Removes a single cached value.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success.
	 */
	public function delete( string $key ): bool;

	/**
	 * Removes every value stored by this cache.
	 *
	 * @return bool True on success.
	 */
	public function flush(): bool;
}
