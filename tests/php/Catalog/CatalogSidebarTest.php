<?php
/**
 * Created: 2026-09-26 14:30 CEST
 * Role: Unit test for Solar_Template\Catalog\CatalogSidebar.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure price-bracket splitting logic behind the catalog sidebar's "Price"
 *          widget (verified end to end in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Catalog;

use PHPUnit\Framework\TestCase;
use Solar_Template\Catalog\CatalogSidebar;

final class CatalogSidebarTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_splits_a_range_into_the_requested_number_of_brackets(): void {
		$brackets = CatalogSidebar::price_brackets( 0.0, 100.0, 4 );

		$this->assertCount( 4, $brackets );
		$this->assertNull( $brackets[0]['min'] );
		$this->assertSame( 25.0, $brackets[0]['max'] );
		$this->assertSame( 25.0, $brackets[1]['min'] );
		$this->assertSame( 50.0, $brackets[1]['max'] );
		$this->assertSame( 50.0, $brackets[2]['min'] );
		$this->assertSame( 75.0, $brackets[2]['max'] );
		$this->assertSame( 75.0, $brackets[3]['min'] );
		$this->assertNull( $brackets[3]['max'] );
	}

	/**
	 * @return void
	 */
	public function test_returns_empty_when_max_does_not_exceed_min(): void {
		$this->assertSame( array(), CatalogSidebar::price_brackets( 50.0, 50.0 ) );
		$this->assertSame( array(), CatalogSidebar::price_brackets( 50.0, 10.0 ) );
	}

	/**
	 * @return void
	 */
	public function test_single_bracket_spans_the_whole_range(): void {
		$brackets = CatalogSidebar::price_brackets( 10.0, 90.0, 1 );

		$this->assertCount( 1, $brackets );
		$this->assertNull( $brackets[0]['min'] );
		$this->assertNull( $brackets[0]['max'] );
	}
}
