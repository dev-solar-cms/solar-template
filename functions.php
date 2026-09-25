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

/**
 * Returns the front page's "Categories" section header (eyebrow label, heading).
 *
 * @return array{eyebrow: string, heading: string}
 */
function solar_template_categories_heading(): array {
	$defaults = array(
		'eyebrow' => __( 'Collections', 'solar-template' ),
		'heading' => __( 'Explore our worlds', 'solar-template' ),
	);

	/**
	 * Filters the front page's "Categories" section header.
	 *
	 * @param array $config See solar_template_categories_heading()'s return type.
	 */
	return apply_filters( 'solar_template_categories_heading', $defaults );
}

/**
 * Returns the top-level WooCommerce product categories shown on the front page's "Categories"
 * section, mapped to {name, url, image_url, image_alt}.
 *
 * Reads real WooCommerce taxonomy data (not editorial placeholder content), ordered by product
 * count (most populated first) so the featured categories are the ones with actual products in
 * them. Returns an empty array when WooCommerce is missing/inactive, or when the store has no
 * product category yet (beyond the default "Uncategorized" one), so the calling template-part can
 * skip rendering the section entirely rather than showing an empty grid.
 *
 * @param int $limit Maximum number of categories to return.
 * @return array<int, array{name: string, url: string, image_url: string|null, image_alt: string}>
 */
function solar_template_get_home_categories( int $limit = 5 ): array {
	if ( ! solar_template_load_autoloader() || ! \Solar_Template\Support\WooCommerceStatus::is_active() ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
			'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return array_map(
		static function ( \WP_Term $term ): array {
			$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

			return array(
				'name'      => $term->name,
				'url'       => get_term_link( $term ),
				'image_url' => $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'large' ) : null,
				'image_alt' => $term->name,
			);
		},
		$terms
	);
}

/**
 * Returns the front page "Brand story" section's content (eyebrow, heading, paragraphs, image, a
 * floating stat highlight card, a row of three stats, and a CTA).
 *
 * All editorial content, exposed via a single filterable array — same convention as
 * solar_template_hero_config(): a future "Home page" administration tab is expected to hook into
 * this filter with the site owner's actual copy/image.
 *
 * @return array{
 *     eyebrow: string,
 *     heading: string,
 *     paragraphs: string[],
 *     image_url: string|null,
 *     image_alt: string,
 *     highlight: array{value: string, label: string},
 *     stats: array<int, array{value: string, label: string}>,
 *     cta: array{label: string, url: string},
 * }
 */
function solar_template_brand_story_config(): array {
	$defaults = array(
		'eyebrow'    => __( 'Our story', 'solar-template' ),
		'heading'    => __( 'Craftsmanship, quality and contemporary design.', 'solar-template' ),
		'paragraphs' => array(
			__( 'For over a decade, we have been selecting and offering products that combine artisanal quality, durability and modern aesthetics.', 'solar-template' ),
			__( 'Every item is chosen with care, paying close attention to materials, manufacturing and environmental impact.', 'solar-template' ),
		),
		'image_url'  => null,
		'image_alt'  => '',
		'highlight'  => array(
			'value' => __( '10+', 'solar-template' ),
			'label' => __( 'years of expertise', 'solar-template' ),
		),
		'stats'      => array(
			array(
				'value' => __( '50K+', 'solar-template' ),
				'label' => __( 'Happy customers', 'solar-template' ),
			),
			array(
				'value' => __( '200+', 'solar-template' ),
				'label' => __( 'Products available', 'solar-template' ),
			),
			array(
				'value' => __( '4.9★', 'solar-template' ),
				'label' => __( 'Average rating', 'solar-template' ),
			),
		),
		'cta'        => array(
			'label' => __( 'Learn more', 'solar-template' ),
			'url'   => solar_template_page_url_by_slug( 'about' ),
		),
	);

	/**
	 * Filters the front page "Brand story" section's content.
	 *
	 * @param array $config See solar_template_brand_story_config()'s return type.
	 */
	return apply_filters( 'solar_template_brand_story_config', $defaults );
}

/**
 * Returns the front page "Testimonials" section header (eyebrow label, heading).
 *
 * @return array{eyebrow: string, heading: string}
 */
function solar_template_testimonials_heading(): array {
	$defaults = array(
		'eyebrow' => __( 'Testimonials', 'solar-template' ),
		'heading' => __( 'What our customers say', 'solar-template' ),
	);

	/**
	 * Filters the front page "Testimonials" section header.
	 *
	 * @param array $config See solar_template_testimonials_heading()'s return type.
	 */
	return apply_filters( 'solar_template_testimonials_heading', $defaults );
}

/**
 * Returns the front page's customer testimonials.
 *
 * Editorial placeholder content, exposed via a single filterable array — same convention as
 * solar_template_hero_config(): no dedicated custom post type or admin screen is introduced for
 * this step, per the project roadmap ("simply defined" content source); a future "Home page"
 * administration tab is expected to hook into this filter with the site owner's actual reviews.
 *
 * @return array<int, array{quote: string, rating: int, author_name: string, author_since: string, avatar_url: string|null}>
 */
