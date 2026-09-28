<?php
/**
 * Created: 2026-09-28 16:30 CEST
 * Role: Unit test for Solar_Template\Account\CustomerOrders.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert for_user() merges the given user ID into `'customer'` alongside whatever extra
 *          args the caller passed, unchanged — the property every one of its 5 real callers
 *          (Account\Dashboard ×2, Account\SupportRequestController, Account\OrdersView,
 *          Account\AccountDeletion) relies on to keep its own specific query shape.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\CustomerOrders;

final class CustomerOrdersTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$GLOBALS['solar_template_test_orders_by_user']          = array();
		$GLOBALS['solar_template_test_wc_get_orders_calls']     = 0;
		$GLOBALS['solar_template_test_wc_get_orders_last_args'] = array();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_for_user_merges_the_user_id_with_no_extra_args(): void {
		$GLOBALS['solar_template_test_orders_by_user'][7] = array( 'order-a', 'order-b' );

		$this->assertSame( array( 'order-a', 'order-b' ), CustomerOrders::for_user( 7 ) );
		$this->assertSame( 1, $GLOBALS['solar_template_test_wc_get_orders_calls'] );
	}

	/**
	 * @return void
	 */
	public function test_for_user_preserves_every_caller_specific_extra_arg(): void {
		$GLOBALS['solar_template_test_orders_by_user'][9] = array( 'order-c' );

		CustomerOrders::for_user(
			9,
			array(
				'limit'   => 3,
				'orderby' => 'date',
				'order'   => 'DESC',
				'return'  => 'objects',
			)
		);

		$this->assertSame(
			array(
				'customer' => 9,
				'limit'    => 3,
				'orderby'  => 'date',
				'order'    => 'DESC',
				'return'   => 'objects',
			),
			$GLOBALS['solar_template_test_wc_get_orders_last_args']
		);
	}
}
