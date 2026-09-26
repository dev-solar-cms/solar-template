<?php
/**
 * Created: 2026-09-26 15:00 CEST
 * Role: Unit test for Solar_Template\Account\OrderStatusPresenter.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify the pure status → label/badge mapping and the "in progress"/"reorderable"
 *          predicates, without any WooCommerce dependency.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\OrderStatusPresenter;

/**
 * @covers \Solar_Template\Account\OrderStatusPresenter
 */
final class OrderStatusPresenterTest extends TestCase {

	/**
	 * Every status this theme explicitly recognises maps to its own badge/label.
	 *
	 * @return void
	 */
	public function test_describes_known_statuses(): void {
		$this->assertSame( 'transit', OrderStatusPresenter::describe_status( 'processing' )['badge'] );
		$this->assertSame( 'delivered', OrderStatusPresenter::describe_status( 'completed' )['badge'] );
		$this->assertSame( 'cancelled', OrderStatusPresenter::describe_status( 'cancelled' )['badge'] );
		$this->assertSame( 'cancelled', OrderStatusPresenter::describe_status( 'refunded' )['badge'] );
		$this->assertSame( 'cancelled', OrderStatusPresenter::describe_status( 'failed' )['badge'] );
		$this->assertSame( 'pending', OrderStatusPresenter::describe_status( 'pending' )['badge'] );
		$this->assertSame( 'transit', OrderStatusPresenter::describe_status( 'on-hold' )['badge'] );
	}

	/**
	 * An unrecognised status falls back to a neutral badge using the provided fallback label.
	 *
	 * @return void
	 */
	public function test_falls_back_for_an_unknown_status(): void {
		$descriptor = OrderStatusPresenter::describe_status( 'checkout-draft', 'Draft' );

		$this->assertSame( 'pending', $descriptor['badge'] );
		$this->assertSame( 'Draft', $descriptor['label'] );
	}

	/**
	 * "In progress" covers payment-pending, on-hold and processing orders only.
	 *
	 * @return void
	 */
	public function test_is_in_progress(): void {
		$this->assertTrue( OrderStatusPresenter::is_in_progress( 'pending' ) );
		$this->assertTrue( OrderStatusPresenter::is_in_progress( 'on-hold' ) );
		$this->assertTrue( OrderStatusPresenter::is_in_progress( 'processing' ) );
		$this->assertFalse( OrderStatusPresenter::is_in_progress( 'completed' ) );
		$this->assertFalse( OrderStatusPresenter::is_in_progress( 'cancelled' ) );
	}

	/**
	 * Only a completed order can be reordered.
	 *
	 * @return void
	 */
	public function test_is_reorderable(): void {
		$this->assertTrue( OrderStatusPresenter::is_reorderable( 'completed' ) );
		$this->assertFalse( OrderStatusPresenter::is_reorderable( 'processing' ) );
		$this->assertFalse( OrderStatusPresenter::is_reorderable( 'cancelled' ) );
	}
}