function solar_template_get_testimonials(): array {
	$defaults = array(
		array(
			'quote'        => __( 'Ultra-fast delivery and the product exactly matched my expectations. Customer service is outstanding. Highly recommend!', 'solar-template' ),
			'rating'       => 5,
			'author_name'  => __( 'Marie L.', 'solar-template' ),
			'author_since' => __( 'Customer since 2023', 'solar-template' ),
			'avatar_url'   => null,
		),
		array(
			'quote'        => __( 'Exceptional quality for a very reasonable price. A loyal customer for 2 years and never disappointed. Products that last.', 'solar-template' ),
			'rating'       => 5,
			'author_name'  => __( 'Thomas R.', 'solar-template' ),
			'author_since' => __( 'Customer since 2022', 'solar-template' ),
			'avatar_url'   => null,
		),
		array(
			'quote'        => __( 'Responsive customer service and impeccable product quality. My purchase far exceeded my expectations. Very satisfied!', 'solar-template' ),
			'rating'       => 4,
			'author_name'  => __( 'Sophie M.', 'solar-template' ),
			'author_since' => __( 'Customer since 2024', 'solar-template' ),
			'avatar_url'   => null,
		),
	);

	/**
	 * Filters the front page's customer testimonials.
	 *
	 * @param array $testimonials See solar_template_get_testimonials()'s return type.
	 */
	return apply_filters( 'solar_template_testimonials', $defaults );
}

/**
 * Returns the front page's "Blog preview" section header (eyebrow label, heading, "view all"
 * link) — everything except the post data itself, see solar_template_get_blog_preview_posts().
 *
 * @return array{eyebrow: string, heading: string, view_all: array{label: string, url: string}}
 */
function solar_template_blog_preview_heading(): array {
	$blog_page_id = (int) get_option( 'page_for_posts' );
	$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

	$defaults = array(
		'eyebrow'  => __( 'News', 'solar-template' ),
		'heading'  => __( 'Inspiration & tips', 'solar-template' ),
		'view_all' => array(
			'label' => __( 'View all articles →', 'solar-template' ),
			'url'   => $blog_url,
		),
	);

	/**
	 * Filters the front page's "Blog preview" section header.
	 *
	 * @param array $config See solar_template_blog_preview_heading()'s return type.
	 */
	return apply_filters( 'solar_template_blog_preview_heading', $defaults );
}

/**
 * Estimates a post's reading time, in whole minutes (minimum 1), from its word count at a
 * conventional average reading speed of 200 words per minute.
 *
 * @param \WP_Post $post Post to estimate.
 * @return int Reading time, in minutes.
 */
function solar_template_estimate_reading_time_minutes( \WP_Post $post ): int {
	$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );

	return max( 1, (int) ceil( $word_count / 200 ) );
}

/**
 * Maps a `WP_Post` to the `$args` shape expected by template-parts/blog-card.php.
 *
 * @param \WP_Post $post Post to map.
 * @return array See template-parts/blog-card.php's documented `$args` keys.
 */
function solar_template_map_post_to_card_args( \WP_Post $post ): array {
	$categories = get_the_category( $post->ID );
	$category   = ! empty( $categories ) ? $categories[0]->name : '';

	$meta = sprintf(
		/* translators: 1: publication date, 2: estimated reading time in minutes. */
		__( '%1$s · %2$d min read', 'solar-template' ),
		get_the_date( '', $post ),
		solar_template_estimate_reading_time_minutes( $post )
	);

	$thumbnail_url = get_the_post_thumbnail_url( $post, 'medium_large' );

	return array(
		'image_url'         => false !== $thumbnail_url ? $thumbnail_url : null,
		'image_alt'         => get_the_title( $post ),
		'permalink'         => get_permalink( $post ),
		'badge'             => '' !== $category ? array(
			'type'  => 'outline-gold',
			'label' => $category,
		) : null,
		'meta'              => $meta,
		'title'             => get_the_title( $post ),
		'excerpt'           => get_the_excerpt( $post ),
		'author_name'       => get_the_author_meta( 'display_name', $post->post_author ),
		'author_avatar_url' => get_avatar_url( $post->post_author ),
	);
}

/**
 * Returns the front page "Newsletter" section's content (eyebrow, heading, description, form
 * placeholder/button labels, and feedback messages) — everything except the submission handling
 * itself, see solar_template_handle_newsletter_subscription().
 *
 * @return array{
 *     eyebrow: string,
 *     heading: string,
 *     description: string,
 *     email_placeholder: string,
 *     submit_label: string,
 *     privacy_note: string,
 *     success_message: string,
 *     already_subscribed_message: string,
 *     invalid_email_message: string,
 *     error_message: string,
 * }
 */
function solar_template_newsletter_config(): array {
	$defaults = array(
		'eyebrow'                    => __( 'Newsletter', 'solar-template' ),
		'heading'                    => __( 'Stay in the loop', 'solar-template' ),
		'description'                => __( 'Get our new arrivals, exclusive offers and inspiration straight to your inbox.', 'solar-template' ),
		'email_placeholder'          => __( 'your@email.com', 'solar-template' ),
		'submit_label'               => __( 'Subscribe', 'solar-template' ),
		'privacy_note'               => __( 'Unsubscribe at any time. No spam, ever.', 'solar-template' ),
		'success_message'            => __( 'Thank you for subscribing!', 'solar-template' ),
		'already_subscribed_message' => __( 'This email address is already subscribed.', 'solar-template' ),
		'invalid_email_message'      => __( 'Please enter a valid email address.', 'solar-template' ),
		'error_message'              => __( 'Something went wrong. Please try again.', 'solar-template' ),
	);

	/**
	 * Filters the front page "Newsletter" section's content.
	 *
	 * @param array $config See solar_template_newsletter_config()'s return type.
	 */
	return apply_filters( 'solar_template_newsletter_config', $defaults );
}

