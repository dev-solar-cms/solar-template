<?php
/**
 * Created: 2026-09-26 11:20 CEST
 * Role: Unit test for Solar_Template\Admin\HeaderSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert sanitize()'s validation/fallback behaviour — the real header layout/mega
 *          menu/social links wiring is exercised functionally in Docker (see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\HeaderSettings;

final class HeaderSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_sanitizes_a_fully_valid_submission(): void {
		$result = HeaderSettings::sanitize(
			array(
				'layout'            => 'left-center',
				'mega_menu_enabled' => '1',
				'promo_bar_enabled' => '1',
				'promo_bar_text'    => ' Free shipping ',
				'social_instagram'  => 'https://instagram.com/solar',
				'social_facebook'   => 'https://facebook.com/solar',
				'social_pinterest'  => 'https://pinterest.com/solar',
				'social_x'          => 'https://x.com/solar',
				'transparent_home'  => '1',
			)
		);

		$this->assertSame(
			array(
				'layout'            => 'left-center',
				'mega_menu_enabled' => '1',
				'promo_bar_enabled' => '1',
				'promo_bar_text'    => 'Free shipping',
				'transparent_home'  => '1',
				'social_instagram'  => 'https://instagram.com/solar',
				'social_facebook'   => 'https://facebook.com/solar',
				'social_pinterest'  => 'https://pinterest.com/solar',
				'social_x'          => 'https://x.com/solar',
			),
			$result
		);
	}

	/**
	 * @return void
	 */
	public function test_unknown_layout_falls_back_to_centered(): void {
		$result = HeaderSettings::sanitize( array( 'layout' => 'not-a-layout' ) );

		$this->assertSame( 'centered', $result['layout'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_toggles_mean_disabled(): void {
		$result = HeaderSettings::sanitize( array() );

		$this->assertSame( '0', $result['mega_menu_enabled'] );
		$this->assertSame( '0', $result['promo_bar_enabled'] );
		$this->assertSame( '0', $result['transparent_home'] );
	}

	/**
	 * @return void
	 */
	public function test_invalid_social_url_is_dropped(): void {
		$result = HeaderSettings::sanitize( array( 'social_instagram' => 'javascript:alert(1)' ) );

		$this->assertSame( '', $result['social_instagram'] );
	}
}
