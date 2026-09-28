<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Minimal WC_Cart stand-in for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as WCProductStub.php — covers only what Solar_Template\Product\EngravingCart
 *          actually calls (never a full WooCommerce install here).
 *
 * @package Solar_Template
 */

if ( class_exists( 'WC_Cart', false ) ) {
	return;
}

/**
 * Minimal stand-in for WooCommerce's WC_Cart.
 */
class WC_Cart {

	/**
	 * @var array<string, array<string, mixed>>
	 */
	public array $items = array();

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function get_cart(): array {
		return $this->items;
	}
}