/**
 * Builds the theme's newsletter subscriber repository, wired to the real WordPress database.
 *
 * @return \Solar_Template\Newsletter\SubscriberRepository
 */
function solar_template_newsletter_repository(): \Solar_Template\Newsletter\SubscriberRepository {
	global $wpdb;

	static $repository = null;

	if ( null === $repository ) {
		$repository = new \Solar_Template\Newsletter\SubscriberRepository( $wpdb );
	}

	return $repository;
}

/**
 * Handles the front page newsletter form's AJAX submission (`solar_template_newsletter_subscribe`
 * action): validates the nonce and the submitted email address, records the subscription, and
 * responds with the JSON feedback message the front-end (assets/js/newsletter.js) displays.
 *
 * @return void
 */
function solar_template_handle_newsletter_subscription(): void {
	check_ajax_referer( 'solar_template_newsletter', 'nonce' );

	$config = solar_template_newsletter_config();
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => $config['invalid_email_message'] ) );
	}

	if ( ! solar_template_load_autoloader() ) {
		wp_send_json_error( array( 'message' => $config['error_message'] ) );
	}

	$subscribed = solar_template_newsletter_repository()->subscribe( $email );

	if ( $subscribed ) {
		wp_send_json_success( array( 'message' => $config['success_message'] ) );
	}

	wp_send_json_error( array( 'message' => $config['already_subscribed_message'] ) );
}
add_action( 'wp_ajax_solar_template_newsletter_subscribe', 'solar_template_handle_newsletter_subscription' );
add_action( 'wp_ajax_nopriv_solar_template_newsletter_subscribe', 'solar_template_handle_newsletter_subscription' );

/**
 * Enqueues the front page newsletter form's own script and localizes the AJAX endpoint/nonce it
 * needs, on top of the theme's compiled main script.
 *
 * @return void
 */
function solar_template_enqueue_newsletter_script(): void {
	wp_localize_script(
		'solar-template',
		'solarTemplateNewsletter',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'solar_template_newsletter' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'solar_template_enqueue_newsletter_script', 20 );

/**
 * Returns the front page's latest published blog posts, mapped for template-parts/blog-card.php.
 *
 * Reads real WordPress post data (not editorial placeholder content). Returns an empty array when
 * the site has no published post yet, so the calling template-part can skip rendering the section
 * entirely rather than showing an empty grid.
 *
 * @param int $limit Maximum number of posts to return.
 * @return array<int, array> List of template-parts/blog-card.php `$args` arrays.
 */
function solar_template_get_blog_preview_posts( int $limit = 3 ): array {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);

	return array_map( 'solar_template_map_post_to_card_args', $posts );
}

/**
 * Returns the number of columns the product catalog grid (archive-product.php) renders.
 *
 * Defaults to 4, matching the design handoff, clamped to the 2–6 range it documents as valid
 * regardless of what a filter returns. Filterable so a future "Products" administration tab
 * (Group 10 of the project roadmap) can expose it as a site owner setting without touching this
 * function.
 *
 * @return int Column count, between 2 and 6 inclusive.
 */
function solar_template_catalog_columns(): int {
	/**
	 * Filters the product catalog grid's column count.
	 *
	 * @param int $columns Column count, expected between 2 and 6.
	 */
	$columns = (int) apply_filters( 'solar_template_catalog_columns', 4 );

	return max( 2, min( 6, $columns ) );
}

/**
 * Returns the product catalog's result count label ("N products available"), with correct
 * singular/plural agreement.
 *
 * @param int $count Number of products currently matching the catalog query.
 * @return string Translated, ready-to-escape label.
 */
function solar_template_catalog_result_count_label( int $count ): string {
	return sprintf(
		/* translators: %d: number of products currently shown in the catalog. */
		_n( '%d product available', '%d products available', $count, 'solar-template' ),
		$count
	);
}

/**
 * Returns the slug of the WooCommerce global attribute taxonomy used as the catalog's "Color"
 * filter.
 *
 * Not hardcoded to `pa_color`: a site owner can name (or already have named) this attribute
 * differently (e.g. `pa_couleur`); a future "Products" administration tab (Group 10 of the
 * project roadmap) is expected to expose this filter as a setting.
 *
 * @return string Taxonomy slug.
 */
function solar_template_catalog_color_attribute_slug(): string {
	/**
	 * Filters the taxonomy slug used as the catalog's "Color" filter.
	 *
	 * @param string $taxonomy Taxonomy slug, e.g. `pa_color`.
	 */
	return (string) apply_filters( 'solar_template_catalog_color_attribute_slug', 'pa_color' );
}

/**
 * Returns the slug of the WooCommerce global attribute taxonomy used as the catalog's "Size"
 * filter. See solar_template_catalog_color_attribute_slug() for why this is filterable rather
 * than hardcoded.
 *
 * @return string Taxonomy slug.
 */
function solar_template_catalog_size_attribute_slug(): string {
	/**
	 * Filters the taxonomy slug used as the catalog's "Size" filter.
	 *
	 * @param string $taxonomy Taxonomy slug, e.g. `pa_size`.
	 */
	return (string) apply_filters( 'solar_template_catalog_size_attribute_slug', 'pa_size' );
}

/**
 * Returns the product categories available as catalog filter options (slug, name, product count).
 *
 * Same graceful-degradation convention as solar_template_get_home_categories(): an empty array
 * when WooCommerce is missing/inactive or the store has no populated category, so the calling
 * template-part can skip rendering this filter group entirely.
 *
 * @return array<int, array{slug: string, name: string, count: int}>
 */
