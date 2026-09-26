<?php
/**
 * Created: 2026-09-26 11:10 CEST
 * Role: Unit test for Solar_Template\Admin\GeneralSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert sanitize()'s validation/fallback behaviour for every field — real persistence and
 *          the maintenance mode gate itself are exercised functionally in Docker (see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\GeneralSettings;

final class GeneralSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_sanitizes_a_fully_valid_submission(): void {
		$result = GeneralSettings::sanitize(
			array(
				'shop_name'         => ' My Shop ',
				'logo_id'           => '12',
				'favicon_id'        => '7',
				'currency'          => 'usd',
				'currency_position' => 'after',
				'maintenance_mode'  => '1',
			)
		);

		$this->assertSame(
			array(
				'shop_name'         => 'My Shop',
				'logo_id'           => '12',
				'favicon_id'        => '7',
				'currency'          => 'USD',
				'currency_position' => 'after',
				'maintenance_mode'  => '1',
			),
			$result
		);
	}

	/**
	 * @return void
	 */
	public function test_unknown_currency_falls_back_to_empty(): void {
		$result = GeneralSettings::sanitize( array( 'currency' => 'XYZ' ) );

		$this->assertSame( '', $result['currency'] );
	}

	/**
	 * @return void
	 */
	public function test_unknown_currency_position_falls_back_to_empty(): void {
		$result = GeneralSettings::sanitize( array( 'currency_position' => 'middle' ) );

		$this->assertSame( '', $result['currency_position'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_maintenance_mode_checkbox_means_disabled(): void {
		$result = GeneralSettings::sanitize( array() );

		$this->assertSame( '0', $result['maintenance_mode'] );
	}

	/**
	 * @return void
	 */
	public function test_negative_attachment_ids_become_non_negative(): void {
		$result = GeneralSettings::sanitize( array( 'logo_id' => '-5' ) );

		$this->assertSame( '5', $result['logo_id'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_fields_default_to_empty_or_zero(): void {
		$result = GeneralSettings::sanitize( array() );

		$this->assertSame(
			array(
				'shop_name'         => '',
				'logo_id'           => '0',
				'favicon_id'        => '0',
				'currency'          => '',
				'currency_position' => '',
				'maintenance_mode'  => '0',
			),
			$result
		);
	}
}
