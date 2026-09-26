<?php
/**
 * Created: 2026-09-26 12:15 CEST
 * Role: Unit test for Solar_Template\Admin\HomeSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure section-order resolution logic behind the real drag & drop wiring onto
 *          front-page.php (verified end to end in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\HomeSettings;

final class HomeSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_full_valid_order_is_kept_unchanged(): void {
		$known = array( 'hero', 'featured-products', 'categories' );

		$this->assertSame(
			array( 'categories', 'hero', 'featured-products' ),
			HomeSettings::resolve_order( $known, array( 'categories', 'hero', 'featured-products' ) )
		);
	}

	/**
	 * @return void
	 */
	public function test_missing_slug_is_appended_at_the_end(): void {
		$known = array( 'hero', 'featured-products', 'categories' );

		$this->assertSame(
			array( 'featured-products', 'hero', 'categories' ),
			HomeSettings::resolve_order( $known, array( 'featured-products', 'hero' ) )
		);
	}

	/**
	 * @return void
	 */
	public function test_unknown_slug_is_dropped(): void {
		$known = array( 'hero', 'featured-products' );

		$this->assertSame(
			array( 'hero', 'featured-products' ),
			HomeSettings::resolve_order( $known, array( 'hero', 'not-a-real-section', 'featured-products' ) )
		);
	}

	/**
	 * @return void
	 */
	public function test_duplicate_slugs_are_deduplicated(): void {
		$known = array( 'hero', 'featured-products' );

		$this->assertSame(
			array( 'hero', 'featured-products' ),
			HomeSettings::resolve_order( $known, array( 'hero', 'hero', 'featured-products' ) )
		);
	}

	/**
	 * @return void
	 */
	public function test_empty_requested_order_falls_back_to_the_known_order(): void {
		$known = array( 'hero', 'featured-products', 'categories' );

		$this->assertSame( $known, HomeSettings::resolve_order( $known, array() ) );
	}
}
