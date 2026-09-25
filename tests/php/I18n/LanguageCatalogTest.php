<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for Solar_Template\I18n\LanguageCatalog.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Guard the two languages the theme ships pre-seeded (fr_FR, en_US) and the fallback
 *          behaviour of plural_rule_for() for a language not in the static catalog.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\I18n;

use PHPUnit\Framework\TestCase;
use Solar_Template\I18n\LanguageCatalog;

/**
 * @covers \Solar_Template\I18n\LanguageCatalog
 */
final class LanguageCatalogTest extends TestCase {

	/**
	 * Only fr_FR and en_US are pre-seeded, with fr_FR as the default.
	 *
	 * @return void
	 */
	public function test_defaults_only_contains_french_and_english(): void {
		$defaults = LanguageCatalog::defaults();

		$this->assertSame( array( 'fr_FR', 'en_US' ), array_keys( $defaults ) );
		$this->assertTrue( $defaults['fr_FR']['is_default'] );
		$this->assertFalse( $defaults['en_US']['is_default'] );
	}

	/**
	 * plural_rule_for() falls back to the English-like rule for an unknown language code.
	 *
	 * @return void
	 */
	public function test_plural_rule_for_unknown_language_falls_back_to_english_like_rule(): void {
		$this->assertSame( 'n != 1', LanguageCatalog::plural_rule_for( 'xx_XX' ) );
		$this->assertSame( 'n > 1', LanguageCatalog::plural_rule_for( 'fr_FR' ) );
	}
}