function solar_template_get_catalog_category_options(): array {
	if ( ! solar_template_load_autoloader() || ! \Solar_Template\Support\WooCommerceStatus::is_active() ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return array_map(
		static function ( \WP_Term $term ): array {
			return array(
				'slug'  => $term->slug,
				'name'  => $term->name,
				'count' => (int) $term->count,
			);
		},
		$terms
	);
}

/**
 * Returns the terms of a given attribute taxonomy available as catalog filter options (slug,
 * name), used for both the "Color" and "Size" filters.
 *
 * @param string $taxonomy Attribute taxonomy slug (see solar_template_catalog_color_attribute_slug()/
 *                          solar_template_catalog_size_attribute_slug()).
 * @return array<int, array{slug: string, name: string}> Empty when the taxonomy does not exist
 *                                                         (not registered by the store) or has no
 *                                                         populated term yet.
 */
function solar_template_get_catalog_attribute_options( string $taxonomy ): array {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return array_map(
		static function ( \WP_Term $term ): array {
			return array(
				'slug' => $term->slug,
				'name' => $term->name,
			);
		},
		$terms
	);
}

/**
 * Returns the "Rating" filter's fixed options ("5 stars" down to "1 star"), with correct
 * singular/plural agreement.
 *
 * Deliberately an exact bucket per option (matching a product's own rounded average rating), not
 * a cumulative "N stars & up" threshold: this is exactly how WooCommerce's own native rating
 * filter widget buckets products (via `product_visibility` "rated-N" terms — see
 * solar_template_build_catalog_query_args()), and checking more than one option here simply ORs
 * their buckets together, same as that widget.
 *
 * @return array<int, array{value: int, label: string}>
 */
function solar_template_get_catalog_rating_options(): array {
	$options = array();

	for ( $stars = 5; $stars >= 1; $stars-- ) {
		$options[] = array(
			'value' => $stars,
			'label' => sprintf(
				/* translators: %d: star rating. */
				_n( '%d star', '%d stars', $stars, 'solar-template' ),
				$stars
			),
		);
	}

	return $options;
}

/**
 * Returns the lowest and highest price across published products, used as the "Price" filter's
 * displayed bounds and to clamp any submitted value to a sane range.
 *
 * Reads `_price` directly through `$wpdb` (same convention as
 * Solar_Template\Database\Installer/Solar_Template\Newsletter\SubscriberRepository) rather than a
 * `WP_Query`, since only the two aggregate values are needed, not post objects.
 *
 * @return array{min: float, max: float}
 */
function solar_template_get_catalog_price_bounds(): array {
	global $wpdb;

	$bounds = $wpdb->get_row(
		"SELECT MIN(CAST(pm.meta_value AS DECIMAL(10,2))) AS min_price, MAX(CAST(pm.meta_value AS DECIMAL(10,2))) AS max_price
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_price' AND pm.meta_value != '' AND p.post_type = 'product' AND p.post_status = 'publish'",
		ARRAY_A
	);

	return array(
		'min' => $bounds && null !== $bounds['min_price'] ? (float) $bounds['min_price'] : 0.0,
		'max' => $bounds && null !== $bounds['max_price'] ? (float) $bounds['max_price'] : 0.0,
	);
}

/**
 * Normalizes and validates a raw filter request (from `$_GET` on a plain page load, or from the
 * AJAX filter request's `$_POST`) against the catalog's real filter options, so nothing
 * unsanitized ever reaches a database query: unknown category/color/size slugs are dropped,
 * ratings outside 1–5 are dropped, and price bounds are clamped to the store's real price range.
 *
 * @param array $raw Raw request data (a superglobal-like array).
 * @return array{
 *     category: string[],
 *     color: string[],
 *     size: string[],
 *     rating: int[],
 *     min_price: float|null,
 *     max_price: float|null,
 *     orderby: string,
 * }
 */
function solar_template_sanitize_catalog_filters( array $raw ): array {
	$category_slugs = wp_list_pluck( solar_template_get_catalog_category_options(), 'slug' );
	$color_slugs    = wp_list_pluck( solar_template_get_catalog_attribute_options( solar_template_catalog_color_attribute_slug() ), 'slug' );
	$size_slugs     = wp_list_pluck( solar_template_get_catalog_attribute_options( solar_template_catalog_size_attribute_slug() ), 'slug' );

	$requested_category = array_map( 'sanitize_title', (array) ( $raw['filter_category'] ?? array() ) );
	$requested_color    = array_map( 'sanitize_title', (array) ( $raw['filter_color'] ?? array() ) );
	$requested_size     = array_map( 'sanitize_title', (array) ( $raw['filter_size'] ?? array() ) );
	$requested_rating   = array_map( 'absint', (array) ( $raw['filter_rating'] ?? array() ) );
	$requested_rating   = array_filter(
		$requested_rating,
		static function ( int $value ): bool {
			return $value >= 1 && $value <= 5;
		}
	);

	$price_bounds = solar_template_get_catalog_price_bounds();
	$min_price    = ( isset( $raw['min_price'] ) && '' !== $raw['min_price'] )
		? max( $price_bounds['min'], (float) $raw['min_price'] )
		: null;
	$max_price    = ( isset( $raw['max_price'] ) && '' !== $raw['max_price'] )
		? min( $price_bounds['max'], (float) $raw['max_price'] )
		: null;

	return array(
		'category'  => array_values( array_intersect( $requested_category, $category_slugs ) ),
		'color'     => array_values( array_intersect( $requested_color, $color_slugs ) ),
		'size'      => array_values( array_intersect( $requested_size, $size_slugs ) ),
		'rating'    => array_values( array_unique( $requested_rating ) ),
		'min_price' => $min_price,
		'max_price' => $max_price,
		// Read from its own `catalog_orderby` request key, deliberately not WooCommerce's native
		// `orderby` (`$_GET['orderby']`): that name is also read directly by
		// `WC_Query::get_catalog_ordering_args()` (called from its own `pre_get_posts` handling on
		// every product archive query, independently of anything this theme sets), which registers
		// its own `posts_clauses` ordering callbacks for "price"/"popularity"/"rating" as a side
		// effect — a callback that would silently override this function's own, more reliable
		// `orderby`/`meta_key` handling (see solar_template_catalog_sort_query_args()) whenever the
		// two names collided.
		'orderby'   => solar_template_sanitize_catalog_sort( (string) ( $raw['catalog_orderby'] ?? '' ) ),
	);
}

/**
 * Returns the catalog filter bar's position modifier class.
 *
 * Only `sticky-top` (the design handoff's own layout, applied by
 * template-parts/catalog-filters.php as a `catalog-filters--{position}` class) is styled today; a
 * future "Products" administration tab (Group 10 of the project roadmap) is expected to expose
 * this as a real site owner setting, once an alternative layout (e.g. a sidebar) exists to switch
 * to.
 *
 * @return string Position slug, e.g. `sticky-top`.
 */
function solar_template_catalog_filters_position(): string {
	/**
	 * Filters the catalog filter bar's position.
	 *
	 * @param string $position `sticky-top` by default.
	 */
	return (string) apply_filters( 'solar_template_catalog_filters_position', 'sticky-top' );
}

/**
 * Returns the catalog's sort dropdown options (value + label), in display order. The first option
 * (`menu_order`) is also the default when no valid `orderby` is requested.
 *
 * @return array<int, array{value: string, label: string}>
 */
function solar_template_get_catalog_sort_options(): array {
	return array(
		array(
			'value' => 'menu_order',
			'label' => __( 'Relevance', 'solar-template' ),
		),
		array(
			'value' => 'price-asc',
			'label' => __( 'Price: low to high', 'solar-template' ),
		),
		array(
			'value' => 'price-desc',
			'label' => __( 'Price: high to low', 'solar-template' ),
		),
		array(
			'value' => 'date',
			'label' => __( 'Newest', 'solar-template' ),
		),
		array(
			'value' => 'popularity',
			'label' => __( 'Best sellers', 'solar-template' ),
		),
	);
}

/**
 * Normalizes and validates a raw `orderby` request value against
 * solar_template_get_catalog_sort_options()'s real option values, defaulting to the first one
 * (`menu_order`) for anything else (missing, tampered with, or simply not one of the options).
 *
 * @param string $raw Raw `orderby` request value.
 * @return string A valid sort option value.
 */
function solar_template_sanitize_catalog_sort( string $raw ): string {
	$valid_values = wp_list_pluck( solar_template_get_catalog_sort_options(), 'value' );

	return in_array( $raw, $valid_values, true ) ? $raw : $valid_values[0];
}

/**
 * Maps a validated sort option value (see solar_template_sanitize_catalog_sort()) to the
 * `WP_Query` args that apply it. Deliberately not WooCommerce's own
 * `WC_Query::get_catalog_ordering_args()` for "price"/"popularity": that relies on `posts_clauses`
 * callbacks gated on `$wp_query->is_main_query()`, which — like WooCommerce's native price filter
 * (see solar_template_build_catalog_query_args()) — does not reliably re-engage for the AJAX
 * handler's programmatically-built query. Ordering by a plain, real WooCommerce meta key
 * (`_price`, `total_sales`) via `WP_Query`'s own native `meta_value_num` support needs no such
 * hook, so it behaves identically in both call sites.
 *
 * @param string $sort A valid sort option value.
 * @return array{orderby: string, order: string, meta_key?: string}
 */
function solar_template_catalog_sort_query_args( string $sort ): array {
	switch ( $sort ) {
		case 'price-asc':
			return array(
				'orderby'  => 'meta_value_num',
				'order'    => 'ASC',
				'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- sort dropdown deliberately orders by price meta.
			);

		case 'price-desc':
			return array(
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
				'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- sort dropdown deliberately orders by price meta.
			);

		case 'date':
			return array(
				'orderby' => 'date',
				'order'   => 'DESC',
			);

		case 'popularity':
			return array(
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
				'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- sort dropdown deliberately orders by WooCommerce's own sales count meta.
			);

		default:
			return array(
				'orderby' => 'menu_order title',
				'order'   => 'ASC',
			);
	}
}

/**
 * Reports whether a catalog filter group (as built by template-parts/catalog-filters.php) has at
 * least one active value, regardless of its type (checkbox list or price range).
 *
 * @param array $group One filter group, with an `active` key (array for checkboxes,
 *                       `{min, max}` for the price range).
 * @return bool
 */
function solar_template_catalog_filter_group_is_active( array $group ): bool {
	if ( 'range' === $group['type'] ) {
		return null !== $group['active']['min'] || null !== $group['active']['max'];
	}

	return ! empty( $group['active'] );
}

/**
 * Reads and sanitizes the catalog filters currently active from the request URL (`$_GET`), so a
 * plain page load (no JavaScript, a shared/bookmarked filtered URL, a browser back navigation)
 * renders the filter bar and the results already in sync with it.
 *
 * @return array See solar_template_sanitize_catalog_filters()'s return type.
 */
function solar_template_get_active_catalog_filters(): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter state (not a state-changing request), sanitized/validated below against the store's real filter options.
	return solar_template_sanitize_catalog_filters( wp_unslash( $_GET ) );
}

