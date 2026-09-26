<?php
/**
 * Created: 2026-09-26 15:30 CEST
 * Role: Order confirmation page view model (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map a real, just-placed WC_Order into the plain view model
 *          woocommerce/checkout/thankyou.php renders, so that override template only displays what
 *          this class already computed — same convention as CartView/CheckoutFieldsLayout.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the order confirmation page's view model.
 */
final class OrderConfirmation {

	/**
	 * Returns the confirmation page's view model for a given order, or an empty array when no order
	 * is available (e.g. a direct visit to the confirmation URL without a real order).
	 *
	 * @param WC_Order|false|null $order Order to build the view model for.
	 * @return array{
	 *     order_number: string,
	 *     customer_first_name: string,
	 *     delivery_estimate: string,
	 *     total_html: string,
	 *     shipping_address: string,
	 *     items: array<int, array{name: string, meta: string, total_html: string}>,
	 *     customer_email: string,
	 * }
	 */
	public static function view_model( $order ): array {
		if ( ! $order instanceof WC_Order ) {
			return array();
		}

		return array(
			'order_number'        => $order->get_order_number(),
			'customer_first_name' => $order->get_billing_first_name(),
			'delivery_estimate'   => self::delivery_estimate_label( $order ),
			'total_html'          => $order->get_formatted_order_total(),
			'shipping_address'    => self::shipping_address( $order ),
			'items'               => self::items( $order ),
			'customer_email'      => $order->get_billing_email(),
		);
	}

	/**
	 * Returns one view model per real order line item.
	 *
	 * @param WC_Order $order Order to read line items from.
	 * @return array<int, array{name: string, meta: string, total_html: string}>
	 */
	private static function items( WC_Order $order ): array {
		$items = array();

		foreach ( $order->get_items() as $item ) {
			$items[] = array(
				'name'       => $item->get_name(),
				'meta'       => wp_strip_all_tags( wc_display_item_meta( $item, array( 'echo' => false ) ) ),
				'total_html' => $order->get_formatted_line_subtotal( $item ),
			);
		}

		return $items;
	}

	/**
	 * Returns the order's shipping address, falling back to its billing address when no shipping
	 * address was collected (e.g. a virtual/downloadable-only order).
	 *
	 * @param WC_Order $order Order to read the address from.
	 * @return string
	 */
	private static function shipping_address( WC_Order $order ): string {
		$address = $order->get_formatted_shipping_address();

		return $address ? $address : $order->get_formatted_billing_address();
	}

	/**
	 * Returns a formatted estimated delivery date range, computed from the order's real creation
	 * date plus a filterable lead time (in days) — a future Group 10 admin screen's extension point,
	 * same convention as Product\ProductBadges' own filterable lead time.
	 *
	 * @param WC_Order $order Order to compute the estimate from.
	 * @return string
	 */
	private static function delivery_estimate_label( WC_Order $order ): string {
		$lead_days = apply_filters( 'solar_template_order_confirmation_delivery_lead_days', array( 3, 5 ), $order );
		$min_days  = is_array( $lead_days ) && isset( $lead_days[0] ) ? (int) $lead_days[0] : 3;
		$max_days  = is_array( $lead_days ) && isset( $lead_days[1] ) ? (int) $lead_days[1] : 5;
		$created   = $order->get_date_created();

		if ( ! $created ) {
			return '';
		}

		$date_format = get_option( 'date_format' );
		$starts_at   = wp_date( $date_format, $created->getTimestamp() + $min_days * DAY_IN_SECONDS );
		$ends_at     = wp_date( $date_format, $created->getTimestamp() + $max_days * DAY_IN_SECONDS );

		if ( $starts_at === $ends_at ) {
			return $starts_at;
		}

		return sprintf(
			/* translators: 1: range start date, 2: range end date. */
			__( '%1$s – %2$s', 'solar-template' ),
			$starts_at,
			$ends_at
		);
	}
}
