<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for Solar_Template\I18n\DatabaseTranslator.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify language/catalog CRUD and compilation against an in-memory FakeWpdb, so the
 *          class's logic is covered without a real WordPress/MySQL install.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\I18n;

use PHPUnit\Framework\TestCase;
use Solar_Template\I18n\DatabaseTranslator;
use Solar_Template\I18n\GettextMoCompiler;

/**
 * @covers \Solar_Template\I18n\DatabaseTranslator
 */
final class DatabaseTranslatorTest extends TestCase {

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
	 * Builds a translator wired to a fresh FakeWpdb.
	 *
	 * @return DatabaseTranslator
	 */
	private function make_translator(): DatabaseTranslator {
		return new DatabaseTranslator( new FakeWpdb(), new GettextMoCompiler(), $this->languages_dir );
	}

	/**
	 * A language added with add_language() is returned by get_languages().
	 *
	 * @return void
	 */
	public function test_add_language_then_get_languages(): void {
		$translator = $this->make_translator();

		$translator->add_language( 'fr_FR', 'Français', '🇫🇷', true );
		$translator->add_language( 'en_US', 'English (US)', '🇺🇸' );

		$languages = $translator->get_languages();

		$this->assertSame( array( 'fr_FR', 'en_US' ), array_keys( $languages ) );
		$this->assertTrue( $languages['fr_FR']['is_default'] );
		$this->assertFalse( $languages['en_US']['is_default'] );
	}

	/**
	 * Adding a new default language clears the previous default.
	 *
	 * @return void
	 */
	public function test_only_one_language_stays_default(): void {
		$translator = $this->make_translator();

		$translator->add_language( 'fr_FR', 'Français', '🇫🇷', true );
		$translator->add_language( 'en_US', 'English (US)', '🇺🇸', true );

		$languages = $translator->get_languages();

		$this->assertFalse( $languages['fr_FR']['is_default'] );
		$this->assertTrue( $languages['en_US']['is_default'] );
	}

	/**
	 * A string registered with set_string() is returned by get_strings() for that locale only.
	 *
	 * @return void
	 */
	public function test_set_string_then_get_strings_is_scoped_per_locale(): void {
		$translator = $this->make_translator();

		$translator->set_string( 'fr_FR', '%d item in the cart', '%d article dans le panier', '%d articles dans le panier' );
		$translator->set_string( 'en_US', '%d item in the cart', '%d item in the cart', '%d items in the cart' );

		$french = $translator->get_strings( 'fr_FR' );

		$this->assertSame( '%d article dans le panier', $french['%d item in the cart']['singular'] );
		$this->assertSame( '%d articles dans le panier', $french['%d item in the cart']['plural'] );
		$this->assertCount( 1, $translator->get_strings( 'en_US' ) );
	}

	/**
	 * remove_language() removes the language and its translations.
	 *
	 * @return void
	 */
	public function test_remove_language_also_removes_its_translations(): void {
		$translator = $this->make_translator();

		$translator->add_language( 'fr_FR', 'Français', '🇫🇷' );
		$translator->set_string( 'fr_FR', 'Welcome', 'Bienvenue' );

		$translator->remove_language( 'fr_FR' );

		$this->assertArrayNotHasKey( 'fr_FR', $translator->get_languages() );
		$this->assertSame( array(), $translator->get_strings( 'fr_FR' ) );
	}

	/**
	 * compile() writes a `.mo` file named after the bare locale (WordPress' just-in-time loading
	 * convention for a theme's own languages directory — see the docblock on compile() itself).
	 *
	 * @return void
	 */
	public function test_compile_writes_a_bare_locale_named_mo_file(): void {
		$translator = $this->make_translator();

		$translator->set_string( 'fr_FR', 'Welcome', 'Bienvenue' );

		$this->assertTrue( $translator->compile( 'fr_FR' ) );
		$this->assertFileExists( "{$this->languages_dir}/fr_FR.mo" );
	}

	/**
	 * compile_all() only compiles active languages, and reports which ones it compiled.
	 *
	 * @return void
	 */
	public function test_compile_all_only_compiles_active_languages(): void {
		$translator = $this->make_translator();

		$translator->add_language( 'fr_FR', 'Français', '🇫🇷' );

		$compiled = $translator->compile_all();

		$this->assertSame( array( 'fr_FR' ), $compiled );
	}
}
