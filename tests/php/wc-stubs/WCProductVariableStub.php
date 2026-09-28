<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Minimal WC_Product_Variable stand-in for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as WCProductStub.php — covers only what Solar_Template\Product\
 *          ProductVariations actually calls (never a full WooCommerce install here).
 *
 * @package Solar_Template
 */

require_once __DIR__ . '/WCProductStub.php';

if ( class_exists( 'WC_Product_Variable', false ) ) {
	return;
}

/**
 * Minimal stand-in for WooCommerce's WC_Product_Variable.
 */
class WC_Product_Variable extends WC_Product {

	/**
	 * @var array<int, array<string, mixed>>
	 */
	protected array $available_variations = array();

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_available_variations(): array {
		return $this->available_variations;
	}

	/**
	 * Test-only helper — not part of the real WC_Product_Variable API.
	 *
	 * @param array<int, array<string, mixed>> $variations Raw `get_available_variations()`-shaped payload.
	 * @return void
	 */
	public function solar_template_test_set_available_variations( array $variations ): void {
		$this->available_variations = $variations;
	}
}
