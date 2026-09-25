<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for Solar_Template\I18n\GettextMoCompiler.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify that a catalog with a placeholder and a plural form compiles into a valid `.mo`
 *          file that round-trips correctly for both a French-like (n > 1) and an English-like
 *          (n != 1) plural rule. This is the automated counterpart of the manual Docker check
 *          performed when the translation system was first built (see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\I18n;

use Gettext\Loader\MoLoader;
use PHPUnit\Framework\TestCase;
use Solar_Template\I18n\GettextMoCompiler;

/**
 * @covers \Solar_Template\I18n\GettextMoCompiler
 */
final class GettextMoCompilerTest extends TestCase {

	/**
	 * Absolute path of the temporary `.mo` file written by each test, cleaned up afterwards.
	 *
	 * @var string
	 */
	private string $destination;

	/**
	 * Creates a fresh temporary destination path before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->destination = sys_get_temp_dir() . '/solar-template-test-' . uniqid() . '.mo';
	}

	/**
	 * Removes the temporary `.mo` file after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		if ( file_exists( $this->destination ) ) {
			unlink( $this->destination );
		}

		parent::tearDown();
	}

	/**
	 * A singular/plural entry with a `%d` placeholder compiles and round-trips correctly for a
	 * French-like two-form plural rule (`n > 1`).
	 *
	 * @return void
	 */
	public function test_compiles_a_plural_entry_with_french_plural_rule(): void {
		$compiler = new GettextMoCompiler();

		$success = $compiler->write(
			'fr_FR',
			array(
				array(
					'key'      => '%d item in the cart',
					'singular' => '%d article dans le panier',
					'plural'   => '%d articles dans le panier',
					'context'  => '',
				),
			),
			'n > 1',
			$this->destination
		);

		$this->assertTrue( $success );
		$this->assertFileExists( $this->destination );

		$translations = ( new MoLoader() )->loadFile( $this->destination );

		$this->assertSame( 'nplurals=2; plural=n > 1;', $translations->getHeaders()->get( 'Plural-Forms' ) );

		$translation = $translations->find( null, '%d item in the cart' );

		$this->assertNotNull( $translation );
		$this->assertSame( '%d article dans le panier', $translation->getTranslation() );
		$this->assertSame( array( '%d articles dans le panier' ), $translation->getPluralTranslations() );
	}

	/**
	 * The same entry compiles correctly for an English-like plural rule (`n != 1`), and an entry
	 * without a plural form only stores its singular translation.
	 *
	 * @return void
	 */
	public function test_compiles_a_singular_only_entry_with_english_plural_rule(): void {
		$compiler = new GettextMoCompiler();

		$compiler->write(
			'en_US',
			array(
				array(
					'key'      => 'Welcome',
					'singular' => 'Welcome',
					'plural'   => null,
					'context'  => '',
				),
			),
			'n != 1',
			$this->destination
		);

		$translations = ( new MoLoader() )->loadFile( $this->destination );

		$this->assertSame( 'nplurals=2; plural=n != 1;', $translations->getHeaders()->get( 'Plural-Forms' ) );

		$translation = $translations->find( null, 'Welcome' );

		$this->assertNotNull( $translation );
		$this->assertSame( 'Welcome', $translation->getTranslation() );
		$this->assertSame( array(), $translation->getPluralTranslations() );
	}
}