/**
 * Returns the URL for the current catalog view with the given, already-sanitized filter
 * selection applied as query args (`filter_category`/`min_price`/etc.), replacing whatever
 * filters are currently in the request URL rather than adding to them.
 *
 * @param array       $filters  See solar_template_sanitize_catalog_filters()'s return type.
 * @param string|null $base_url Catalog page URL to build from; the current request URL (correct
 *                                for a plain page load) when null. The AJAX handler passes the
 *                                real catalog page URL explicitly instead, since the current
 *                                request URL there is `admin-ajax.php`, not a page a chip should
 *                                ever link back to.
 * @return string
 */
function solar_template_catalog_filters_url( array $filters, ?string $base_url = null ): string {
	$base_url = solar_template_catalog_clear_filters_url( $base_url );

	$query_args = array();

	foreach ( array( 'category', 'color', 'size', 'rating' ) as $dimension ) {
		if ( ! empty( $filters[ $dimension ] ) ) {
			$query_args[ 'filter_' . $dimension ] = $filters[ $dimension ];
		}
	}

	if ( null !== $filters['min_price'] ) {
		$query_args['min_price'] = $filters['min_price'];
	}

	if ( null !== $filters['max_price'] ) {
		$query_args['max_price'] = $filters['max_price'];
	}

	// Omitted from the URL entirely when it's the default sort (`solar_template_sanitize_catalog_sort('')`'s own fallback), for a clean, sort-less URL until the visitor actually picks one.
	if ( isset( $filters['orderby'] ) && solar_template_sanitize_catalog_sort( '' ) !== $filters['orderby'] ) {
		$query_args['catalog_orderby'] = $filters['orderby'];
	}

	return empty( $query_args ) ? $base_url : add_query_arg( $query_args, $base_url );
}

