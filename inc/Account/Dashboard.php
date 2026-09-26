<?php
/**
 * Created: 2026-09-26 14:20 CEST
 * Role: My Account dashboard view-model (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the dashboard's stat cards and recent orders table from the logged-in customer's
 *          real orders and wishlist. The "S.A.V." stat count is hardcoded to 0 here and wired to
 *          its real repository once that feature exists (see
 *          Solar_Template\Account\SupportRequestRepository, added a few steps later), same
 *          incremental convention as the front page sections.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the My Account dashboard's view-model.
 */
final class Dashboard {

	/**
	 * @param int $user_id Logged-in customer's user ID.
	 * @return array{in_progress: int, total: int, wishlist: int, support_requests: int}
	 */
	public static function stats( int $user_id ): array {
		$statuses    = self::customer_order_statuses( $user_id );
		$in_progress = count( array_filter( $statuses, array( OrderStatusPresenter::class, 'is_in_progress' ) ) );

		global $wpdb;

		return array(
			'in_progress'      => $in_progress,
			'total'            => count( $statuses ),
			'wishlist'         => ( new WishlistRepository( $wpdb ) )->count_for( $user_id ),
			'support_requests' => 0,
		);
	}

	/**
	 * @param int $user_id  Logged-in customer's user ID.
	 * @param int $limit    Maximum number of orders to return.
	 * @return array<int, array{number: string, date: string, item_count: int, status: array, total: string, view_url: string, reorder_url: string|null}>
	 */
	public static function recent_orders( int $user_id, int $limit = 3 ): array {
		$orders = wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => $limit,
				'orderby'  => 'date',
				'order'    => 'DESC',
			)
		);

		return array_map( array( self::class, 'map_order' ), $orders );
	}

	/**
	 * @param \WC_Order $order Order to map.
	 * @return array{number: string, date: string, item_count: int, status: array, total: string, view_url: string, reorder_url: string|null}
	 */
	public static function map_order( \WC_Order $order ): array {
		return array(
			'number'      => $order->get_order_number(),
			'date'        => wc_format_datetime( $order->get_date_created() ),
			'item_count'  => $order->get_item_count(),
			'status'      => OrderStatusPresenter::describe_order( $order ),
			'total'       => $order->get_formatted_order_total(),
			'view_url'    => $order->get_view_order_url(),
			'reorder_url' => OrderStatusPresenter::is_reorderable( $order->get_status() )
				? ReorderHandler::url( $order->get_id() )
				: null,
		);
	}

	/**
	 * @param int $user_id Logged-in customer's user ID.
	 * @return string[] Bare status slugs (no `wc-` prefix) of every non-trashed order this customer
	 *                   has ever placed.
	 */
	private static function customer_order_statuses( int $user_id ): array {
		$orders = wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
				'return'   => 'objects',
			)
		);

		return array_map(
			static fn( \WC_Order $order ): string => $order->get_status(),
			$orders
		);
	}
}
