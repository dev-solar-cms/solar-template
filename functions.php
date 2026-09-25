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
 * Returns the WooCommerce shop page URL, or the site's front page when WooCommerce is missing/
 * inactive. Shared by solar_template_default_nav_items() and solar_template_mega_menu_columns()
 * so both fall back to the same page rather than duplicating this check.
 *
 * @return string
 */
function solar_template_shop_url(): string {
	if ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() && function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/' );
}

/**
 * Returns the permalink of the page whose slug is $slug, or $fallback (the site's front page by
 * default) when no such page exists yet.
 *
 * Shared by the navigation and footer helpers so both resolve a "closest existing page" link the
 * same way, without duplicating this lookup.
 *
 * @param string $slug     Page slug to look up.
 * @param string $fallback URL returned when no page with this slug exists; the site's front page
 *                         when left empty.
 * @return string
 */
function solar_template_page_url_by_slug( string $slug, string $fallback = '' ): string {
	$page = get_page_by_path( $slug );

	if ( $page ) {
		return get_permalink( $page );
	}

	return '' !== $fallback ? $fallback : home_url( '/' );
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
	$shop_url = solar_template_shop_url();

	$blog_page_id = (int) get_option( 'page_for_posts' );
	$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

	$contact_url = solar_template_page_url_by_slug( 'contact' );

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
 * Ensures a real menu item assigned to the "primary" location from Appearance > Menus carries the
 * same `site-header__menu-item` class solar_template_primary_nav_fallback() uses, so header
 * styling and JS behaviour (see assets/js/header.js) work identically whether an administrator
 * has assigned a menu there or not. An administrator marks an item for the mega menu the same way
 * the fallback does: by adding a `has-mega-menu` CSS class to it from the Menus screen.
 *
 * @param string[]  $classes Existing classes for this menu item's `<li>`.
 * @param \WP_Post  $item    Menu item object (untyped to match `Walker_Nav_Menu`'s own filter).
 * @param \stdClass $args    `wp_nav_menu()` arguments, including `theme_location`.
 * @return string[] Classes with `site-header__menu-item` added, or $classes unchanged.
 */
function solar_template_primary_nav_item_classes( array $classes, $item, $args ): array {
	if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
		return $classes;
	}

	$classes[] = 'site-header__menu-item';

	return $classes;
}
add_filter( 'nav_menu_css_class', 'solar_template_primary_nav_item_classes', 10, 3 );

/**
 * Ensures a real menu item's `<a>` carries the same `site-header__menu-link` class the fallback
 * uses, for the same parity reason as solar_template_primary_nav_item_classes() above.
 *
 * @param array     $atts Existing HTML attributes for this menu item's `<a>`.
 * @param \WP_Post  $item Menu item object.
 * @param \stdClass $args `wp_nav_menu()` arguments, including `theme_location`.
 * @return array Attributes with `site-header__menu-link` appended to `class`.
 */
function solar_template_primary_nav_link_attributes( array $atts, $item, $args ): array {
	if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
		return $atts;
	}

	$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' site-header__menu-link' );

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'solar_template_primary_nav_link_attributes', 10, 3 );

/**
 * Returns the columns shown in the header's "Collections" mega menu.
 *
 * Defaults to generic placeholder columns rather than real WooCommerce product categories: wiring
 * real category data is out of scope for this step (see the project roadmap's later
 * catalog-related steps). A future step, or the Group 10 administration screen, is expected to
 * hook into this filter with real taxonomy data.
 *
 * @return array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>
 */
function solar_template_mega_menu_columns(): array {
	$shop_url = solar_template_shop_url();

	$columns = array(
		array(
			'heading' => __( 'Shop by category', 'solar-template' ),
			'links'   => array(
				array(
					'label' => __( 'New In', 'solar-template' ),
					'url'   => $shop_url,
				),
				array(
					'label' => __( 'Best Sellers', 'solar-template' ),
					'url'   => $shop_url,
				),
				array(
					'label' => __( 'Limited Edition', 'solar-template' ),
					'url'   => $shop_url,
				),
			),
		),
		array(
			'heading' => __( 'Featured', 'solar-template' ),
			'links'   => array(
				array(
					'label' => __( 'View All Collections', 'solar-template' ),
					'url'   => $shop_url,
				),
			),
		),
	);

	/**
	 * Filters the columns shown in the header's "Collections" mega menu.
	 *
	 * @param array $columns List of { heading, links: [{ label, url }] }, see
	 *                       solar_template_mega_menu_columns()'s return type.
	 */
	return apply_filters( 'solar_template_mega_menu_columns', $columns );
}

