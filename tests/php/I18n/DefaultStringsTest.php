<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for Solar_Template\I18n\DefaultStrings.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify that seeding the catalog registers every entry for both fr_FR and en_US, and
 *          that the seed data itself stays internally consistent (no empty translation, every
 *          placeholder used in the English source also present in its French translation).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\I18n;

use PHPUnit\Framework\TestCase;
use Solar_Template\I18n\DatabaseTranslator;
use Solar_Template\I18n\DefaultStrings;
use Solar_Template\I18n\GettextMoCompiler;

/**
 * @covers \Solar_Template\I18n\DefaultStrings
 */
final class DefaultStringsTest extends TestCase {

	/**
	 * Directory the test writes temporary `.mo` files to, cleaned up afterwards.
	 *
	 * @var string
	 */
	private string $languages_dir;

	/**
	 * Creates a fresh temporary languages directory before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->languages_dir = sys_get_temp_dir() . '/solar-template-test-' . uniqid();
		mkdir( $this->languages_dir );
	}

	/**
	 * Removes the temporary languages directory after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		array_map( 'unlink', glob( "{$this->languages_dir}/*" ) );
		rmdir( $this->languages_dir );

		parent::tearDown();
	}

	/**
	 * seed() registers every entry from all() for both fr_FR and en_US.
	 *
	 * @return void
	 */
	public function test_seed_registers_every_entry_for_both_locales(): void {
		$translator = new DatabaseTranslator( new FakeWpdb(), new GettextMoCompiler(), $this->languages_dir );

		DefaultStrings::seed( $translator );

		$keys            = array_keys( DefaultStrings::all() );
		$french_strings  = $translator->get_strings( 'fr_FR' );
		$english_strings = $translator->get_strings( 'en_US' );

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $french_strings, "Missing fr_FR translation for \"{$key}\"." );
			$this->assertArrayHasKey( $key, $english_strings, "Missing en_US translation for \"{$key}\"." );
			$this->assertNotSame( '', $french_strings[ $key ]['singular'] );
			$this->assertNotSame( '', $english_strings[ $key ]['singular'] );
		}
	}

	/**
	 * Every printf-style placeholder in an English source key also appears in its French
	 * translation (order may differ, e.g. `%1$s`/`%2$s`, but the set of placeholders must match).
	 *
	 * @return void
	 */
	public function test_every_placeholder_in_the_source_key_is_present_in_the_french_translation(): void {
		foreach ( DefaultStrings::all() as $key => $translations ) {
			preg_match_all( '/%(?:\d+\$)?[sd]/', $key, $expected_placeholders );
			preg_match_all( '/%(?:\d+\$)?[sd]/', $translations['fr_FR'], $found_placeholders );

			sort( $expected_placeholders[0] );
			sort( $found_placeholders[0] );

			$this->assertSame(
				$expected_placeholders[0],
				$found_placeholders[0],
				"Placeholder mismatch between the source key and its fr_FR translation for \"{$key}\"."
			);
		}
	}
}