/**
 * Returns the current catalog view's URL with every filter query arg removed, used as the
 * "Clear all" link and as the base URL for solar_template_catalog_filters_url().
 *
 * @param string|null $base_url See solar_template_catalog_filters_url()'s $base_url parameter.
 * @return string
 */
function solar_template_catalog_clear_filters_url( ?string $base_url = null ): string {
	$keys = array( 'filter_category', 'filter_color', 'filter_size', 'filter_rating', 'min_price', 'max_price' );

	return null !== $base_url ? remove_query_arg( $keys, $base_url ) : remove_query_arg( $keys );
}

/**
 * Returns the URL that removes a single active filter value (one category/color/size/rating
 * choice, or the whole price range as one unit — it has a single chip/removal control), keeping
 * every other currently active filter untouched.
 *
 * @param array       $filters   See solar_template_sanitize_catalog_filters()'s return type.
 * @param string      $dimension One of `category`, `color`, `size`, `rating`, `price`.
 * @param string|int  $value     The value to remove; ignored when $dimension is `price`.
 * @param string|null $base_url  See solar_template_catalog_filters_url()'s $base_url parameter.
 * @return string
 */
function solar_template_catalog_filter_remove_url( array $filters, string $dimension, $value, ?string $base_url = null ): string {
	if ( 'price' === $dimension ) {
		$filters['min_price'] = null;
		$filters['max_price'] = null;
	} elseif ( isset( $filters[ $dimension ] ) && is_array( $filters[ $dimension ] ) ) {
		$filters[ $dimension ] = array_values( array_diff( $filters[ $dimension ], array( $value ) ) );
	}

	return solar_template_catalog_filters_url( $filters, $base_url );
}

/**
 * Formats the "Price" filter's active chip label ("20,00 € – 150,00 €", "From 20,00 €", or
 * "Up to 150,00 €" depending on which bound is set), using WooCommerce's own `wc_price()` for
 * locale-correct formatting.
 *
 * @param float|null $min_price Minimum price, or null when unset.
 * @param float|null $max_price Maximum price, or null when unset.
 * @return string
 */
function solar_template_catalog_price_filter_chip_label( ?float $min_price, ?float $max_price ): string {
	if ( null !== $min_price && null !== $max_price ) {
		return sprintf(
			/* translators: 1: minimum price, 2: maximum price. */
			__( '%1$s – %2$s', 'solar-template' ),
			wp_strip_all_tags( wc_price( $min_price ) ),
			wp_strip_all_tags( wc_price( $max_price ) )
		);
	}

	if ( null !== $min_price ) {
		return sprintf(
			/* translators: %s: minimum price. */
			__( 'From %s', 'solar-template' ),
			wp_strip_all_tags( wc_price( $min_price ) )
		);
	}

	return sprintf(
		/* translators: %s: maximum price. */
		__( 'Up to %s', 'solar-template' ),
		wp_strip_all_tags( wc_price( $max_price ) )
	);
}

