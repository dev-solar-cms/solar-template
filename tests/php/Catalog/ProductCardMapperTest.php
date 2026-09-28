<?php
/**
 * Created: 2026-09-28 13:30 CEST
 * Role: Unit test for Solar_Template\Catalog\ProductCardMapper.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert map()'s own data mapping, and — the actual point of this test class —
 *          map_many()'s batched wishlist resolution: a grid of N products must resolve wishlist
 *          state with exactly one query, never one per card.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Catalog;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\WishlistRepository;
use Solar_Template\Catalog\ProductCardMapper;
use Solar_Template\Tests\Account\FakeWpdb;

final class ProductCardMapperTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$GLOBALS['solar_template_test_logged_in_user_id'] = 0;
		$GLOBALS['solar_template_test_terms']             = array();
		$GLOBALS['wpdb']                                  = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- test-only stand-in for the real `global $wpdb`, reset after each test.

		parent::tearDown();
	}

	/**
	 * @param int $id Product ID.
	 * @return \WC_Product
	 */
	private function product( int $id ): \WC_Product {
		$product = new \WC_Product( $id );
		$product->solar_template_test_set_name( "Product {$id}" );
		$product->solar_template_test_set_price( 10.0 );

		return $product;
	}

	/**
	 * @return void
	 */
	public function test_map_uses_the_given_in_wishlist_value_without_querying_anything(): void {
		$args = ProductCardMapper::map( $this->product( 1 ), true );

		$this->assertTrue( $args['in_wishlist'] );

		$args = ProductCardMapper::map( $this->product( 1 ), false );

		$this->assertFalse( $args['in_wishlist'] );
	}

	/**
	 * @return void
	 */
	public function test_map_computes_the_discount_percent_and_badge_when_on_sale(): void {
		$product = $this->product( 1 );
		$product->solar_template_test_set_regular_price( 100.0 );
		$product->solar_template_test_set_price( 75.0 );
		$product->solar_template_test_set_on_sale( true );

		$args = ProductCardMapper::map( $product, false );

		$this->assertSame( 25, $args['discount_percent'] );
		$this->assertSame( 'sale', $args['badge']['type'] );
	}

	/**
	 * @return void
	 */
	public function test_map_has_no_badge_when_not_on_sale(): void {
		$args = ProductCardMapper::map( $this->product( 1 ), false );

		$this->assertNull( $args['badge'] );
		$this->assertNull( $args['discount_percent'] );
	}

	/**
	 * The actual point of this whole test class: mapping several products at once resolves wishlist
	 * state with a single batched query, not one per product.
	 *
	 * @return void
	 */
	public function test_map_many_resolves_wishlist_state_with_a_single_query_for_a_logged_in_user(): void {
		$GLOBALS['solar_template_test_logged_in_user_id'] = 7;
		$fake_wpdb                                        = new FakeWpdb();
		$GLOBALS['wpdb']                                  = $fake_wpdb; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- test-only stand-in for the real `global $wpdb`.

		( new WishlistRepository( $fake_wpdb ) )->add( 7, 2 );

		$products = array( $this->product( 1 ), $this->product( 2 ), $this->product( 3 ) );

		$mapped = ProductCardMapper::map_many( $products );

		$this->assertSame( 1, $fake_wpdb->get_col_calls );
		$this->assertFalse( $mapped[0]['in_wishlist'] );
		$this->assertTrue( $mapped[1]['in_wishlist'] );
		$this->assertFalse( $mapped[2]['in_wishlist'] );
	}

	/**
	 * A logged-out visitor never triggers a wishlist query at all — every card maps to `false`.
	 *
	 * @return void
	 */
	public function test_map_many_skips_the_wishlist_query_entirely_for_a_logged_out_visitor(): void {
		$fake_wpdb       = new FakeWpdb();
		$GLOBALS['wpdb'] = $fake_wpdb; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- test-only stand-in for the real `global $wpdb`.

		$mapped = ProductCardMapper::map_many( array( $this->product( 1 ), $this->product( 2 ) ) );

		$this->assertSame( 0, $fake_wpdb->get_col_calls );
		$this->assertFalse( $mapped[0]['in_wishlist'] );
		$this->assertFalse( $mapped[1]['in_wishlist'] );
	}

	/**
	 * @return void
	 */
	public function test_map_many_returns_an_empty_array_for_an_empty_product_list(): void {
		$this->assertSame( array(), ProductCardMapper::map_many( array() ) );
	}
}
