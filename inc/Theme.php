<?php
/**
 * Created: 2026-09-25 16:00 CEST
 * Role: Theme bootstrap orchestrator (Solar_Template).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The single place every WordPress hook the theme registers is wired up, pointing
 *          directly at the static methods of the classes under inc/ that actually implement each
 *          feature. functions.php's only job is to load the Composer autoloader and call
 *          self::boot() once.
 *
 * @package Solar_Template
 */

namespace Solar_Template;

use Solar_Template\Admin\Notices;
use Solar_Template\Catalog\CatalogController;
use Solar_Template\Checkout\CheckoutController;
use Solar_Template\Checkout\CheckoutFieldsLayout;
use Solar_Template\Database\Installer;
use Solar_Template\Header\Cart;
use Solar_Template\Header\Nav;
use Solar_Template\Newsletter\NewsletterController;
use Solar_Template\Product\EngravingAdminFields;
use Solar_Template\Product\EngravingCart;
use Solar_Template\Product\ProductController;
use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers every WordPress hook the theme depends on.
 */
final class Theme {

	/**
	 * Registers the theme's WordPress hooks. Called once from functions.php.
	 *
	 * @return void
	 */
	public static function boot(): void {
		add_action( 'after_setup_theme', array( self::class, 'setup' ) );
		add_action( 'after_switch_theme', array( Installer::class, 'install' ) );
		add_action( 'admin_notices', array( Notices::class, 'woocommerce_missing' ) );

		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( Cart::class, 'enqueue_cart_fragments' ), 20 );
		add_action( 'wp_enqueue_scripts', array( NewsletterController::class, 'enqueue_script' ), 20 );
		add_action( 'wp_enqueue_scripts', array( CatalogController::class, 'enqueue_script' ), 20 );
		add_action( 'wp_enqueue_scripts', array( ProductController::class, 'enqueue_script' ), 20 );

		add_filter( 'woocommerce_add_to_cart_fragments', array( Cart::class, 'cart_fragments' ) );
		add_filter( 'nav_menu_css_class', array( Nav::class, 'primary_nav_item_classes' ), 10, 3 );
		add_filter( 'nav_menu_link_attributes', array( Nav::class, 'primary_nav_link_attributes' ), 10, 3 );

		add_action( 'pre_get_posts', array( CatalogController::class, 'apply_filters_to_main_query' ) );

		add_filter( 'template_include', array( CheckoutController::class, 'template_include' ) );
		add_action( 'wp', array( CheckoutController::class, 'detach_default_checkout_hooks' ) );
		add_filter( 'woocommerce_checkout_fields', array( CheckoutFieldsLayout::class, 'reorder_address_fields' ) );

		add_action( 'wp_ajax_solar_template_newsletter_subscribe', array( NewsletterController::class, 'handle_subscription' ) );
		add_action( 'wp_ajax_nopriv_solar_template_newsletter_subscribe', array( NewsletterController::class, 'handle_subscription' ) );

		add_action( 'wp_ajax_solar_template_catalog_filter', array( CatalogController::class, 'handle_filter_request' ) );
		add_action( 'wp_ajax_nopriv_solar_template_catalog_filter', array( CatalogController::class, 'handle_filter_request' ) );

		add_action( 'woocommerce_product_options_general_product_data', array( EngravingAdminFields::class, 'render' ) );
		add_action( 'woocommerce_process_product_meta', array( EngravingAdminFields::class, 'save' ) );

		add_filter( 'woocommerce_add_cart_item_data', array( EngravingCart::class, 'add_cart_item_data' ), 10, 3 );
		add_action( 'woocommerce_before_calculate_totals', array( EngravingCart::class, 'adjust_cart_item_price' ) );
		add_filter( 'woocommerce_get_item_data', array( EngravingCart::class, 'display_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( EngravingCart::class, 'add_order_item_meta' ), 10, 3 );
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
	public static function setup(): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		load_theme_textdomain( 'solar-template', get_template_directory() . '/languages' );

		register_nav_menus(
			array(
				'primary' => __( 'Primary Navigation', 'solar-template' ),
			)
		);

		if ( WooCommerceStatus::is_active() ) {
			add_theme_support( 'woocommerce' );
		}
	}

	/**
	 * Enqueues the theme's font and the compiled front-end assets built by the Vite pipeline
	 * (`npm run build`/`npm run dev`).
	 *
	 * The Google Fonts stylesheet has no local fallback: it is a small external request, accepted as-
	 * is per the design handoff. The compiled CSS/JS files are always present in a theme release
	 * (built by `npm run build` before shipping, not a step a site owner runs themselves), so they
	 * are enqueued unconditionally. The query-string version on those two is the file's own
	 * modification time, so browsers pick up a rebuilt asset immediately without any manual
	 * cache-busting.
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		wp_enqueue_style(
			'solar-template-fonts',
			'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
			array(),
			wp_get_theme( get_template() )->get( 'Version' )
		);

		$dist_url = get_template_directory_uri() . '/assets/dist';
		$dist_dir = get_template_directory() . '/assets/dist';

		wp_enqueue_style( 'solar-template', "{$dist_url}/main.css", array(), filemtime( "{$dist_dir}/main.css" ) );
		wp_enqueue_script( 'solar-template', "{$dist_url}/main.js", array(), filemtime( "{$dist_dir}/main.js" ), true );
	}
}