/**
 * Returns the catalog's currently active filters as a flat list of removable chips (label +
 * the URL that removes just that one value), for template-parts/catalog-active-filters.php.
 *
 * @param array|null  $filters  Already-sanitized filters to build chips for; reads the current
 *                                request URL (via solar_template_get_active_catalog_filters())
 *                                when null — the AJAX handler passes its own `$_POST`-derived
 *                                filters instead, since they never reach `$_GET`.
 * @param string|null $base_url See solar_template_catalog_filters_url()'s $base_url parameter.
 * @return array<int, array{label: string, url: string}>
 */
function solar_template_get_active_catalog_filter_chips( ?array $filters = null, ?string $base_url = null ): array {
	$filters = $filters ?? solar_template_get_active_catalog_filters();
	$chips   = array();

	$category_names = wp_list_pluck( solar_template_get_catalog_category_options(), 'name', 'slug' );

	foreach ( $filters['category'] as $slug ) {
		if ( isset( $category_names[ $slug ] ) ) {
			$chips[] = array(
				'label' => $category_names[ $slug ],
				'url'   => solar_template_catalog_filter_remove_url( $filters, 'category', $slug, $base_url ),
			);
		}
	}

	$color_names = wp_list_pluck( solar_template_get_catalog_attribute_options( solar_template_catalog_color_attribute_slug() ), 'name', 'slug' );

	foreach ( $filters['color'] as $slug ) {
		if ( isset( $color_names[ $slug ] ) ) {
			$chips[] = array(
				'label' => $color_names[ $slug ],
				'url'   => solar_template_catalog_filter_remove_url( $filters, 'color', $slug, $base_url ),
			);
		}
	}

	$size_names = wp_list_pluck( solar_template_get_catalog_attribute_options( solar_template_catalog_size_attribute_slug() ), 'name', 'slug' );

	foreach ( $filters['size'] as $slug ) {
		if ( isset( $size_names[ $slug ] ) ) {
			$chips[] = array(
				'label' => $size_names[ $slug ],
				'url'   => solar_template_catalog_filter_remove_url( $filters, 'size', $slug, $base_url ),
			);
		}
	}

	$rating_labels = wp_list_pluck( solar_template_get_catalog_rating_options(), 'label', 'value' );

	foreach ( $filters['rating'] as $stars ) {
		if ( isset( $rating_labels[ $stars ] ) ) {
			$chips[] = array(
				'label' => $rating_labels[ $stars ],
				'url'   => solar_template_catalog_filter_remove_url( $filters, 'rating', $stars, $base_url ),
			);
		}
	}

	if ( null !== $filters['min_price'] || null !== $filters['max_price'] ) {
		$chips[] = array(
			'label' => solar_template_catalog_price_filter_chip_label( $filters['min_price'], $filters['max_price'] ),
			'url'   => solar_template_catalog_filter_remove_url( $filters, 'price', '', $base_url ),
		);
	}

	return $chips;
}

/**
 * Builds `WP_Query` `tax_query`/`meta_query`/ordering arguments for the given, already-sanitized
 * filter selection. Shared by solar_template_apply_catalog_filters_to_main_query() (the initial
 * page load) and solar_template_handle_catalog_filter_request() (the AJAX re-render), so both
 * always filter/sort identically — this is deliberately self-contained rather than relying on
 * WooCommerce's own `$_GET`-reading native price/rating filtering (`price_filter_post_clauses()`/
 * `rating_filter` handling in `WC_Query::get_tax_query()`) or its `posts_clauses`-based catalog
 * ordering (`WC_Query::get_catalog_ordering_args()`'s "price"/"popularity" cases): all three are
 * wired deep into `WC_Query::pre_get_posts()`'s own detection of "is this really a product archive
 * query" and do not reliably engage for a query built programmatically the way the AJAX
 * re-render's is, whereas plain `meta_query`/`tax_query`/`orderby` args on the query itself always
 * apply regardless of how the query was constructed. The "Rating" filter still reuses
 * WooCommerce's real `product_visibility` "rated-N" terms (`wc_get_product_visibility_term_ids()`)
 * — the same data its own widget filters on — rather than a `_wc_average_rating` meta comparison,
 * which would not reflect how WooCommerce itself buckets/caches ratings.
 *
 * @param array $filters See solar_template_sanitize_catalog_filters()'s return type.
 * @return array{tax_query?: array, meta_query?: array, orderby: string, order: string, meta_key?: string}
 */