/**
 * Reports whether the "Collections" mega menu should be rendered at all.
 *
 * Defaults to enabled; a future "Header" administration tab (Group 10 of the project roadmap) is
 * expected to hook into this filter with the site owner's actual preference.
 *
 * @return bool
 */
function solar_template_mega_menu_enabled(): bool {
	/**
	 * Filters whether the header's "Collections" mega menu is enabled.
	 *
	 * @param bool $enabled True by default.
	 */
	return (bool) apply_filters( 'solar_template_header_mega_menu_enabled', true );
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

/**
 * Returns the footer's column configuration (brand blurb, Shop/Information/Legal link columns,
 * newsletter copy).
 *
 * Link columns point at the closest existing equivalent already available (shop page, posts
 * page, a page matching a legal-page slug if one exists) rather than a dedicated view, for the
 * same reason as solar_template_default_nav_items() — real legal pages are built by a later step
 * of the project roadmap.
 *
 * @return array{
 *     brand: array{name: string, description: string},
 *     columns: array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>,
 *     newsletter: array{heading: string, description: string},
 * }
 */
function solar_template_footer_config(): array {
	$shop_url     = solar_template_shop_url();
	$blog_page_id = (int) get_option( 'page_for_posts' );
	$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

	$config = array(
		'brand'      => array(
			'name'        => get_bloginfo( 'name' ),
			'description' => __( 'A modern, configurable WooCommerce theme to showcase your products.', 'solar-template' ),
		),
		'columns'    => array(
			array(
				'heading' => __( 'Shop', 'solar-template' ),
				'links'   => array(
					array(
						'label' => __( 'All Products', 'solar-template' ),
						'url'   => $shop_url,
					),
					array(
						'label' => __( 'New In', 'solar-template' ),
						'url'   => $shop_url,
					),
					array(
						'label' => __( 'Sale', 'solar-template' ),
						'url'   => $shop_url,
					),
					array(
						'label' => __( 'Collections', 'solar-template' ),
						'url'   => $shop_url,
					),
				),
			),
			array(
				'heading' => __( 'Information', 'solar-template' ),
				'links'   => array(
					array(
						'label' => __( 'About', 'solar-template' ),
						'url'   => solar_template_page_url_by_slug( 'about' ),
					),
					array(
						'label' => __( 'Blog', 'solar-template' ),
						'url'   => $blog_url,
					),
					array(
						'label' => __( 'Contact', 'solar-template' ),
						'url'   => solar_template_page_url_by_slug( 'contact' ),
					),
				),
			),
			array(
				'heading' => __( 'Legal', 'solar-template' ),
				'links'   => array(
					array(
						'label' => __( 'Terms & Conditions', 'solar-template' ),
						'url'   => solar_template_page_url_by_slug( 'terms-and-conditions' ),
					),
					array(
						'label' => __( 'Privacy Policy', 'solar-template' ),
						'url'   => solar_template_page_url_by_slug( 'privacy-policy' ),
					),
					array(
						'label' => __( 'Legal Notice', 'solar-template' ),
						'url'   => solar_template_page_url_by_slug( 'legal-notice' ),
					),
					array(
						'label' => __( 'Cookies', 'solar-template' ),
						'url'   => solar_template_page_url_by_slug( 'cookies' ),
					),
				),
			),
		),
		'newsletter' => array(
			'heading'     => __( 'Newsletter', 'solar-template' ),
			'description' => __( 'Get exclusive offers first.', 'solar-template' ),
		),
	);

	/**
	 * Filters the footer's column configuration.
	 *
	 * @param array $config See solar_template_footer_config()'s return type.
	 */
	return apply_filters( 'solar_template_footer_config', $config );
}

/**
 * Returns the payment method badges shown in the footer's bottom bar.
 *
 * @return string[] Payment method labels, in display order.
 */
function solar_template_footer_payment_icons(): array {
	$icons = array( 'VISA', 'MC', 'PAYPAL', 'STRIPE', 'APPLE PAY' );

	/**
	 * Filters the payment method badges shown in the footer.
	 *
	 * @param string[] $icons Payment method labels, in display order.
	 */
	return apply_filters( 'solar_template_footer_payment_icons', $icons );
}

/**
 * Returns the footer's copyright line, with the `{year}` placeholder replaced by the current
 * year.
 *
 * @return string
 */
function solar_template_footer_copyright(): string {
	$default = sprintf(
		/* translators: %s: site name. */
		__( '© {year} %s — WordPress WooCommerce theme · All rights reserved', 'solar-template' ),
		get_bloginfo( 'name' )
	);

	/**
	 * Filters the footer's copyright line, before the `{year}` placeholder is replaced.
	 *
	 * @param string $template Copyright line, with a literal `{year}` placeholder.
	 */
	$template = apply_filters( 'solar_template_footer_copyright', $default );

	return str_replace( '{year}', gmdate( 'Y' ), $template );
}

/**
 * Refreshes the header's cart-count badge through WooCommerce's own AJAX cart fragments
 * mechanism, so adding a product to the cart updates the badge without a full page reload.
 *
 * WooCommerce enqueues its `wc-cart-fragments` script on the front end whenever at least one
 * callback is hooked to this filter, and that script already listens for the `added_to_cart`
 * event and swaps any DOM element matching a fragment's selector for the markup returned here —
 * no custom front-end JS is needed for this step. The selector below matches the exact markup
 * `template-parts/cart-badge.php` already renders in the header (see 02.01's cart badge), which
 * is why that badge is always present in the DOM, even at zero items: this fragment can only
 * replace an element that already exists.
 *
 * @param array $fragments Existing fragments, keyed by CSS selector.
 * @return array $fragments with the cart badge's selector added/replaced.
 */
function solar_template_cart_fragments( array $fragments ): array {
	ob_start();
	get_template_part( 'template-parts/cart-badge' );
	$fragments['span.site-header__cart-count'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'solar_template_cart_fragments' );

/**
 * Enqueues WooCommerce's own `wc-cart-fragments` script on the front end.
 *
 * WooCommerce registers this script but only auto-enqueues it from its own "Cart" widget
 * (`WC_Widget_Cart`); since the header's cart badge is custom markup rather than that widget,
 * the theme has to enqueue it itself for solar_template_cart_fragments() above to ever run in the
 * browser — confirmed by reading WooCommerce's own `WC_Frontend_Scripts::load_scripts()`, which
 * does not enqueue it unconditionally.
 *
 * @return void
 */
function solar_template_enqueue_cart_fragments(): void {
	if ( solar_template_load_autoloader() && \Solar_Template\Support\WooCommerceStatus::is_active() ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'solar_template_enqueue_cart_fragments' );

/**
 * Returns the front page hero section's content (eyebrow label, three-line heading, subtitle,
 * both CTAs, trust badges, and the floating "highlight" card shown over the media block).
 *
 * All editorial content, exposed via a single filterable array rather than wired to a real
 * WooCommerce product: this step only renders the section itself (see the project roadmap), a
 * future "Home page" administration tab is expected to hook into this filter with the site
 * owner's actual copy/image, same convention as solar_template_footer_config() and friends.
 *
 * @return array{
 *     eyebrow: string,
 *     heading_lines: string[],
 *     subtitle: string,
 *     primary_cta: array{label: string, url: string},
 *     secondary_cta: array{label: string, url: string},
 *     trust_badges: array<int, array{icon: string, label: string}>,
 *     image_url: string|null,
 *     image_alt: string,
 *     highlight: array{
 *         eyebrow: string,
 *         title: string,
 *         price: float|null,
 *         regular_price: float|null,
 *         currency_symbol: string,
 *         progress_percent: int,
 *         note: string,
 *     },
 * }
 */
function solar_template_hero_config(): array {
	$shop_url = solar_template_shop_url();

	$defaults = array(
		'eyebrow'       => __( 'Spring · Summer 2025 Collection', 'solar-template' ),
		// These three lines form a single sentence, split for the display treatment of the
		// mockup (each on its own line, the middle one in accent gold italics) — kept as separate
		// strings so each line can wrap/translate independently rather than a single string with
		// hardcoded line breaks.
		'heading_lines' => array(
			__( 'The essentials,', 'solar-template' ),
			__( 'reimagined', 'solar-template' ),
			__( 'for you.', 'solar-template' ),
		),
		'subtitle'      => __( 'Discover our premium selection of products, carefully chosen to combine quality, design and durability.', 'solar-template' ),
		'primary_cta'   => array(
			'label' => __( 'Explore the shop', 'solar-template' ),
			'url'   => $shop_url,
		),
		'secondary_cta' => array(
			'label' => __( 'See what’s new →', 'solar-template' ),
			'url'   => $shop_url,
		),
		'trust_badges'  => array(
			array(
				'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>',
				'label' => __( 'Free shipping from €60', 'solar-template' ),
			),
			array(
				'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>',
				'label' => __( 'Returns within 30 days', 'solar-template' ),
			),
			array(
				'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
				'label' => __( 'Secure payment', 'solar-template' ),
			),
		),
		'image_url'     => null,
		'image_alt'     => '',
		'highlight'     => array(
			'eyebrow'          => __( 'Favorite pick', 'solar-template' ),
			'title'            => __( 'Summer 2025 Collection', 'solar-template' ),
			'price'            => 129.0,
			'regular_price'    => 179.0,
			'currency_symbol'  => '€',
			'progress_percent' => 62,
			'note'             => __( '38% already claimed · Limited stock', 'solar-template' ),
		),
	);

	/**
	 * Filters the front page hero section's content.
	 *
	 * @param array $config See solar_template_hero_config()'s return type.
	 */
	return apply_filters( 'solar_template_hero_config', $defaults );
}

/**
 * Returns the front page's "Featured products" section header (eyebrow label, heading, "view
 * all" link) — everything except the product data itself, see
 * solar_template_get_featured_products().
 *
 * @return array{eyebrow: string, heading: string, view_all: array{label: string, url: string}}
 */
function solar_template_featured_products_heading(): array {
	$defaults = array(
		'eyebrow'  => __( 'Selection', 'solar-template' ),
		'heading'  => __( 'Favorites', 'solar-template' ),
		'view_all' => array(
			'label' => __( 'View all →', 'solar-template' ),
			'url'   => solar_template_shop_url(),
		),
	);

	/**
	 * Filters the front page's "Featured products" section header.
	 *
	 * @param array $config See solar_template_featured_products_heading()'s return type.
	 */
	return apply_filters( 'solar_template_featured_products_heading', $defaults );
}

/**
 * Maps a `WC_Product` to the `$args` shape expected by template-parts/product-card.php.
 *
 * @param \WC_Product $product Product to map.
 * @return array See template-parts/product-card.php's documented `$args` keys.
 */
function solar_template_map_product_to_card_args( \WC_Product $product ): array {
	$image_id = $product->get_image_id();
	$price    = $product->get_price();
	$regular  = $product->get_regular_price();

	$discount_percent = null;
	$badge            = null;

	if ( $product->is_on_sale() && '' !== $regular && (float) $regular > 0 ) {
		$discount_percent = (int) round( ( ( (float) $regular - (float) $price ) / (float) $regular ) * 100 );
		$badge            = array(
			'type'  => 'sale',
			'label' => __( 'On Sale', 'solar-template' ),
		);
	}

	$categories = get_the_terms( $product->get_id(), 'product_cat' );
	$category   = ( $categories && ! is_wp_error( $categories ) ) ? reset( $categories )->name : '';

	return array(
		'image_url'        => $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : null,
		'image_alt'        => $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '',
		'permalink'        => get_permalink( $product->get_id() ),
		'badge'            => $badge,
		'category'         => $category,
		'name'             => $product->get_name(),
		'price'            => '' !== $price ? (float) $price : null,
		'regular_price'    => '' !== $regular ? (float) $regular : null,
		'currency_symbol'  => get_woocommerce_currency_symbol(),
		'discount_percent' => $discount_percent,
		'in_wishlist'      => false,
		'swatches'         => array(),
	);
}

/**
 * Returns the front page's featured products, mapped for template-parts/product-card.php.
 *
 * Reads real WooCommerce data (products marked "Featured" from the product edit screen) — unlike
 * solar_template_hero_config(), this is not editorial placeholder content. Returns an empty array
 * when WooCommerce is missing/inactive, or when no product is currently marked as featured, so the
 * calling template-part can skip rendering the section entirely rather than showing an empty grid.
 *
 * @param int $limit Maximum number of products to return.
 * @return array<int, array> List of template-parts/product-card.php `$args` arrays.
 */
function solar_template_get_featured_products( int $limit = 4 ): array {
	if ( ! solar_template_load_autoloader() || ! \Solar_Template\Support\WooCommerceStatus::is_active() || ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$products = wc_get_products(
		array(
			'featured' => true,
			'status'   => 'publish',
			'limit'    => $limit,
			'orderby'  => 'date',
			'order'    => 'DESC',
		)
	);

	return array_map( 'solar_template_map_product_to_card_args', $products );
}
