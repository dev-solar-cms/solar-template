<?php
/**
 * Created: 2026-09-26 14:00 CEST
 * Role: Order status → display label/badge mapping (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Translate a WooCommerce order status into the theme's own label/badge/icon, shared by
 *          the dashboard's recent orders table and the orders list page, so both present the exact
 *          same status vocabulary. Split into a pure `describe_status()` (unit-tested) and a thin
 *          `describe_order()` wrapper (WC_Order-dependent, Docker-verified only), same convention
 *          as Solar_Template\Blog\ReadingTime.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps a WooCommerce order status to the theme's own label/badge/icon.
 */
final class OrderStatusPresenter {

	/**
	 * Status descriptors, keyed by WooCommerce's bare status slug (no `wc-` prefix). Any status not
	 * listed here (a custom status a plugin might add) falls back to a neutral descriptor using
	 * WooCommerce's own status label.
	 *
	 * @return array<string, array{badge: string, icon: string, label: string}>
	 */
	private static function descriptors(): array {
		return array(
			'pending'    => array(
				'badge' => 'pending',
				'icon'  => '⏳',
				'label' => __( 'Awaiting payment', 'solar-template' ),
			),
			'on-hold'    => array(
				'badge' => 'transit',
				'icon'  => '⏳',
				'label' => __( 'On hold', 'solar-template' ),
			),
			'processing' => array(
				'badge' => 'transit',
				'icon'  => '📦',
				'label' => __( 'In transit', 'solar-template' ),
			),
			'completed'  => array(
				'badge' => 'delivered',
				'icon'  => '✓',
				'label' => __( 'Delivered', 'solar-template' ),
			),
			'cancelled'  => array(
				'badge' => 'cancelled',
				'icon'  => '✕',
				'label' => __( 'Cancelled', 'solar-template' ),
			),
			'refunded'   => array(
				'badge' => 'cancelled',
				'icon'  => '↩',
				'label' => __( 'Refunded', 'solar-template' ),
			),
			'failed'     => array(
				'badge' => 'cancelled',
				'icon'  => '✕',
				'label' => __( 'Failed', 'solar-template' ),
			),
		);
	}

	/**
	 * Pure mapping from a bare status slug to its descriptor. Falls back to a neutral "pending"-style
	 * badge with $fallback_label (typically WooCommerce's own `wc_get_order_status_name()`) for a
	 * status this theme doesn't otherwise recognise.
	 *
	 * @param string      $status         Bare order status slug (no `wc-` prefix).
	 * @param string|null $fallback_label Label to use when $status isn't recognised.
	 * @return array{badge: string, icon: string, label: string}
	 */
	public static function describe_status( string $status, ?string $fallback_label = null ): array {
		$descriptors = self::descriptors();

		if ( isset( $descriptors[ $status ] ) ) {
			return $descriptors[ $status ];
		}

		return array(
			'badge' => 'pending',
			'icon'  => '•',
			'label' => $fallback_label ?? ucfirst( $status ),
		);
	}

	/**
	 * Reports whether the given bare status slug is considered "in progress" for the dashboard's
	 * stat card (payment pending, on hold, or being prepared/shipped).
	 *
	 * @param string $status Bare order status slug (no `wc-` prefix).
	 * @return bool
	 */
	public static function is_in_progress( string $status ): bool {
		return in_array( $status, array( 'pending', 'on-hold', 'processing' ), true );
	}

	/**
	 * Reports whether the given bare status slug allows re-ordering ("Reorder" only makes sense for
	 * an order that was actually fulfilled).
	 *
	 * @param string $status Bare order status slug (no `wc-` prefix).
	 * @return bool
	 */
	public static function is_reorderable( string $status ): bool {
		return 'completed' === $status;
	}

	/**
	 * Thin `WC_Order`-dependent wrapper around self::describe_status().
	 *
	 * @param \WC_Order $order Order to describe.
	 * @return array{badge: string, icon: string, label: string}
	 */
	public static function describe_order( \WC_Order $order ): array {
		return self::describe_status( $order->get_status(), wc_get_order_status_name( $order->get_status() ) );
	}
}