function solar_template_build_catalog_query_args( array $filters ): array {
	$args = array();

	$tax_query = array();

	if ( ! empty( $filters['category'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $filters['category'],
		);
	}

	$color_taxonomy = solar_template_catalog_color_attribute_slug();

	if ( ! empty( $filters['color'] ) && taxonomy_exists( $color_taxonomy ) ) {
		$tax_query[] = array(
			'taxonomy' => $color_taxonomy,
			'field'    => 'slug',
			'terms'    => $filters['color'],
		);
	}

	$size_taxonomy = solar_template_catalog_size_attribute_slug();

	if ( ! empty( $filters['size'] ) && taxonomy_exists( $size_taxonomy ) ) {
		$tax_query[] = array(
			'taxonomy' => $size_taxonomy,
			'field'    => 'slug',
			'terms'    => $filters['size'],
		);
	}

	if ( ! empty( $filters['rating'] ) && function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$visibility_term_ids = wc_get_product_visibility_term_ids();
		$rating_term_ids     = array();

		foreach ( $filters['rating'] as $stars ) {
			if ( isset( $visibility_term_ids[ 'rated-' . $stars ] ) ) {
				$rating_term_ids[] = $visibility_term_ids[ 'rated-' . $stars ];
			}
		}

		if ( ! empty( $rating_term_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $rating_term_ids,
			);
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- filter bar deliberately queries by taxonomy.
	}

	if ( null !== $filters['min_price'] || null !== $filters['max_price'] ) {
		$price_query = array(
			'key'  => '_price',
			'type' => 'NUMERIC',
		);

		if ( null !== $filters['min_price'] && null !== $filters['max_price'] ) {
			$price_query['value']   = array( $filters['min_price'], $filters['max_price'] );
			$price_query['compare'] = 'BETWEEN';
		} elseif ( null !== $filters['min_price'] ) {
			$price_query['value']   = $filters['min_price'];
			$price_query['compare'] = '>=';
		} else {
			$price_query['value']   = $filters['max_price'];
			$price_query['compare'] = '<=';
		}

		$args['meta_query'] = array( $price_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- filter bar deliberately queries by price meta.
	}

	return array_merge( $args, solar_template_catalog_sort_query_args( $filters['orderby'] ?? '' ) );
}

/**
 * Merges the currently active catalog filters and sort selection into the shop/product-taxonomy
 * page's main query, so a plain page load (no JavaScript, a shared/bookmarked filtered URL)
 * already returns filtered, correctly-ordered results — the AJAX handler only has to re-render
 * the same query for an in-page update.
 *
 * When a "Category" filter is active, it fully replaces the archive's own taxonomy scope (rather
 * than narrowing it further): picking a category from the filter bar can move a visitor already
 * on one category's archive to a different category entirely, exactly like the design handoff's
 * quick category links would have (see archive-product.php).
 *
 * @param \WP_Query $query The query being modified.
 * @return void
 */
function solar_template_apply_catalog_filters_to_main_query( \WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}

	$filters = solar_template_get_active_catalog_filters();
	$args    = solar_template_build_catalog_query_args( $filters );

	if ( isset( $args['tax_query'] ) ) {
		if ( ! empty( $filters['category'] ) ) {
			$query->set( 'product_cat', '' );
			$query->set( 'product_tag', '' );
		}

		// `get( 'tax_query', array() )` explicitly defaults to an array: `WP_Query::get()` itself
		// defaults to '' for an unset query var, and `(array) ''` produces `array( '' )` rather
		// than `array()`, which corrupts `parse_tax_query()`'s expected clause structure.
		$query->set( 'tax_query', array_merge( (array) $query->get( 'tax_query', array() ), $args['tax_query'] ) );
	}

	if ( isset( $args['meta_query'] ) ) {
		$query->set( 'meta_query', array_merge( (array) $query->get( 'meta_query', array() ), $args['meta_query'] ) );
	}

	$query->set( 'orderby', $args['orderby'] );
	$query->set( 'order', $args['order'] );
	$query->set( 'meta_key', $args['meta_key'] ?? '' );
}
add_action( 'pre_get_posts', 'solar_template_apply_catalog_filters_to_main_query' );

/**
 * Handles the catalog filter bar's AJAX request (`solar_template_catalog_filter` action):
 * validates the nonce, sanitizes the submitted filters, runs them as the page's main product
 * query (so WooCommerce's own visibility/stock/ordering logic — only ever applied to the main
 * query — still applies, exactly as on a plain page load), and responds with the re-rendered
 * results markup (template-parts/catalog-results.php) the front-end
 * (assets/js/catalog.js) swaps into the page.
 *
 * @return void
 */
function solar_template_handle_catalog_filter_request(): void {
	check_ajax_referer( 'solar_template_catalog_filter', 'nonce' );

	$filters  = solar_template_sanitize_catalog_filters( wp_unslash( $_POST ) );
	$paged    = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
	$page_url = isset( $_POST['pageUrl'] ) ? esc_url_raw( wp_unslash( $_POST['pageUrl'] ) ) : '';

	$args = array_merge(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
		),
		solar_template_build_catalog_query_args( $filters )
	);

	global $wp_query, $wp_the_query;

	$previous_query      = $wp_query;
	$previous_main_query = $wp_the_query;

	$wp_the_query = new \WP_Query(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- temporarily swapped so WooCommerce's own main-query-only filtering (visibility/stock) applies, restored right below.
	$wp_query     = $wp_the_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$wp_the_query->query( $args );

	ob_start();
	get_template_part( 'template-parts/catalog-results' );
	$results_html = ob_get_clean();

	ob_start();
	get_template_part(
		'template-parts/catalog-active-filters',
		null,
		array(
			'filters'  => $filters,
			'base_url' => '' !== $page_url ? $page_url : null,
		)
	);
	$active_filters_html = ob_get_clean();

	$wp_query     = $previous_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$wp_the_query = $previous_main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	wp_send_json_success(
		array(
			'html'              => $results_html,
			'activeFiltersHtml' => $active_filters_html,
		)
	);
}
add_action( 'wp_ajax_solar_template_catalog_filter', 'solar_template_handle_catalog_filter_request' );
add_action( 'wp_ajax_nopriv_solar_template_catalog_filter', 'solar_template_handle_catalog_filter_request' );

/**
 * Enqueues the catalog filter bar's own script and localizes the AJAX endpoint/nonce it needs, on
 * the shop page and product category/tag archives only.
 *
 * @return void
 */
function solar_template_enqueue_catalog_script(): void {
	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}

	wp_localize_script(
		'solar-template',
		'solarTemplateCatalog',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'solar_template_catalog_filter' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'solar_template_enqueue_catalog_script', 20 );
