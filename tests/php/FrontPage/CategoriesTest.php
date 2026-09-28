<?php
/**
 * Created: 2026-09-28 14:10 CEST
 * Role: Unit test for Solar_Template\FrontPage\Categories.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert terms_for_ids() — the method extracted to replace a `get_term()` call per category
 *          with a single `get_terms(['include' => $ids])` call — resolves the real terms and restores
 *          the caller's own order (self::term_ids()'s product-count order, which `get_terms()` does
 *          not otherwise preserve), and degrades gracefully when an ID no longer resolves to a real
 *          term. self::categories() itself is not exercised here: it also depends on
 *          Support\WooCommerceStatus::is_active() and the theme's transient cache, both out of this
 *          étape's own scope (see the étape file).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\FrontPage;

use PHPUnit\Framework\TestCase;
use Solar_Template\FrontPage\Categories;

final class CategoriesTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$GLOBALS['solar_template_test_terms_by_taxonomy'] = array();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_terms_for_ids_preserves_the_given_order_regardless_of_get_terms_own_order(): void {
		$GLOBALS['solar_template_test_terms_by_taxonomy']['product_cat'] = array(
			new \WP_Term( 10, 'Rings' ),
			new \WP_Term( 20, 'Necklaces' ),
			new \WP_Term( 30, 'Bracelets' ),
		);

		// Deliberately not the same order as the fixture above (nor sorted) — the caller's own order
		// (by product count, already decided by self::term_ids()) must win.
		$terms = Categories::terms_for_ids( array( 30, 10, 20 ) );

		$this->assertSame( array( 30, 10, 20 ), array_map( static fn( \WP_Term $term ): int => $term->term_id, $terms ) );
	}

	/**
	 * @return void
	 */
	public function test_terms_for_ids_skips_an_id_that_no_longer_resolves_to_a_real_term(): void {
		$GLOBALS['solar_template_test_terms_by_taxonomy']['product_cat'] = array(
			new \WP_Term( 10, 'Rings' ),
		);

		$terms = Categories::terms_for_ids( array( 10, 999 ) );

		$this->assertCount( 1, $terms );
		$this->assertSame( 10, $terms[0]->term_id );
	}

	/**
	 * @return void
	 */
	public function test_terms_for_ids_returns_an_empty_array_when_nothing_resolves(): void {
		$this->assertSame( array(), Categories::terms_for_ids( array( 404 ) ) );
	}
}
