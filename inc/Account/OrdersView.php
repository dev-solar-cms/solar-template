<?php
/**
 * Created: 2026-09-26 16:00 CEST
 * Role: My Account orders list view-model (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the orders list page's status tabs (with real counts) and order cards (with
 *          thumbnails and status-appropriate actions) from the logged-in customer's real orders.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the orders list page's tabs and order cards.
 */
final class OrdersView {

	private const PER_PAGE = 10;

	/**
	 * @param int    $user_id     Logged-in customer's user ID.
	 * @param string $active_tab  Currently selected tab key.
	 * @return array<int, array{key: string, label: string, count: int, url: string, is_active: bool}>
	 */
	public static function tabs( int $user_id, string $active_tab ): array {
		$orders = self::customer_orders( $user_id );

		$counts = array(
			'all'         => count( $orders ),
			'in_progress' => 0,
			'delivered'   => 0,
			'returns'     => 0,
		);

		foreach ( $orders as $order ) {
			$status = $order->get_status();

			if ( OrderStatusPresenter::is_in_progress( $status ) ) {
				++$counts['in_progress'];
			} elseif ( 'completed' === $status ) {
				++$counts['delivered'];
			} elseif ( 'refunded' === $status ) {
				++$counts['returns'];
			}
		}

		$labels = array(
			'all'         => __( 'All', 'solar-template' ),
			'in_progress' => __( 'In progress', 'solar-template' ),
			'delivered'   => __( 'Delivered', 'solar-template' ),
			'returns'     => __( 'Returns', 'solar-template' ),
		);

		$tabs = array();

		foreach ( $labels as $key => $label ) {
			$tabs[] = array(
				'key'       => $key,
				'label'     => $label,
				'count'     => $counts[ $key ],
				'url'       => 'all' === $key
					? wc_get_account_endpoint_url( 'orders' )
					: add_query_arg( 'orders-tab', $key, wc_get_account_endpoint_url( 'orders' ) ),
				'is_active' => $key === $active_tab,
			);
		}

		return $tabs;
	}

	/**
	 * @param int    $user_id Logged-in customer's user ID.
	 * @param string $tab     Active tab key (see self::tabs()).
	 * @param int    $page    1-based page number.
	 * @return array{orders: array<int, array>, current_page: int, max_pages: int}
	 */
	public static function orders_for_tab( int $user_id, string $tab, int $page = 1 ): array {
		$all_orders = array_values(
			array_filter(
				self::customer_orders( $user_id ),
				static fn( \WC_Order $order ): bool => self::order_matches_tab( $order, $tab )
			)
		);

		$max_pages = (int) max( 1, ceil( count( $all_orders ) / self::PER_PAGE ) );
		$page      = max( 1, min( $page, $max_pages ) );
		$slice     = array_slice( $all_orders, ( $page - 1 ) * self::PER_PAGE, self::PER_PAGE );

		return array(
			'orders'       => array_map( array( self::class, 'map_order' ), $slice ),
			'current_page' => $page,
			'max_pages'    => $max_pages,
		);
	}

	/**
	 * @param \WC_Order $order Order to map.
	 * @return array{number: string, date: string, total: string, status: array, item_count: int, thumbnails: string[], extra_item_count: int, view_url: string, reorder_url: string|null}
	 */
	public static function map_order( \WC_Order $order ): array {
		$thumbnails = array();

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( $product && $product->get_image_id() ) {
				$thumbnails[] = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
			}
		}

		$item_count = $order->get_item_count();

		return array(
			'number'           => $order->get_order_number(),
			'date'             => wc_format_datetime( $order->get_date_created() ),
			'total'            => $order->get_formatted_order_total(),
			'status'           => OrderStatusPresenter::describe_order( $order ),
			'item_count'       => $item_count,
			'thumbnails'       => array_slice( $thumbnails, 0, 3 ),
			'extra_item_count' => max( 0, $item_count - 3 ),
			'view_url'         => $order->get_view_order_url(),
			'reorder_url'      => OrderStatusPresenter::is_reorderable( $order->get_status() )
				? ReorderHandler::url( $order->get_id() )
				: null,
		);
	}

	/**
	 * @param \WC_Order $order Order to test.
	 * @param string    $tab   Tab key.
	 * @return bool
	 */
	private static function order_matches_tab( \WC_Order $order, string $tab ): bool {
		$status = $order->get_status();

		return match ( $tab ) {
			'in_progress' => OrderStatusPresenter::is_in_progress( $status ),
			'delivered'   => 'completed' === $status,
			'returns'     => 'refunded' === $status,
			default       => true,
		};
	}

	/**
	 * @param int $user_id Logged-in customer's user ID.
	 * @return \WC_Order[] Every non-trashed order this customer has ever placed, newest first.
	 */
	private static function customer_orders( int $user_id ): array {
		return wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
				'orderby'  => 'date',
				'order'    => 'DESC',
			)
		);
	}
}
