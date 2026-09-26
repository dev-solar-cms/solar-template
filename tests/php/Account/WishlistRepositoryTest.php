<?php
/**
 * Created: 2026-09-26 17:40 CEST
 * Role: Unit test for Solar_Template\Account\WishlistRepository.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify add/remove/toggle/count/lookup behaviour against an in-memory `$wpdb` double (no
 *          real WordPress/MySQL install available here).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\WishlistRepository;

/**
 * @covers \Solar_Template\Account\WishlistRepository
 */
final class WishlistRepositoryTest extends TestCase {

	/**
	 * Toggling a product not yet wishlisted adds it and reports the new "wishlisted" state.
	 *
	 * @return void
	 */
	public function test_toggle_adds_a_product_not_yet_wishlisted(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$this->assertFalse( $repository->is_wishlisted( 1, 42 ) );
		$this->assertTrue( $repository->toggle( 1, 42 ) );
		$this->assertTrue( $repository->is_wishlisted( 1, 42 ) );
	}

	/**
	 * Toggling an already-wishlisted product removes it and reports the new state.
	 *
	 * @return void
	 */
	public function test_toggle_removes_an_already_wishlisted_product(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$repository->add( 1, 42 );

		$this->assertFalse( $repository->toggle( 1, 42 ) );
		$this->assertFalse( $repository->is_wishlisted( 1, 42 ) );
	}

	/**
	 * count_for() only counts the given user's own wishlisted products.
	 *
	 * @return void
	 */
	public function test_count_for_is_scoped_to_the_user(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$repository->add( 1, 10 );
		$repository->add( 1, 11 );
		$repository->add( 2, 12 );

		$this->assertSame( 2, $repository->count_for( 1 ) );
		$this->assertSame( 1, $repository->count_for( 2 ) );
		$this->assertSame( 0, $repository->count_for( 3 ) );
	}

	/**
	 * product_ids_for() returns only the given user's own product IDs, newest first.
	 *
	 * @return void
	 */
	public function test_product_ids_for_returns_newest_first(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$repository->add( 1, 10 );
		$repository->add( 1, 11 );

		$this->assertSame( array( 11, 10 ), $repository->product_ids_for( 1 ) );
	}
}
