<?php
/**
 * Created: 2026-09-26 11:25 CEST
 * Role: Unit test for Solar_Template\Admin\FooterSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert sanitize()'s validation/fallback behaviour and the pure column-count trimming
 *          logic behind the real `solar_template_footer_config` filter wiring (verified end to end
 *          in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\FooterSettings;

final class FooterSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_sanitizes_a_fully_valid_submission(): void {
		$result = FooterSettings::sanitize(
			array(
				'columns_count'      => '3',
				'newsletter_enabled' => '1',
				'payment_icons'      => array( 'visa', 'paypal', 'not-a-real-one' ),
				'copyright_text'     => ' © {year} Acme ',
			)
		);

		$this->assertSame(
			array(
				'columns_count'      => '3',
				'newsletter_enabled' => '1',
				'payment_icons'      => 'visa,paypal',
				'copyright_text'     => '© {year} Acme',
			),
			$result
		);
	}

	/**
	 * @return void
	 */
	public function test_unknown_column_count_falls_back_to_five(): void {
		$result = FooterSettings::sanitize( array( 'columns_count' => '9' ) );

		$this->assertSame( '5', $result['columns_count'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_newsletter_toggle_means_disabled(): void {
		$result = FooterSettings::sanitize( array() );

		$this->assertSame( '0', $result['newsletter_enabled'] );
	}

	/**
	 * @return void
	 */
	public function test_missing_payment_icons_means_none_selected(): void {
		$result = FooterSettings::sanitize( array() );

		$this->assertSame( '', $result['payment_icons'] );
	}

	/**
	 * @return void
	 */
	public function test_visible_columns_are_trimmed_to_available_slots_with_newsletter_enabled(): void {
		$columns = array( 'shop', 'information', 'legal' );

		$this->assertSame( array( 'shop', 'information', 'legal' ), FooterSettings::resolve_visible_columns( $columns, 5, true ) );
		$this->assertSame( array( 'shop', 'information' ), FooterSettings::resolve_visible_columns( $columns, 4, true ) );
		$this->assertSame( array( 'shop' ), FooterSettings::resolve_visible_columns( $columns, 3, true ) );
		$this->assertSame( array(), FooterSettings::resolve_visible_columns( $columns, 2, true ) );
	}

	/**
	 * Without the newsletter column, one more slot is available to Shop/Information/Legal for the
	 * same total count.
	 *
	 * @return void
	 */
	public function test_visible_columns_gain_a_slot_when_newsletter_is_disabled(): void {
		$columns = array( 'shop', 'information', 'legal' );

		$this->assertSame( array( 'shop', 'information', 'legal' ), FooterSettings::resolve_visible_columns( $columns, 4, false ) );
		$this->assertSame( array( 'shop' ), FooterSettings::resolve_visible_columns( $columns, 2, false ) );
	}
}
