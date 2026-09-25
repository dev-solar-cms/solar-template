<?php
/**
 * Created: 2026-09-25 15:55 CEST
 * Role: Translator factory (Solar_Template\I18n).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the theme's translator, wired to the real WordPress database and `.mo` compiler.
 *          Not yet called from anywhere in the theme — prepared ahead of the future Group 11 i18n
 *          administration screen (see DECISIONS.md's i18n plan), which will use it to manage
 *          languages/translations from wp-admin.
 *
 * @package Solar_Template
 */

namespace Solar_Template\I18n;

use Solar_Template\Contracts\TranslatorInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Factory/holder for the theme's real TranslatorInterface implementation.
 */
final class Translator {

	/**
	 * @var TranslatorInterface|null
	 */
	private static ?TranslatorInterface $instance = null;

	/**
	 * @return TranslatorInterface
	 */
	public static function instance(): TranslatorInterface {
		global $wpdb;

		if ( null === self::$instance ) {
			self::$instance = new DatabaseTranslator(
				$wpdb,
				new GettextMoCompiler(),
				get_template_directory() . '/languages'
			);
		}

		return self::$instance;
	}
}
