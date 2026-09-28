<?php
/**
 * Created: 2026-09-28 16:10 CEST
 * Role: Unit test for Solar_Template\Admin\SettingsRepository.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The only one of the theme's 5 `$wpdb`-backed repository-style classes with no dedicated
 *          test before this — added alongside the shared Solar_Template\Support\WpdbTableTrait
 *          migration, which touched all 5. Covers get()/set()/get_many()/set_many() against an
 *          in-memory `$wpdb` double, and the pure sanitize_checkbox() helper every settings tab now
 *          shares instead of repeating its own `! empty( $raw['x'] ) ? '1' : '0'` idiom.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\SettingsRepository;

final class SettingsRepositoryTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['wpdb'] = new FakeWpdb(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- test-only stand-in for the real `global $wpdb`.
	}

	/**
	 * @return void
	 */
	public function test_get_returns_the_default_when_never_saved(): void {
		$this->assertSame( 'fallback', SettingsRepository::get( 'general.shop_name', 'fallback' ) );
	}

	/**
	 * @return void
	 */
	public function test_set_then_get_returns_the_stored_value(): void {
		SettingsRepository::set( 'general.shop_name', 'Solar Boutique' );

		$this->assertSame( 'Solar Boutique', SettingsRepository::get( 'general.shop_name' ) );
	}

	/**
	 * @return void
	 */
	public function test_set_overwrites_a_previously_stored_value(): void {
		SettingsRepository::set( 'general.shop_name', 'First' );
		SettingsRepository::set( 'general.shop_name', 'Second' );

		$this->assertSame( 'Second', SettingsRepository::get( 'general.shop_name' ) );
	}

	/**
	 * @return void
	 */
	public function test_get_many_mixes_stored_and_default_values(): void {
		SettingsRepository::set( 'general.shop_name', 'Solar Boutique' );

		$values = SettingsRepository::get_many(
			array(
				'general.shop_name' => '',
				'general.currency'  => 'EUR',
			)
		);

		$this->assertSame(
			array(
				'general.shop_name' => 'Solar Boutique',
				'general.currency'  => 'EUR',
			),
			$values
		);
	}

	/**
	 * @return void
	 */
	public function test_set_many_prefixes_every_key_with_the_given_tab_slug(): void {
		SettingsRepository::set_many(
			'general',
			array(
				'shop_name' => 'Solar Boutique',
				'currency'  => 'EUR',
			)
		);

		$this->assertSame( 'Solar Boutique', SettingsRepository::get( 'general.shop_name' ) );
		$this->assertSame( 'EUR', SettingsRepository::get( 'general.currency' ) );
	}

	/**
	 * @return void
	 */
	public function test_sanitize_checkbox_returns_1_when_checked(): void {
		$this->assertSame( '1', SettingsRepository::sanitize_checkbox( array( 'auto_detect' => '1' ), 'auto_detect' ) );
		$this->assertSame( '1', SettingsRepository::sanitize_checkbox( array( 'auto_detect' => 'on' ), 'auto_detect' ) );
	}

	/**
	 * @return void
	 */
	public function test_sanitize_checkbox_returns_0_when_absent_or_falsy(): void {
		$this->assertSame( '0', SettingsRepository::sanitize_checkbox( array(), 'auto_detect' ) );
		$this->assertSame( '0', SettingsRepository::sanitize_checkbox( array( 'auto_detect' => '' ), 'auto_detect' ) );
		$this->assertSame( '0', SettingsRepository::sanitize_checkbox( array( 'auto_detect' => '0' ), 'auto_detect' ) );
	}
}
