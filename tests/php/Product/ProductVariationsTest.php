<?php
/**
 * Created: 2026-09-28 11:45 CEST
 * Role: Unit test for Solar_Template\Product\ProductVariations.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: variations_payload() supplies the real, per-variation price assets/js/product.js resolves
 *          a Color/Size selection against and formats for display (decimals/separators/symbol
 *          position — see Solar_Template\Product\ProductController) — a silent regression in which
 *          real price gets attached to which variation combination has a direct financial impact
 *          (DECISIONS.md §8), unlike this class' purely label-building attribute_groups() method
 *          (display-only, left to the general backlog per that same decision). Covers: the nominal
 *          case (extraction of real variation_id/attributes/stock/price) and is_variable() detection.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Product;

use PHPUnit\Framework\TestCase;
use Solar_Template\Product\ProductVariations;

final class ProductVariationsTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$GLOBALS['solar_template_test_products'] = array();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_variations_payload_extracts_real_price_attributes_and_stock_per_variation(): void {
		$variation_a = new \WC_Product( 101 );
		$variation_a->solar_template_test_set_price( 59.90 );
		$variation_a->solar_template_test_set_in_stock( true );
		$GLOBALS['solar_template_test_products'][101] = $variation_a;

		$variation_b = new \WC_Product( 102 );
		$variation_b->solar_template_test_set_price( 64.90 );
		$variation_b->solar_template_test_set_in_stock( false );
		$GLOBALS['solar_template_test_products'][102] = $variation_b;

		$product = new \WC_Product_Variable( 100 );
		$product->solar_template_test_set_available_variations(
			array(
				array(
					'variation_id' => 101,
					'attributes'   => array(
						'attribute_pa_color' => 'red',
						'attribute_pa_size'  => 'm',
					),
				),
				array(
					'variation_id' => 102,
					'attributes'   => array(
						'attribute_pa_color' => 'blue',
						'attribute_pa_size'  => 'l',
					),
				),
			)
		);

		$payload = ProductVariations::variations_payload( $product );

		$this->assertCount( 2, $payload );

		$this->assertSame( 101, $payload[0]['variation_id'] );
		$this->assertSame(
			array(
				'pa_color' => 'red',
				'pa_size'  => 'm',
			),
			$payload[0]['attributes']
		);
		$this->assertTrue( $payload[0]['is_in_stock'] );
		$this->assertSame( 59.90, $payload[0]['display_price'] );

		$this->assertSame( 102, $payload[1]['variation_id'] );
		$this->assertFalse( $payload[1]['is_in_stock'] );
		$this->assertSame( 64.90, $payload[1]['display_price'] );
	}

	/**
	 * @return void
	 */
	public function test_variations_payload_skips_a_variation_whose_product_cannot_be_found(): void {
		$product = new \WC_Product_Variable( 200 );
		$product->solar_template_test_set_available_variations(
			array(
				array(
					'variation_id' => 201, // Never registered — wc_get_product() returns false for it.
					'attributes'   => array(),
				),
			)
		);

		$this->assertSame( array(), ProductVariations::variations_payload( $product ) );
	}

	/**
	 * @return void
	 */
	public function test_variations_payload_returns_an_empty_array_for_a_product_with_no_variations(): void {
		$this->assertSame( array(), ProductVariations::variations_payload( new \WC_Product_Variable( 300 ) ) );
	}

	/**
	 * @return void
	 */
	public function test_is_variable_detects_a_variable_product(): void {
		$this->assertTrue( ProductVariations::is_variable( new \WC_Product_Variable( 1 ) ) );
		$this->assertFalse( ProductVariations::is_variable( new \WC_Product( 2 ) ) );
	}
}
