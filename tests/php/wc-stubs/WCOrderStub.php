<?php
/**
 * Created: 2026-09-28 14:30 CEST
 * Role: Minimal WC_Order stand-in for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as WCProductStub.php — covers only what Solar_Template\Account\OrdersView's
 *          tab-counting/filtering logic actually calls (never a full WooCommerce install here); the
 *          full order-card mapping (Solar_Template\Account\OrdersView::map_order()) stays
 *          Docker-verified only, same convention as OrderStatusPresenter::describe_order().
 *
 * @package Solar_Template
 */

if ( class_exists( 'WC_Order', false ) ) {
	return;
}

/**
 * Minimal stand-in for WooCommerce's WC_Order.
 */
class WC_Order {

	/**
	 * @var int
	 */
	protected int $id;

	/**
	 * @var string
	 */
	protected string $status;

	/**
	 * @param int    $id     Order ID.
	 * @param string $status Bare order status slug (no `wc-` prefix).
	 */
	public function __construct( int $id = 0, string $status = 'pending' ) {
		$this->id     = $id;
		$this->status = $status;
	}

	/**
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}
}
