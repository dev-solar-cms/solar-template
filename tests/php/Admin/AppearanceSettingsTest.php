<?php
/**
 * Created: 2026-09-26 11:15 CEST
 * Role: Unit test for Solar_Template\Admin\AppearanceSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert sanitize()'s validation/fallback behaviour for colors, fonts and roundness — the
 *          real `:root` custom property output is exercised functionally in Docker.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\AppearanceSettings;

final class AppearanceSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_sanitizes_a_fully_valid_submission(): void {
		$result = AppearanceSettings::sanitize(
			array(
				'primary_color'    => '#101010',
				'accent_color'     => '#abc',
				'background_color' => '#ffffff',
				'font_body'        => 'Inter',
				'font_heading'     => 'Cormorant',
				'radius'           => '10',
			)
		);

		$this->assertSame(
			array(
				'primary_color'    => '#101010',
				'accent_color'     => '#abc',
				'background_color' => '#ffffff',
				'font_body'        => 'Inter',
				'font_heading'     => 'Cormorant',
				'radius'           => '10',
			),
			$result
		);
	}

	/**
	 * @return void
	 */
	public function test_invalid_color_falls_back_to_the_default(): void {
		$result = AppearanceSettings::sanitize( array( 'primary_color' => 'not-a-color' ) );

		$this->assertSame( '#0d0d0d', $result['primary_color'] );
	}

	/**
	 * @return void
	 */
	public function test_unknown_body_font_falls_back_to_the_default(): void {
		$result = AppearanceSettings::sanitize( array( 'font_body' => 'Comic Sans' ) );

		$this->assertSame( 'Plus Jakarta Sans', $result['font_body'] );
	}

	/**
	 * @return void
	 */
	public function test_unknown_heading_font_falls_back_to_inherit(): void {
		$result = AppearanceSettings::sanitize( array( 'font_heading' => 'Comic Sans' ) );

		$this->assertSame( '', $result['font_heading'] );
	}

	/**
	 * @return void
	 */
	public function test_empty_heading_font_means_inherit_the_body_font(): void {
		$result = AppearanceSettings::sanitize( array( 'font_heading' => '' ) );

		$this->assertSame( '', $result['font_heading'] );
	}

	/**
	 * @return void
	 */
	public function test_unknown_radius_falls_back_to_the_default(): void {
		$result = AppearanceSettings::sanitize( array( 'radius' => '42' ) );

		$this->assertSame( '5', $result['radius'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_fields_use_defaults(): void {
		$this->assertSame( AppearanceSettings::defaults(), AppearanceSettings::sanitize( array() ) );
	}
}
