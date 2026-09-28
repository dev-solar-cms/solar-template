<?php
/**
 * Created: 2026-09-28 14:30 CEST
 * Role: Unit test for Solar_Template\Account\OrdersView.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert self::tabs() and self::orders_for_tab() share the same memoized
 *          customer_orders() result on one page render (a single wc_get_orders() call), rather than
 *          each triggering its own. Full order-card content (self::map_order(), WC_Order-heavy) stays
 *          Docker-verified only, same convention as OrderStatusPresenter::describe_order() — this
 *          test picks a tab with no matching order so map_order() is never reached.
 *
 * Each test method uses its own, never-reused user ID: customer_orders() memoizes with a
 * function-local `static` cache (same convention as WishlistController::repository()), which persists
 * for the whole PHPUnit process — a repeated ID across test methods would silently read a previous
 * test's cached result instead of exercising a fresh call.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\OrdersView;

final class OrdersViewTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$GLOBALS['solar_template_test_orders_by_user']      = array();
		$GLOBALS['solar_template_test_wc_get_orders_calls'] = 0;

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_tabs_and_orders_for_tab_together_trigger_a_single_wc_get_orders_call(): void {
		$user_id = 5;

		$GLOBALS['solar_template_test_orders_by_user'][ $user_id ] = array(
			new \WC_Order( 1, 'processing' ),
			new \WC_Order( 2, 'processing' ),
			new \WC_Order( 3, 'on-hold' ),
		);

		// Same real-page-render sequence as woocommerce/myaccount/orders.php: both called once, on
		// the same request. "delivered" matches none of the fixture orders above, so this also never
		// reaches self::map_order() (WC_Order-heavy, out of this test's own scope — see the class
		// docblock).
		$tabs   = OrdersView::tabs( $user_id, 'all' );
		$result = OrdersView::orders_for_tab( $user_id, 'delivered' );

		$this->assertSame( 1, $GLOBALS['solar_template_test_wc_get_orders_calls'] );
		$this->assertSame( array(), $result['orders'] );

		$counts = array_column( $tabs, 'count', 'key' );
		$this->assertSame( 3, $counts['all'] );
		$this->assertSame( 3, $counts['in_progress'] );
		$this->assertSame( 0, $counts['delivered'] );
	}

	/**
	 * customer_orders() is memoized per user ID, not globally — a different user on the same request
	 * (e.g. a future admin-facing view) must never see another customer's cached orders.
	 *
	 * @return void
	 */
	public function test_customer_orders_memoization_is_scoped_per_user(): void {
		$GLOBALS['solar_template_test_orders_by_user'][105] = array( new \WC_Order( 1, 'processing' ) );
		$GLOBALS['solar_template_test_orders_by_user'][106] = array(
			new \WC_Order( 2, 'processing' ),
			new \WC_Order( 3, 'processing' ),
		);

		$tabs_user_105 = array_column( OrdersView::tabs( 105, 'all' ), 'count', 'key' );
		$tabs_user_106 = array_column( OrdersView::tabs( 106, 'all' ), 'count', 'key' );

		$this->assertSame( 1, $tabs_user_105['all'] );
		$this->assertSame( 2, $tabs_user_106['all'] );
		$this->assertSame( 2, $GLOBALS['solar_template_test_wc_get_orders_calls'] );
	}
}
