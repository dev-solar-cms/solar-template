<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Main bootstrap file of the Solar Template theme (loaded by WordPress on every request).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Wire the Composer autoloader and register the theme's core WordPress hooks. Kept
 *          procedural as required by WordPress' own conventions for this file; actual behaviour
 *          lives in classes under `inc/` (namespace `Solar_Template\*`).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the Composer autoloader if dependencies were installed.
 *
 * A theme cloned from git will not have `vendor/` until `composer install` is run: this must
 * degrade gracefully (admin notice, no fatal error) rather than break the whole site.
 *
 * @return bool True when the autoloader was loaded successfully.
 */
function solar_template_load_autoloader(): bool {
	static $loaded = null;

	if ( null !== $loaded ) {
		return $loaded;
	}

	$autoload_file = get_template_directory() . '/vendor/autoload.php';

	$loaded = file_exists( $autoload_file );

	if ( $loaded ) {
		require_once $autoload_file;
	} else {
		add_action(
			'admin_notices',
			static function (): void {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html__( 'Solar Template: run "composer install" in the theme directory to enable its PHP dependencies.', 'solar-template' )
				);
			}
		);
	}

	return $loaded;
}
solar_template_load_autoloader();

/**
 * Registers the theme's core support features and loads its compiled translations.
 *
 * `.mo` files are looked up in `languages/`, named `{locale}.mo` (WordPress' just-in-time loading
 * expects a bare locale name for a theme's own languages directory): this is exactly what
 * Solar_Template\I18n\DatabaseTranslator::compile() writes, so any language added and compiled
 * from the future administration screen (Group 11) is picked up automatically.
 *
 * @return void
 */
function solar_template_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	load_theme_textdomain( 'solar-template', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'solar_template_setup' );

/**
 * Builds the theme's translator, wired to the real WordPress database and `.mo` compiler.
 *
 * @return \Solar_Template\Contracts\TranslatorInterface
 */
function solar_template_translator(): \Solar_Template\Contracts\TranslatorInterface {
	global $wpdb;

	static $translator = null;

	if ( null === $translator ) {
		$translator = new \Solar_Template\I18n\DatabaseTranslator(
			$wpdb,
			new \Solar_Template\I18n\GettextMoCompiler(),
			get_template_directory() . '/languages'
		);
	}

	return $translator;
}
