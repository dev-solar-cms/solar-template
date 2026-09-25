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
 * Reads a value from the theme's own `.env` file (see `.env.example`).
 *
 * Never used for database credentials: those belong to the parent `wordpress-dev-env` repo's own
 * `.env`, a separate file this theme never reads from or writes to.
 *
 * @param string $key           Name of the environment variable, e.g. `SOLAR_TEMPLATE_CACHE_TTL`.
 * @param mixed  $default_value Value returned when the key is not set.
 * @return mixed The value found, or $default_value.
 */
function solar_template_env( string $key, mixed $default_value = null ): mixed {
	static $loader = null;

	if ( null === $loader ) {
		$loader = solar_template_load_autoloader()
			? new \Solar_Template\Support\DotenvEnvironmentLoader()
			: null;

		if ( null !== $loader ) {
			$loader->load( get_template_directory() );
		}
	}

	return null !== $loader ? $loader->get( $key, $default_value ) : $default_value;
}

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

	if ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() ) {
		add_theme_support( 'woocommerce' );
	}
}
add_action( 'after_setup_theme', 'solar_template_setup' );

/**
 * Warns the site administrator when WooCommerce is missing or inactive.
 *
 * The theme still activates and renders normally without WooCommerce (see solar_template_setup(),
 * which only declares `woocommerce` theme support when the plugin is actually active); this only
 * makes the situation visible in wp-admin instead of silently skipping storefront features.
 *
 * @return void
 */
function solar_template_woocommerce_notice(): void {
	if ( ! solar_template_load_autoloader() || \Solar_Template\Support\WooCommerceStatus::is_active() ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		wp_kses(
			sprintf(
				/* translators: %s: link to the "Add plugins" screen. */
				__( 'Solar Template is designed for WooCommerce. Please <a href="%s">install and activate WooCommerce</a> to enable the storefront features.', 'solar-template' ),
				esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) )
			),
			array( 'a' => array( 'href' => true ) )
		)
	);
}
add_action( 'admin_notices', 'solar_template_woocommerce_notice' );

/**
 * Creates the theme's dedicated database tables and seeds their default data on activation.
 *
 * Only runs if the Composer autoloader is available, since it relies on classes under `inc/`; a
 * checkout without `composer install` yet already shows the admin notice from
 * solar_template_load_autoloader() and simply skips table creation until dependencies are
 * installed and the theme is reactivated.
 *
 * @return void
 */
function solar_template_activate(): void {
	if ( solar_template_load_autoloader() ) {
		\Solar_Template\Database\Installer::install();
	}
}
add_action( 'after_switch_theme', 'solar_template_activate' );

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

/**
 * Enqueues the theme's font and the compiled front-end assets built by the Vite pipeline
 * (`npm run build`/`npm run dev`).
 *
 * The Google Fonts stylesheet (Plus Jakarta Sans, all weights used by the design system) has no
 * local fallback: it is a small external request, accepted as-is per the design handoff. The
 * compiled CSS/JS files are optional: a theme checkout where `npm run build` has not been run yet
 * simply serves no custom CSS/JS instead of fataling, with an admin notice pointing at the missing
 * step. The query-string version on those two is the file's own modification time, so browsers
 * pick up a rebuilt asset immediately without any manual cache-busting.
 *
 * @return void
 */
function solar_template_enqueue_assets(): void {
	wp_enqueue_style(
		'solar-template-fonts',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
		array(),
		wp_get_theme( get_template() )->get( 'Version' )
	);

	$dist_url = get_template_directory_uri() . '/assets/dist';
	$dist_dir = get_template_directory() . '/assets/dist';

	$style_file  = "{$dist_dir}/main.css";
	$script_file = "{$dist_dir}/main.js";

	if ( file_exists( $style_file ) ) {
		wp_enqueue_style( 'solar-template', "{$dist_url}/main.css", array(), filemtime( $style_file ) );
	}

	if ( file_exists( $script_file ) ) {
		wp_enqueue_script( 'solar-template', "{$dist_url}/main.js", array(), filemtime( $script_file ), true );
	}

	if ( ! file_exists( $style_file ) && ! file_exists( $script_file ) ) {
		add_action(
			'admin_notices',
			static function (): void {
				printf(
					'<div class="notice notice-warning"><p>%s</p></div>',
					esc_html__( 'Solar Template: run "npm run build" in the theme directory to compile its CSS/JS assets.', 'solar-template' )
				);
			}
		);
	}
}
add_action( 'wp_enqueue_scripts', 'solar_template_enqueue_assets' );
