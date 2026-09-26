<?php
/**
 * Created: 2026-09-26 14:20 CEST
 * Role: Unit test for Solar_Template\Admin\ProductsSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert sanitize()'s validation/fallback behaviour (verified end to end onto the catalog/
 *          product page in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\ProductsSettings;

final class ProductsSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_sanitizes_a_fully_valid_submission(): void {
		$result = ProductsSettings::sanitize(
			array(
				'columns'                 => '5',
				'products_per_page'       => '20',
				'filters_position'        => 'sidebar-left',
				'sidebar_enabled'         => '1',
				'sidebar_widgets'         => array( 'category', 'brand', 'not-a-real-widget' ),
				'related_enabled'         => '1',
				'related_count'           => '6',
				'engraving_enabled'       => '1',
				'engraving_label'         => ' Gravure ',
				'engraving_field_type'    => 'textarea',
				'engraving_default_price' => '30',
			)
		);

		$this->assertSame(
			array(
				'columns'                 => '5',
				'products_per_page'       => '20',
				'filters_position'        => 'sidebar-left',
				'sidebar_enabled'         => '1',
				'sidebar_widgets'         => 'category,brand',
				'related_enabled'         => '1',
				'related_count'           => '6',
				'engraving_enabled'       => '1',
				'engraving_label'         => 'Gravure',
				'engraving_field_type'    => 'textarea',
				'engraving_default_price' => '30',
			),
			$result
		);
	}

	/**
	 * @return void
	 */
	public function test_columns_are_clamped_to_the_valid_range(): void {
		$this->assertSame( '2', ProductsSettings::sanitize( array( 'columns' => '1' ) )['columns'] );
		$this->assertSame( '6', ProductsSettings::sanitize( array( 'columns' => '99' ) )['columns'] );
	}

	/**
	 * @return void
	 */
	public function test_unknown_filters_position_falls_back_to_top(): void {
		$this->assertSame( 'top', ProductsSettings::sanitize( array( 'filters_position' => 'not-a-real-position' ) )['filters_position'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_toggles_mean_disabled(): void {
		$result = ProductsSettings::sanitize( array() );

		$this->assertSame( '0', $result['sidebar_enabled'] );
		$this->assertSame( '0', $result['related_enabled'] );
		$this->assertSame( '0', $result['engraving_enabled'] );
	}

	/**
	 * @return void
	 */
	public function test_unknown_engraving_field_type_falls_back_to_text(): void {
		$this->assertSame( 'text', ProductsSettings::sanitize( array( 'engraving_field_type' => 'select' ) )['engraving_field_type'] );
	}

	/**
	 * @return void
	 */
	public function test_related_count_is_clamped(): void {
		$this->assertSame( '1', ProductsSettings::sanitize( array( 'related_count' => '0' ) )['related_count'] );
		$this->assertSame( '12', ProductsSettings::sanitize( array( 'related_count' => '99' ) )['related_count'] );
	}
}
