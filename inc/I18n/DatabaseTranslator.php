<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Default implementation of Solar_Template\Contracts\TranslatorInterface.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Manage the theme's languages and translation catalog stored in
 *          `wp_solar_template_languages`/`wp_solar_template_translations`, and delegate `.mo`
 *          compilation to a Solar_Template\Contracts\MoCompilerInterface. The database is
 *          accessed through a plain `$wpdb`-shaped object injected in the constructor, so this
 *          class can be unit tested with a lightweight fake instead of a full WordPress install.
 *
 * @package Solar_Template
 */

namespace Solar_Template\I18n;

use Solar_Template\Contracts\MoCompilerInterface;
use Solar_Template\Contracts\TranslatorInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes the theme's translation catalog in the database, and compiles it to `.mo`.
 */
final class DatabaseTranslator implements TranslatorInterface {

	/**
	 * Database access object (the WordPress global `$wpdb` in production).
	 *
	 * @var object
	 */
	private object $wpdb;

	/**
	 * Compiler used to turn a catalog into a `.mo` file.
	 *
	 * @var MoCompilerInterface
	 */
	private MoCompilerInterface $mo_compiler;

	/**
	 * Absolute path of the directory `.mo` files are written to (the theme's `languages/`).
	 *
	 * @var string
	 */
	private string $languages_dir;

	/**
	 * @param object              $wpdb          Database access object exposing `prefix`,
	 *                                           `get_results()`, `get_row()`, `query()`,
	 *                                           `prepare()`, `insert()`, `replace()`, `delete()`.
	 * @param MoCompilerInterface $mo_compiler   Compiler used by compile()/compile_all().
	 * @param string              $languages_dir Absolute path of the theme's `languages/` directory.
	 */
	public function __construct( object $wpdb, MoCompilerInterface $mo_compiler, string $languages_dir ) {
		$this->wpdb          = $wpdb;
		$this->mo_compiler   = $mo_compiler;
		$this->languages_dir = rtrim( $languages_dir, '/\\' );
	}

	/**
	 * Name of the languages table, including the WordPress table prefix.
	 *
	 * @return string
	 */
	private function languages_table(): string {
		return $this->wpdb->prefix . 'solar_template_languages';
	}

	/**
	 * Name of the translations table, including the WordPress table prefix.
	 *
	 * @return string
	 */
	private function translations_table(): string {
		return $this->wpdb->prefix . 'solar_template_translations';
	}

	/**
	 * Lists every language currently registered (active or not).
	 *
	 * @return array<string, array{label: string, flag: string, is_active: bool, is_default: bool}>
	 */
	public function get_languages(): array {
		$table = $this->languages_table();
		$rows  = $this->wpdb->get_results( "SELECT code, label, flag, is_active, is_default FROM {$table} ORDER BY is_default DESC, label ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$languages = array();

		foreach ( (array) $rows as $row ) {
			$languages[ $row['code'] ] = array(
				'label'      => $row['label'],
				'flag'       => $row['flag'],
				'is_active'  => ! empty( $row['is_active'] ),
				'is_default' => ! empty( $row['is_default'] ),
			);
		}

		return $languages;
	}

	/**
	 * Registers a new language.
	 *
	 * @param string $code       Locale code, e.g. `fr_FR`.
	 * @param string $label      Human-readable label, e.g. `Français`.
	 * @param string $flag       Flag associated with the language (emoji or short code).
	 * @param bool   $is_default Whether this language becomes the theme's default.
	 * @return bool True on success.
	 */
	public function add_language( string $code, string $label, string $flag, bool $is_default = false ): bool {
		if ( $is_default ) {
			$this->wpdb->query( "UPDATE {$this->languages_table()} SET is_default = 0" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$result = $this->wpdb->replace(
			$this->languages_table(),
			array(
				'code'       => $code,
				'label'      => $label,
				'flag'       => $flag,
				'is_active'  => 1,
				'is_default' => $is_default ? 1 : 0,
			),
			array( '%s', '%s', '%s', '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Removes a language and every translation registered for it.
	 *
	 * @param string $code Locale code to remove.
	 * @return bool True on success.
	 */
	public function remove_language( string $code ): bool {
		$this->wpdb->delete( $this->translations_table(), array( 'language_code' => $code ), array( '%s' ) );

		return false !== $this->wpdb->delete( $this->languages_table(), array( 'code' => $code ), array( '%s' ) );
	}

	/**
	 * Returns every translatable string registered, with its translation for the given locale.
	 *
	 * @param string $locale Locale code, e.g. `fr_FR`.
	 * @return array<string, array{singular: string, plural: ?string, context: string}>
	 */
	public function get_strings( string $locale ): array {
		$table = $this->translations_table();
		$sql   = $this->wpdb->prepare( "SELECT string_key, singular, plural, context FROM {$table} WHERE language_code = %s", $locale ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows  = $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$strings = array();

		foreach ( (array) $rows as $row ) {
			$strings[ $row['string_key'] ] = array(
				'singular' => $row['singular'],
				'plural'   => ( '' === (string) $row['plural'] ) ? null : $row['plural'],
				'context'  => (string) $row['context'],
			);
		}

		return $strings;
	}

	/**
	 * Registers or updates the translation of a string for a given locale.
	 *
	 * @param string      $locale   Locale code, e.g. `fr_FR`.
	 * @param string      $key      String key (the original/English text used as `msgid`).
	 * @param string      $singular Singular translation.
	 * @param string|null $plural   Plural translation, when the string uses `_n()`.
	 * @param string      $context  Optional gettext context (`_x()`/`_nx()`).
	 * @return bool True on success.
	 */
	public function set_string( string $locale, string $key, string $singular, ?string $plural = null, string $context = '' ): bool {
		$result = $this->wpdb->replace(
			$this->translations_table(),
			array(
				'language_code' => $locale,
				'string_key'    => $key,
				'context'       => $context,
				'singular'      => $singular,
				'plural'        => $plural ?? '',
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Compiles the catalog of a single locale into a `.mo` file under `languages/`.
	 *
	 * Since WordPress 6.7, `load_theme_textdomain()` hands loading off to a just-in-time
	 * mechanism (`_load_textdomain_just_in_time()`) which expects a theme's own `languages/`
	 * directory to contain bare `{locale}.mo` files (no domain prefix) — the `{domain}-{locale}.mo`
	 * naming is only used for the *global* `wp-content/languages/themes/` directory. Since this
	 * theme always loads its textdomain from its own `languages/` directory, the bare name is
	 * used here.
	 *
	 * @param string $locale Locale code to compile.
	 * @return bool True on success.
	 */
	public function compile( string $locale ): bool {
		$entries = array();

		foreach ( $this->get_strings( $locale ) as $key => $translation ) {
			$entries[] = array(
				'key'      => $key,
				'singular' => $translation['singular'],
				'plural'   => $translation['plural'],
				'context'  => $translation['context'],
			);
		}

		$destination = "{$this->languages_dir}/{$locale}.mo";

		return $this->mo_compiler->write( $locale, $entries, LanguageCatalog::plural_rule_for( $locale ), $destination );
	}

	/**
	 * Compiles the catalog of every active language.
	 *
	 * @return array<int, string> The list of locale codes successfully compiled.
	 */
	public function compile_all(): array {
		$compiled = array();

		foreach ( $this->get_languages() as $code => $language ) {
			if ( $language['is_active'] && $this->compile( $code ) ) {
				$compiled[] = $code;
			}
		}

		return $compiled;
	}
}
