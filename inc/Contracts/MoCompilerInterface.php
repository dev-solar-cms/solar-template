<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Part of the theme's dependency-inversion layer (Solar_Template\Contracts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define the contract used to compile a set of translated strings into a binary `.mo`
 *          file, so the underlying library (currently gettext/gettext) can be swapped without
 *          touching Solar_Template\I18n\DatabaseTranslator.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compiles a translation catalog into a gettext `.mo` file.
 */
interface MoCompilerInterface {

	/**
	 * Writes a `.mo` file for a single locale.
	 *
	 * @param string $locale           Locale code, e.g. `fr_FR`.
	 * @param array  $entries          List of entries, each shaped as
	 *                                 `['key' => string, 'singular' => string, 'plural' => ?string, 'context' => string]`.
	 * @param string $plural_rule      Plural rule expression using `n` (e.g. `n != 1`), for the
	 *                                 two-form (singular/plural) system used by this theme.
	 * @param string $destination_file Absolute path of the `.mo` file to write.
	 * @return bool True on success.
	 */
	public function write( string $locale, array $entries, string $plural_rule, string $destination_file ): bool;
}
