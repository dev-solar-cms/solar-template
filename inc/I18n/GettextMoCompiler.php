<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Default implementation of Solar_Template\Contracts\MoCompilerInterface.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compile the theme's translation catalog into `.mo` files with the gettext/gettext
 *          library. This is the only class allowed to reference that library directly.
 *
 * @package Solar_Template
 */

namespace Solar_Template\I18n;

use Gettext\Generator\MoGenerator;
use Gettext\Translation;
use Gettext\Translations;
use Solar_Template\Contracts\MoCompilerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compiles the translation catalog with gettext/gettext, always writing the two-form
 * (singular/plural) header expected by the theme's admin catalog editor.
 */
final class GettextMoCompiler implements MoCompilerInterface {

	/**
	 * Text domain baked into the compiled `.mo` files.
	 *
	 * @var string
	 */
	private const DOMAIN = 'solar-template';

	/**
	 * Writes a `.mo` file for a single locale.
	 *
	 * @param string $locale           Locale code, e.g. `fr_FR`.
	 * @param array  $entries          List of entries, each shaped as
	 *                                 `['key' => string, 'singular' => string, 'plural' => ?string, 'context' => string]`.
	 * @param string $plural_rule      Plural rule expression using `n` (e.g. `n != 1`).
	 * @param string $destination_file Absolute path of the `.mo` file to write.
	 * @return bool True on success.
	 */
	public function write( string $locale, array $entries, string $plural_rule, string $destination_file ): bool {
		$translations = Translations::create( self::DOMAIN );
		$translations->getHeaders()->setLanguage( $locale );
		$translations->getHeaders()->setPluralForm( 2, $plural_rule );

		foreach ( $entries as $entry ) {
			$translation = Translation::create( $entry['context'] ?? '', $entry['key'] );
			$translation->translate( $entry['singular'] );

			if ( ! empty( $entry['plural'] ) ) {
				$translation->setPlural( $entry['key'] );
				$translation->translatePlural( $entry['plural'] );
			}

			$translations->add( $translation );
		}

		$destination_dir = dirname( $destination_file );

		if ( ! is_dir( $destination_dir ) ) {
			wp_mkdir_p( $destination_dir );
		}

		return ( new MoGenerator() )->includeHeaders( true )->generateFile( $translations, $destination_file );
	}
}
