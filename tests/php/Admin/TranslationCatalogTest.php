<?php
/**
 * Created: 2026-09-27 10:05 CEST
 * Role: Unit test for Solar_Template\Admin\TranslationCatalog.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify placeholder/plural detection and the search/pagination logic behind the
 *          translation editor, independently of WordPress and of the real (large) catalog.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\TranslationCatalog;

/**
 * @covers \Solar_Template\Admin\TranslationCatalog
 */
final class TranslationCatalogTest extends TestCase {

	/**
	 * placeholders() extracts every distinct printf-style token, in first-seen order.
	 *
	 * @return void
	 */
	public function test_placeholders_extracts_every_distinct_token(): void {
		$this->assertSame( array( '%d' ), TranslationCatalog::placeholders( '%d product available' ) );
		$this->assertSame( array( '%1$s', '%2$d' ), TranslationCatalog::placeholders( '%1$s has %2$d items' ) );
		$this->assertSame( array(), TranslationCatalog::placeholders( 'Add to cart' ) );
	}

	/**
	 * has_plural() reflects whether the real catalog registered a plural form for a key.
	 *
	 * @return void
	 */
	public function test_has_plural_reflects_the_real_catalog(): void {
		$this->assertTrue( TranslationCatalog::has_plural( '%d product available' ) );
		$this->assertFalse( TranslationCatalog::has_plural( 'Add to cart' ) );
		$this->assertFalse( TranslationCatalog::has_plural( 'A key that does not exist at all' ) );
	}

	/**
	 * build_rows() with no search returns every key, one row per key, using the current
	 * translation when one exists and an empty string otherwise.
	 *
	 * @return void
	 */
	public function test_build_rows_with_no_search_returns_every_key(): void {
		$keys    = array( 'Add to cart', 'Cart', 'Search' );
		$current = array(
			'Cart' => array(
				'singular' => 'Panier',
				'plural'   => null,
				'context'  => '',
			),
		);

		$result = TranslationCatalog::build_rows( $keys, $current, '', 1, 50 );

		$this->assertSame( 3, $result['total'] );
		$this->assertSame( 1, $result['total_pages'] );
		$this->assertCount( 3, $result['rows'] );
		$this->assertSame( 'Panier', $result['rows'][1]['singular'] );
		$this->assertSame( '', $result['rows'][0]['singular'] );
	}

	/**
	 * build_rows() matches the search term against the key itself.
	 *
	 * @return void
	 */
	public function test_build_rows_search_matches_the_key(): void {
		$keys   = array( 'Add to cart', 'Cart', 'Search' );
		$result = TranslationCatalog::build_rows( $keys, array(), 'cart', 1, 50 );

		$this->assertSame( array( 'Add to cart', 'Cart' ), array_column( $result['rows'], 'key' ) );
	}

	/**
	 * build_rows() also matches the search term against the current translation, not just the key.
	 *
	 * @return void
	 */
	public function test_build_rows_search_matches_the_current_translation(): void {
		$keys    = array( 'Add to cart', 'Cart', 'Search' );
		$current = array(
			'Cart' => array(
				'singular' => 'Panier',
				'plural'   => null,
				'context'  => '',
			),
		);

		$result = TranslationCatalog::build_rows( $keys, $current, 'panier', 1, 50 );

		$this->assertSame( array( 'Cart' ), array_column( $result['rows'], 'key' ) );
	}

	/**
	 * build_rows() paginates its (filtered) result and clamps an out-of-range page to the last one.
	 *
	 * @return void
	 */
	public function test_build_rows_paginates_and_clamps_out_of_range_page(): void {
		$keys = array( 'One', 'Two', 'Three', 'Four', 'Five' );

		$page1 = TranslationCatalog::build_rows( $keys, array(), '', 1, 2 );
		$this->assertSame( array( 'One', 'Two' ), array_column( $page1['rows'], 'key' ) );
		$this->assertSame( 3, $page1['total_pages'] );

		$page3 = TranslationCatalog::build_rows( $keys, array(), '', 3, 2 );
		$this->assertSame( array( 'Five' ), array_column( $page3['rows'], 'key' ) );

		$clamped = TranslationCatalog::build_rows( $keys, array(), '', 99, 2 );
		$this->assertSame( 3, $clamped['page'] );
		$this->assertSame( array( 'Five' ), array_column( $clamped['rows'], 'key' ) );
	}
}
