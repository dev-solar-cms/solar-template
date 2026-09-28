<?php
/**
 * Created: 2026-09-28 11:45 CEST
 * Role: Unit test for Solar_Template\Product\EngravingCart.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: This class computes part of the price actually charged to the customer (the engraving
 *          surcharge, combined with the product/variation's real price) — a silent regression here
 *          has a direct financial impact, unlike most of the theme's other, purely display-oriented
 *          classes (see DECISIONS.md §8). Covers: text truncation to the configured max length, the
 *          surcharge recomputed from the real base price every time (never cumulated onto an
 *          already-adjusted price, including across a variable product's own real variation price —
 *          the combined case), and propagation onto the resulting order line item.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Product;

use PHPUnit\Framework\TestCase;
use Solar_Template\Product\EngravingCart;
use Solar_Template\Product\ProductEngraving;

final class EngravingCartTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$_POST                                   = array();
		$GLOBALS['solar_template_test_products'] = array();

		parent::tearDown();
	}

	/**
	 * @param int   $id         Product ID.
	 * @param float $price      Real product price.
	 * @param int   $max_length Configured max engraving length.
	 * @param float $surcharge  Configured engraving surcharge.
	 * @return \WC_Product
	 */
	private function engraved_product( int $id, float $price, int $max_length = 20, float $surcharge = 25.0 ): \WC_Product {
		$product = new \WC_Product( $id );
		$product->solar_template_test_set_price( $price );
		$product->solar_template_test_set_meta( ProductEngraving::META_ENABLED, 'yes' );
		$product->solar_template_test_set_meta( ProductEngraving::META_PRICE, (string) $surcharge );
		$product->solar_template_test_set_meta( ProductEngraving::META_MAX_LENGTH, (string) $max_length );

		return $product;
	}

	/**
	 * @return void
	 */
	public function test_add_cart_item_data_captures_and_truncates_the_submitted_text(): void {
		$product                                     = $this->engraved_product( 10, 100.0, 5 );
		$GLOBALS['solar_template_test_products'][10] = $product;

		$_POST['solar_template_engraving_enabled'] = '1';
		$_POST['solar_template_engraving_text']    = 'Hello World';

		$result = EngravingCart::add_cart_item_data( array(), 10, 0 );

		$this->assertSame( 'Hello', $result['solar_template_engraving']['text'] );
		$this->assertSame( 25.0, $result['solar_template_engraving']['surcharge'] );
		$this->assertArrayHasKey( 'unique_key', $result );
	}

	/**
	 * @return void
	 */
	public function test_add_cart_item_data_ignores_products_without_engraving_enabled(): void {
		$product                                     = new \WC_Product( 11 );
		$GLOBALS['solar_template_test_products'][11] = $product;

		$_POST['solar_template_engraving_enabled'] = '1';
		$_POST['solar_template_engraving_text']    = 'Hello';

		$result = EngravingCart::add_cart_item_data( array( 'foo' => 'bar' ), 11, 0 );

		$this->assertSame( array( 'foo' => 'bar' ), $result );
	}

	/**
	 * @return void
	 */
	public function test_add_cart_item_data_ignores_an_empty_submitted_text(): void {
		$product                                     = $this->engraved_product( 12, 50.0 );
		$GLOBALS['solar_template_test_products'][12] = $product;

		$_POST['solar_template_engraving_enabled'] = '1';
		$_POST['solar_template_engraving_text']    = '';

		$result = EngravingCart::add_cart_item_data( array(), 12, 0 );

		$this->assertArrayNotHasKey( 'solar_template_engraving', $result );
	}

	/**
	 * @return void
	 */
	public function test_add_cart_item_data_reads_the_variation_product_when_a_variation_is_selected(): void {
		$parent                                      = new \WC_Product( 13 );
		$variation                                   = $this->engraved_product( 14, 75.0 );
		$GLOBALS['solar_template_test_products'][13] = $parent;
		$GLOBALS['solar_template_test_products'][14] = $variation;

		$_POST['solar_template_engraving_enabled'] = '1';
		$_POST['solar_template_engraving_text']    = 'Hi';

		$result = EngravingCart::add_cart_item_data( array(), 13, 14 );

		$this->assertSame( 'Hi', $result['solar_template_engraving']['text'] );
	}

	/**
	 * The surcharge is recomputed from the real base product price on every call — a nominal case,
	 * proving the "never cumulated" contract holds even when WooCommerce recalculates cart totals
	 * more than once within the same request (its own documented real-world trigger).
	 *
	 * @return void
	 */
	public function test_adjust_cart_item_price_never_cumulates_the_surcharge_across_repeated_recalculations(): void {
		$base_product = new \WC_Product( 20 );
		$base_product->solar_template_test_set_price( 80.0 );
		$GLOBALS['solar_template_test_products'][20] = $base_product;

		$cart        = new \WC_Cart();
		$cart->items = array(
			'key1' => array(
				'data'                     => new \WC_Product( 20 ),
				'product_id'               => 20,
				'variation_id'             => 0,
				'solar_template_engraving' => array(
					'text'      => 'Hi',
					'surcharge' => 25.0,
				),
			),
		);

		EngravingCart::adjust_cart_item_price( $cart );
		$this->assertSame( 105.0, $cart->items['key1']['data']->get_price() );

		EngravingCart::adjust_cart_item_price( $cart );
		$this->assertSame( 105.0, $cart->items['key1']['data']->get_price() );
	}

	/**
	 * The combined case: a variable product's own real, selected variation price is what the
	 * surcharge is added to — never the parent product's price, and still never cumulated.
	 *
	 * @return void
	 */
	public function test_adjust_cart_item_price_combines_the_surcharge_with_the_real_variation_price(): void {
		$parent = new \WC_Product( 30 );
		$parent->solar_template_test_set_price( 999.0 ); // Must never be read — the cart item is a variation.
		$variation = new \WC_Product( 31 );
		$variation->solar_template_test_set_price( 120.0 );
		$GLOBALS['solar_template_test_products'][30] = $parent;
		$GLOBALS['solar_template_test_products'][31] = $variation;

		$cart        = new \WC_Cart();
		$cart->items = array(
			'key1' => array(
				'data'                     => new \WC_Product( 31 ),
				'product_id'               => 30,
				'variation_id'             => 31,
				'solar_template_engraving' => array(
					'text'      => 'Hi',
					'surcharge' => 25.0,
				),
			),
		);

		EngravingCart::adjust_cart_item_price( $cart );

		$this->assertSame( 145.0, $cart->items['key1']['data']->get_price() );

		// Recalculated again (e.g. a second `woocommerce_before_calculate_totals` pass): still 145,
		// never 145 + 25.
		EngravingCart::adjust_cart_item_price( $cart );
		$this->assertSame( 145.0, $cart->items['key1']['data']->get_price() );
	}

	/**
	 * @return void
	 */
	public function test_adjust_cart_item_price_ignores_a_cart_item_without_engraving(): void {
		$product = new \WC_Product( 40 );
		$product->solar_template_test_set_price( 50.0 );
		$GLOBALS['solar_template_test_products'][40] = $product;

		$cart      = new \WC_Cart();
		$line_item = new \WC_Product( 40 );
		$line_item->solar_template_test_set_price( 50.0 );
		$cart->items = array(
			'key1' => array(
				'data'         => $line_item,
				'product_id'   => 40,
				'variation_id' => 0,
			),
		);

		EngravingCart::adjust_cart_item_price( $cart );

		$this->assertSame( 50.0, $cart->items['key1']['data']->get_price() );
	}

	/**
	 * @return void
	 */
	public function test_add_order_item_meta_propagates_the_engraving_text_to_the_order_line_item(): void {
		$item = new \WC_Order_Item_Product();

		EngravingCart::add_order_item_meta( $item, 'any-key', array( 'solar_template_engraving' => array( 'text' => 'Engraved Text' ) ) );

		$this->assertSame( array( array( 'Engraving', 'Engraved Text' ) ), $item->added_meta );
	}

	/**
	 * @return void
	 */
	public function test_add_order_item_meta_does_nothing_without_engraving_data(): void {
		$item = new \WC_Order_Item_Product();

		EngravingCart::add_order_item_meta( $item, 'any-key', array() );

		$this->assertSame( array(), $item->added_meta );
	}
}
