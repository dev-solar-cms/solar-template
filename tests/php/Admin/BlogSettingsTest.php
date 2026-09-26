<?php
/**
 * Created: 2026-09-26 14:25 CEST
 * Role: Unit test for Solar_Template\Admin\BlogSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert sanitize()'s validation/fallback behaviour (verified end to end onto the blog in
 *          Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\BlogSettings;

final class BlogSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_sanitizes_a_fully_valid_submission(): void {
		$result = BlogSettings::sanitize(
			array(
				'posts_per_page'   => '12',
				'featured_enabled' => '1',
				'columns'          => '2',
				'share_networks'   => array( 'facebook', 'pinterest', 'not-a-real-network' ),
				'related_count'    => '5',
			)
		);

		$this->assertSame(
			array(
				'posts_per_page'   => '12',
				'featured_enabled' => '1',
				'columns'          => '2',
				'share_networks'   => 'facebook,pinterest',
				'related_count'    => '5',
			),
			$result
		);
	}

	/**
	 * @return void
	 */
	public function test_columns_are_clamped_to_the_valid_range(): void {
		$this->assertSame( '2', BlogSettings::sanitize( array( 'columns' => '1' ) )['columns'] );
		$this->assertSame( '4', BlogSettings::sanitize( array( 'columns' => '9' ) )['columns'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_featured_toggle_means_disabled(): void {
		$this->assertSame( '0', BlogSettings::sanitize( array() )['featured_enabled'] );
	}

	/**
	 * @return void
	 */
	public function test_related_count_of_zero_is_kept_to_disable_the_section(): void {
		$this->assertSame( '0', BlogSettings::sanitize( array( 'related_count' => '0' ) )['related_count'] );
	}

	/**
	 * @return void
	 */
	public function test_related_count_is_clamped_to_six(): void {
		$this->assertSame( '6', BlogSettings::sanitize( array( 'related_count' => '99' ) )['related_count'] );
	}
}
