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
}
