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
 * from a future administration screen is picked up automatically.
 *
 * @return void
 */
function solar_template_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	load_theme_textdomain( 'solar-template', get_template_directory() . '/languages' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'solar-template' ),
		)
	);

	if ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() ) {
		add_theme_support( 'woocommerce' );
	}
}
add_action( 'after_setup_theme', 'solar_template_setup' );

/**
 * Returns the social network links shown in the header top bar.
 *
 * Defaults to a placeholder `#` URL for each network so the icons match the design handoff
 * out of the box; a future "Header" administration tab is expected to hook into this filter
 * with the site owner's actual URLs (see Group 10 of the project roadmap).
 *
 * @return array<string, array{url: string, label: string, icon: string}>
 */
function solar_template_social_links(): array {
	$defaults = array(
		'instagram' => array(
			'url'   => '#',
			'label' => __( 'Instagram', 'solar-template' ),
			'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
		),
		'facebook'  => array(
			'url'   => '#',
			'label' => __( 'Facebook', 'solar-template' ),
			'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
		),
		'x'         => array(
			'url'   => '#',
			'label' => __( 'X (Twitter)', 'solar-template' ),
			'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		),
	);

	/**
	 * Filters the social network links shown in the header top bar.
	 *
	 * @param array $links Social network slug => { url, label, icon } map.
	 */
	return apply_filters( 'solar_template_social_links', $defaults );
}

/**
 * Returns the URL the header's account icon should link to.
 *
 * Points at the WooCommerce "My account" page when WooCommerce is active, or the standard
 * WordPress login screen otherwise, so the icon never links to a broken/non-existent page.
 *
 * @return string
 */
function solar_template_account_url(): string {
	if ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() && function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return wp_login_url();
}

/**
 * Returns the URL the header's cart icon should link to.
 *
 * Points at the WooCommerce cart page when WooCommerce is active, or the site's front page
 * otherwise, so the icon never links to a broken/non-existent page.
 *
 * @return string
 */
function solar_template_cart_url(): string {
	if ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() && function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}

	return home_url( '/' );
}

/**
 * Returns the number of items currently in the visitor's WooCommerce cart.
 *
 * @return int 0 when WooCommerce is missing/inactive or the cart is not available yet.
 */
function solar_template_cart_count(): int {
	if ( ! solar_template_load_autoloader() || ! \Solar_Template\Support\WooCommerceStatus::is_active() || ! function_exists( 'WC' ) ) {
		return 0;
	}

	if ( null === WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Returns the default primary navigation items, used when no menu is assigned to the
 * "primary" location from Appearance > Menus.
 *
 * Every item points at the closest existing equivalent already available in a stock
 * WordPress/WooCommerce install (shop page, posts page, a "contact" page if one exists) rather
 * than a dedicated archive/promotion view, since building those views is the scope of later
 * steps of the project roadmap; a future administration screen or a real menu assignment is
 * expected to refine these.
 *
 * @return array<int, array{slug: string, label: string, url: string, has_mega_menu?: bool}>
 */
function solar_template_default_nav_items(): array {
	$shop_url = ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() && function_exists( 'wc_get_page_permalink' ) )
		? wc_get_page_permalink( 'shop' )
		: home_url( '/' );

	$blog_page_id = (int) get_option( 'page_for_posts' );
	$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

	$contact_page = get_page_by_path( 'contact' );
	$contact_url  = $contact_page ? get_permalink( $contact_page ) : home_url( '/' );

	$items = array(
		array(
			'slug'  => 'shop',
			'label' => __( 'Shop', 'solar-template' ),
			'url'   => $shop_url,
		),
		array(
			'slug'  => 'new',
			'label' => __( 'New In', 'solar-template' ),
			'url'   => $shop_url,
		),
		array(
			'slug'          => 'collections',
			'label'         => __( 'Collections', 'solar-template' ),
			'url'           => $shop_url,
			'has_mega_menu' => true,
		),
		array(
			'slug'  => 'sale',
			'label' => __( 'Sale', 'solar-template' ),
			'url'   => $shop_url,
		),
		array(
			'slug'  => 'blog',
			'label' => __( 'Blog', 'solar-template' ),
			'url'   => $blog_url,
		),
		array(
			'slug'  => 'contact',
			'label' => __( 'Contact', 'solar-template' ),
			'url'   => $contact_url,
		),
	);

	/**
	 * Filters the default primary navigation items (used only as a fallback, see
	 * solar_template_primary_nav_fallback()).
	 *
	 * @param array $items Navigation items, see solar_template_default_nav_items() return type.
	 */
	return apply_filters( 'solar_template_primary_nav_items', $items );
}

/**
 * Reports whether the given default navigation item slug matches the page currently viewed.
 *
 * @param string $slug One of solar_template_default_nav_items()'s item slugs.
 * @return bool True when this item should be marked as the current one.
 */
function solar_template_is_current_nav_item( string $slug ): bool {
	switch ( $slug ) {
		case 'shop':
			return function_exists( 'is_shop' ) && is_shop();
		case 'collections':
			return function_exists( 'is_product_category' ) && is_product_category();
		case 'blog':
			return is_home() || is_singular( 'post' );
		case 'contact':
			return is_page( 'contact' );
		default:
			return false;
	}
}

/**
 * Renders the primary navigation when no menu is assigned to that location from
 * Appearance > Menus (`wp_nav_menu()`'s `fallback_cb`).
 *
 * @return void
 */
function solar_template_primary_nav_fallback(): void {
	echo '<ul class="site-header__menu">';

	foreach ( solar_template_default_nav_items() as $item ) {
		$classes = array( 'site-header__menu-item' );

		if ( ! empty( $item['has_mega_menu'] ) ) {
			$classes[] = 'has-mega-menu';
		}

		if ( solar_template_is_current_nav_item( $item['slug'] ) ) {
			$classes[] = 'is-current';
		}

		printf(
			'<li class="%1$s"><a class="site-header__menu-link" href="%2$s">%3$s</a></li>',
			esc_attr( implode( ' ', $classes ) ),
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}

	echo '</ul>';
}

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
