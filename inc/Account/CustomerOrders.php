<?php
/**
 * Created: 2026-09-28 16:30 CEST
 * Role: Shared "this customer's orders" query (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Single point of access for `wc_get_orders(['customer' => $user_id, ...])`, previously
 *          rebuilt independently in 5 places (Account\Dashboard::recent_orders()/
 *          customer_order_statuses(), Account\SupportRequestController::render_sav_page(),
 *          Account\OrdersView::customer_orders(), Account\AccountDeletion::maybe_handle_submission()).
 *          Every caller still passes its own specific extra args (limit/orderby/order/return) — this
 *          class only removes the duplicated call site itself, it does not unify or change any
 *          caller's own query shape (see each caller's own comment for why its args are what they
 *          are). Solar_Template\Account\OrdersView keeps its own local memoization on top of this
 *          (added before this class existed, see its own docblock) since it's the only caller that
 *          reads the same customer's orders more than once on a single page render.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads a customer's real WooCommerce orders.
 */
final class CustomerOrders {

	/**
	 * @param int                  $user_id Customer's user ID.
	 * @param array<string, mixed> $args    Extra `wc_get_orders()` args (e.g. `limit`/`orderby`/
	 *                                      `order`/`return`), merged with `'customer' => $user_id`.
	 * @return array<int, \WC_Order>|array<int, int> Whatever `wc_get_orders()` itself returns for
	 *                                                these args (an object list, or an ID list when
	 *                                                `'return' => 'ids'`).
	 */
	public static function for_user( int $user_id, array $args = array() ): array {
		return wc_get_orders( array_merge( array( 'customer' => $user_id ), $args ) );
	}
}
