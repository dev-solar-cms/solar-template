<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for Solar_Template\Support\TransientCache.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify the CacheInterface contract (get/set/delete + missing-key default) against the
 *          in-memory transients stand-in defined in tests/php/bootstrap.php.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Support;

use PHPUnit\Framework\TestCase;
use Solar_Template\Support\TransientCache;

/**
 * @covers \Solar_Template\Support\TransientCache
 */
final class TransientCacheTest extends TestCase {

	/**
	 * A value stored with set() is returned as-is by get().
	 *
	 * @return void
	 */
	public function test_set_then_get_returns_the_stored_value(): void {
		$cache = new TransientCache();

		$cache->set( 'cart_count', 4 );

		$this->assertSame( 4, $cache->get( 'cart_count' ) );
	}

	/**
	 * get() falls back to the given default when the key was never set.
	 *
	 * @return void
	 */
	public function test_get_returns_default_when_key_is_missing(): void {
		$cache = new TransientCache();

		$this->assertSame( 'fallback', $cache->get( 'never_set', 'fallback' ) );
	}

	/**
	 * delete() removes a previously stored value.
	 *
	 * @return void
	 */
	public function test_delete_removes_the_value(): void {
		$cache = new TransientCache();
		$cache->set( 'to_remove', 'value' );

		$this->assertTrue( $cache->delete( 'to_remove' ) );
		$this->assertNull( $cache->get( 'to_remove' ) );
	}

	/**
	 * set() with no explicit $ttl resolves the theme's `.env`-configured `SOLAR_TEMPLATE_CACHE_TTL`
	 * (via Solar_Template\Support\Env), rather than always using its one-hour fallback.
	 *
	 * @return void
	 */
	public function test_default_ttl_reads_the_env_configured_value(): void {
		putenv( 'SOLAR_TEMPLATE_CACHE_TTL=120' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- test-only: simulates the theme's own `.env` value without needing a real file on disk (this suite runs without WordPress or a filesystem `.env`, see tests/php/bootstrap.php's own docblock).
		$_ENV['SOLAR_TEMPLATE_CACHE_TTL'] = '120';

		( new TransientCache() )->set( 'env_ttl_probe', 'value' );

		global $solar_template_test_transient_ttls;

		$this->assertSame( 120, $solar_template_test_transient_ttls['solar_template_env_ttl_probe'] );

		putenv( 'SOLAR_TEMPLATE_CACHE_TTL' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- restores the process environment this test itself changed above.
		unset( $_ENV['SOLAR_TEMPLATE_CACHE_TTL'] );
	}

	/**
	 * set() with no explicit $ttl falls back to one hour when the `.env` value is absent/invalid.
	 *
	 * @return void
	 */
	public function test_default_ttl_falls_back_to_one_hour_when_env_value_is_missing(): void {
		putenv( 'SOLAR_TEMPLATE_CACHE_TTL' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- test-only: clears any value a previous test (or a real local .env) may have leaked into the process environment.
		unset( $_ENV['SOLAR_TEMPLATE_CACHE_TTL'] );

		( new TransientCache() )->set( 'fallback_ttl_probe', 'value' );

		global $solar_template_test_transient_ttls;

		$this->assertSame( HOUR_IN_SECONDS, $solar_template_test_transient_ttls['solar_template_fallback_ttl_probe'] );

		putenv( 'SOLAR_TEMPLATE_CACHE_TTL' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- restores the process environment for whichever test runs next.
		unset( $_ENV['SOLAR_TEMPLATE_CACHE_TTL'] );
	}
}
