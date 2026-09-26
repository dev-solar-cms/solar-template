<?php
/**
 * Created: 2026-09-26 14:40 CEST
 * Role: Unit test for Solar_Template\Admin\TranslationsSettings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure locale-resolution logic behind the real `determine_locale` wiring
 *          (verified end to end in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\TranslationsSettings;

final class TranslationsSettingsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_exact_match_wins_over_primary_subtag(): void {
		$this->assertSame(
			'fr_FR',
			TranslationsSettings::match_accept_language( array( 'fr_FR', 'en_US' ), 'fr-FR,fr;q=0.9,en;q=0.8' )
		);
	}

	/**
	 * @return void
	 */
	public function test_falls_back_to_primary_subtag_match(): void {
		$this->assertSame(
			'en_US',
			TranslationsSettings::match_accept_language( array( 'fr_FR', 'en_US' ), 'en-GB,en;q=0.9' )
		);
	}

	/**
	 * @return void
	 */
	public function test_returns_null_when_nothing_matches(): void {
		$this->assertNull( TranslationsSettings::match_accept_language( array( 'fr_FR', 'en_US' ), 'de-DE,de;q=0.9' ) );
	}

	/**
	 * @return void
	 */
	public function test_respects_quality_order(): void {
		$this->assertSame(
			'en_US',
			TranslationsSettings::match_accept_language( array( 'fr_FR', 'en_US' ), 'de-DE;q=0.9,en-US;q=0.8' )
		);
	}

	/**
	 * @return void
	 */
	public function test_resolve_locale_prefers_auto_detection_over_the_configured_default(): void {
		$this->assertSame(
			'en_US',
			TranslationsSettings::resolve_locale( array( 'fr_FR', 'en_US' ), 'fr_FR', true, 'en-US', 'de_DE' )
		);
	}

	/**
	 * @return void
	 */
	public function test_resolve_locale_falls_back_to_the_configured_default(): void {
		$this->assertSame(
			'fr_FR',
			TranslationsSettings::resolve_locale( array( 'fr_FR', 'en_US' ), 'fr_FR', true, 'de-DE', 'de_DE' )
		);
	}

	/**
	 * @return void
	 */
	public function test_resolve_locale_falls_back_to_the_site_locale_when_nothing_else_matches(): void {
		$this->assertSame(
			'de_DE',
			TranslationsSettings::resolve_locale( array( 'fr_FR', 'en_US' ), '', false, '', 'de_DE' )
		);
	}

	/**
	 * @return void
	 */
	public function test_resolve_locale_ignores_auto_detection_when_disabled(): void {
		$this->assertSame(
			'fr_FR',
			TranslationsSettings::resolve_locale( array( 'fr_FR', 'en_US' ), 'fr_FR', false, 'en-US', 'de_DE' )
		);
	}
}
