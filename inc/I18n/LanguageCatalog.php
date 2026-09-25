<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Static reference data for the theme's translation system (Solar_Template\I18n).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: List the languages a future "add a language" administration screen can offer,
 *          with a two-form (singular/plural) plural rule for each, and the subset of languages
 *          pre-seeded at theme activation (fr_FR + en_US). No language is hardcoded anywhere
 *          else in the theme: adding support for a new one only means adding a row here.
 *
 * @package Solar_Template
 */

namespace Solar_Template\I18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static catalog of languages available to add, independent from the languages actually
 * installed (stored in `wp_solar_template_languages`).
 */
final class LanguageCatalog {

	/**
	 * Every language that can be proposed by the future "add a language" admin screen.
	 *
	 * The plural rule uses the theme's simplified two-form (singular/plural) system, expressed
	 * with `n`, e.g. `n != 1` (English-like) or `n > 1` (French-like).
	 *
	 * @return array<string, array{label: string, flag: string, plural_rule: string}>
	 */
	public static function available(): array {
		return array(
			'fr_FR' => array(
				'label'       => 'Français',
				'flag'        => '🇫🇷',
				'plural_rule' => 'n > 1',
			),
			'en_US' => array(
				'label'       => 'English (US)',
				'flag'        => '🇺🇸',
				'plural_rule' => 'n != 1',
			),
			'en_GB' => array(
				'label'       => 'English (UK)',
				'flag'        => '🇬🇧',
				'plural_rule' => 'n != 1',
			),
			'es_ES' => array(
				'label'       => 'Español',
				'flag'        => '🇪🇸',
				'plural_rule' => 'n != 1',
			),
			'de_DE' => array(
				'label'       => 'Deutsch',
				'flag'        => '🇩🇪',
				'plural_rule' => 'n != 1',
			),
			'it_IT' => array(
				'label'       => 'Italiano',
				'flag'        => '🇮🇹',
				'plural_rule' => 'n != 1',
			),
			'pt_PT' => array(
				'label'       => 'Português',
				'flag'        => '🇵🇹',
				'plural_rule' => 'n != 1',
			),
			'pt_BR' => array(
				'label'       => 'Português do Brasil',
				'flag'        => '🇧🇷',
				'plural_rule' => 'n > 1',
			),
			'nl_NL' => array(
				'label'       => 'Nederlands',
				'flag'        => '🇳🇱',
				'plural_rule' => 'n != 1',
			),
		);
	}

	/**
	 * Languages pre-seeded at theme activation.
	 *
	 * @return array<string, array{label: string, flag: string, plural_rule: string, is_default: bool}>
	 */
	public static function defaults(): array {
		$available = self::available();

		return array(
			'fr_FR' => $available['fr_FR'] + array( 'is_default' => true ),
			'en_US' => $available['en_US'] + array( 'is_default' => false ),
		);
	}

	/**
	 * Returns the plural rule of a known language, falling back to the English-like rule.
	 *
	 * @param string $code Locale code, e.g. `fr_FR`.
	 * @return string Plural rule expression using `n`.
	 */
	public static function plural_rule_for( string $code ): string {
		$available = self::available();

		return $available[ $code ]['plural_rule'] ?? 'n != 1';
	}
}
