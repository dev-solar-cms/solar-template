<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Minimal WC_Order_Item_Product stand-in for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as WCProductStub.php — covers only what Solar_Template\Product\EngravingCart
 *          actually calls (never a full WooCommerce install here).
 *
 * @package Solar_Template
 */

if ( class_exists( 'WC_Order_Item_Product', false ) ) {
	return;
}

/**
 * Minimal stand-in for WooCommerce's WC_Order_Item_Product.
 */
class WC_Order_Item_Product {

	/**
	 * @var array<int, array{0: string, 1: mixed}>
	 */
	public array $added_meta = array();

	/**
	 * @param string $key   Meta key/label.
	 * @param mixed  $value Meta value.
	 * @return void
	 */
	public function add_meta_data( string $key, $value ): void {
		$this->added_meta[] = array( $key, $value );
	}
}
