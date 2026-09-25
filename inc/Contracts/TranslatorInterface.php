<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Part of the theme's dependency-inversion layer (Solar_Template\Contracts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define the contract for the theme's translation catalog (languages + strings stored
 *          in `wp_solar_template_languages`/`wp_solar_template_translations`). The theme's
 *          templates keep calling WordPress core `__()`/`_e()`/`_n()` directly: this interface
 *          only manages the editable catalog behind those calls and the compilation of the
 *          resulting `.mo` files, so the real rendering always goes through WordPress' own
 *          gettext runtime.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the theme's languages and translation catalog, and compiles them to `.mo` files.
 */
interface TranslatorInterface {

	/**
	 * Lists every language currently registered (active or not).
	 *
	 * @return array<string, array{label: string, flag: string, is_active: bool, is_default: bool}>
	 *         Keyed by locale code (e.g. `fr_FR`).
	 */
	public function get_languages(): array;

	/**
	 * Registers a new language.
	 *
	 * @param string $code       Locale code, e.g. `fr_FR`.
	 * @param string $label      Human-readable label, e.g. `Français`.
	 * @param string $flag       Flag associated with the language (emoji or short code).
	 * @param bool   $is_default Whether this language becomes the theme's default.
	 * @return bool True on success.
	 */
	public function add_language( string $code, string $label, string $flag, bool $is_default = false ): bool;

	/**
	 * Removes a language and every translation registered for it.
	 *
	 * @param string $code Locale code to remove.
	 * @return bool True on success.
	 */
	public function remove_language( string $code ): bool;

	/**
	 * Returns every translatable string registered, with its translation for the given locale.
	 *
	 * @param string $locale Locale code, e.g. `fr_FR`.
	 * @return array<string, array{singular: string, plural: ?string, context: string}> Keyed by
	 *         string key (the original/English text, as used as `msgid` in `__()`/`_n()` calls).
	 */
	public function get_strings( string $locale ): array;

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
	public function set_string( string $locale, string $key, string $singular, ?string $plural = null, string $context = '' ): bool;

	/**
	 * Compiles the catalog of a single locale into a `.mo` file under `languages/`.
	 *
	 * @param string $locale Locale code to compile.
	 * @return bool True on success.
	 */
	public function compile( string $locale ): bool;

	/**
	 * Compiles the catalog of every active language.
	 *
	 * @return array<int, string> The list of locale codes successfully compiled.
	 */
	public function compile_all(): array;
}
