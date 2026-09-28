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

use Gettext\Loader\PoLoader;
use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\TranslationsSettings;
use Solar_Template\I18n\GettextMoCompiler;

final class TranslationsSettingsTest extends TestCase {

	/**
	 * Absolute paths of every temporary fixture file written by a test, cleaned up afterwards.
	 *
	 * @var array<int, string>
	 */
	private array $temp_files = array();

	/**
	 * Removes every temporary fixture file created by the test that just ran.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach ( $this->temp_files as $path ) {
			if ( file_exists( $path ) ) {
				unlink( $path );
			}
		}

		$this->temp_files = array();

		parent::tearDown();
	}

	/**
	 * Writes $contents to a fresh temporary file, tracked for cleanup in tearDown().
	 *
	 * @param string $contents Raw file content to write.
	 * @return string Absolute path of the written file.
	 */
	private function write_temp_file( string $contents ): string {
		$path = sys_get_temp_dir() . '/solar-template-test-' . uniqid() . '.tmp';
		file_put_contents( $path, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- test fixture written to the OS temp dir, no WP_Filesystem context in a plain PHPUnit run.
		$this->temp_files[] = $path;

		return $path;
	}

	/**
	 * Compiles a real, valid `.mo` file with GettextMoCompiler (the same production compiler the
	 * theme's own "Translate strings" editor uses), tracked for cleanup in tearDown().
	 *
	 * @return string Absolute path of the compiled file.
	 */
	private function write_real_mo_file(): string {
		$path               = sys_get_temp_dir() . '/solar-template-test-' . uniqid() . '.mo';
		$this->temp_files[] = $path;

		( new GettextMoCompiler() )->write(
			'fr_FR',
			array(
				array(
					'key'      => 'Welcome',
					'singular' => 'Bienvenue',
					'plural'   => null,
					'context'  => '',
				),
			),
			'n > 1',
			$path
		);

		return $path;
	}

	/**
	 * @return void
	 */
	public function test_content_check_accepts_a_real_po_file(): void {
		$path = $this->write_temp_file( "msgid \"Welcome\"\nmsgstr \"Bienvenue\"\n" );

		$this->assertTrue( TranslationsSettings::file_passes_real_content_check( $path, 'po' ) );
	}

	/**
	 * A translation payload that looks like markup is not special-cased by the content check — it
	 * only validates real `.po` structure, never the translated text itself. The gettext parser
	 * stores it back as plain, literal text (never evaluated), which stays inert once displayed
	 * through the theme's own consistently-escaped output (see DECISIONS.md §14).
	 *
	 * @return void
	 */
	public function test_content_check_accepts_a_po_file_with_a_script_payload_and_stores_it_as_inert_text(): void {
		$path = $this->write_temp_file( "msgid \"Welcome\"\nmsgstr \"<script>alert(1)</script>\"\n" );

		$this->assertTrue( TranslationsSettings::file_passes_real_content_check( $path, 'po' ) );

		$translations = ( new PoLoader() )->loadFile( $path );
		$translation  = $translations->find( null, 'Welcome' );

		$this->assertNotNull( $translation );
		$this->assertSame( '<script>alert(1)</script>', $translation->getTranslation() );
	}

	/**
	 * @return void
	 */
	public function test_content_check_rejects_a_file_with_no_msgid_renamed_to_po(): void {
		$path = $this->write_temp_file( "This is just a plain text file, not a real catalog.\n" );

		$this->assertFalse( TranslationsSettings::file_passes_real_content_check( $path, 'po' ) );
	}

	/**
	 * @return void
	 */
	public function test_content_check_rejects_binary_content_renamed_to_po(): void {
		$path = $this->write_temp_file( "\xff\xfe\x00\x01msgid binary garbage, not real UTF-8 text" );

		$this->assertFalse( TranslationsSettings::file_passes_real_content_check( $path, 'po' ) );
	}

	/**
	 * @return void
	 */
	public function test_content_check_accepts_a_real_mo_file(): void {
		$path = $this->write_real_mo_file();

		$this->assertTrue( TranslationsSettings::file_passes_real_content_check( $path, 'mo' ) );
	}

	/**
	 * @return void
	 */
	public function test_content_check_rejects_a_file_with_no_real_magic_number_renamed_to_mo(): void {
		$path = $this->write_temp_file( 'Not a real compiled gettext catalog.' );

		$this->assertFalse( TranslationsSettings::file_passes_real_content_check( $path, 'mo' ) );
	}

	/**
	 * @return void
	 */
	public function test_content_check_rejects_a_missing_file(): void {
		$this->assertFalse(
			TranslationsSettings::file_passes_real_content_check( sys_get_temp_dir() . '/solar-template-test-does-not-exist.po', 'po' )
		);
	}

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
