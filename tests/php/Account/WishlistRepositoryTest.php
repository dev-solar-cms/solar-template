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

	/**
	 * wishlisted_ids_for() returns only the subset of the given product IDs the user actually
	 * wishlisted, scoped to that user, with a single query regardless of how many IDs are checked.
	 *
	 * @return void
	 */
	public function test_wishlisted_ids_for_returns_only_the_matching_subset(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$repository->add( 1, 10 );
		$repository->add( 1, 12 );
		$repository->add( 2, 11 ); // Different user — must never be returned for user 1.

		$this->assertSame( array( 10, 12 ), $repository->wishlisted_ids_for( 1, array( 10, 11, 12, 13 ) ) );
	}

	/**
	 * @return void
	 */
	public function test_wishlisted_ids_for_returns_an_empty_array_for_an_empty_id_list(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$this->assertSame( array(), $repository->wishlisted_ids_for( 1, array() ) );
	}

	/**
	 * @return void
	 */
	public function test_wishlisted_ids_for_returns_an_empty_array_when_none_match(): void {
		$repository = new WishlistRepository( new FakeWpdb() );

		$repository->add( 1, 99 );

		$this->assertSame( array(), $repository->wishlisted_ids_for( 1, array( 10, 11 ) ) );
	}
}
