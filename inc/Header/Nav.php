<?php
/**
 * Created: 2026-09-25 15:12 CEST
 * Role: Primary navigation logic (Solar_Template\Header).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the default primary navigation items (used when no menu is assigned to the
 *          "primary" location), the fallback markup renderer passed as `wp_nav_menu()`'s
 *          `fallback_cb`, and the filters that keep a real assigned menu's classes identical to
 *          the fallback's.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Header;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Primary navigation: default items, current-item detection, fallback renderer, menu-item filters.
 */
final class Nav {

	/**
	 * Returns the default primary navigation items, used when no menu is assigned to the
	 * "primary" location from Appearance > Menus.
	 *
	 * Every item points at the closest existing equivalent already available in a stock
	 * WordPress/WooCommerce install (shop page, posts page, a "contact" page if one exists) rather
	 * than a dedicated archive/promotion view; a future administration screen or a real menu
	 * assignment is expected to refine these.
	 *
	 * @return array<int, array{slug: string, label: string, url: string, has_mega_menu?: bool}>
	 */
	public static function default_items(): array {
		$shop_url = StoreLinks::shop_url();

		$blog_page_id = (int) get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

		$contact_url = StoreLinks::page_url_by_slug( 'contact' );

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
		 * self::render_fallback()).
		 *
		 * @param array $items Navigation items, see self::default_items()'s return type.
		 */
		return apply_filters( 'solar_template_primary_nav_items', $items );
	}

	/**
	 * Reports whether the given default navigation item slug matches the page currently viewed.
	 *
	 * @param string $slug One of self::default_items()'s item slugs.
	 * @return bool True when this item should be marked as the current one.
	 */
	public static function is_current_item( string $slug ): bool {
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
	public static function render_fallback(): void {
		echo '<ul class="site-header__menu">';

		foreach ( self::default_items() as $item ) {
			$classes = array( 'site-header__menu-item' );

			if ( ! empty( $item['has_mega_menu'] ) ) {
				$classes[] = 'has-mega-menu';
			}

			if ( self::is_current_item( $item['slug'] ) ) {
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
	 * same `site-header__menu-item` class self::render_fallback() uses, so header styling and JS
	 * behaviour (see assets/js/header.js) work identically whether an administrator has assigned a
	 * menu there or not. An administrator marks an item for the mega menu the same way the fallback
	 * does: by adding a `has-mega-menu` CSS class to it from the Menus screen.
	 *
	 * @param string[]  $classes Existing classes for this menu item's `<li>`.
	 * @param \WP_Post  $item    Menu item object (untyped to match `Walker_Nav_Menu`'s own filter).
	 * @param \stdClass $args    `wp_nav_menu()` arguments, including `theme_location`.
	 * @return string[] Classes with `site-header__menu-item` added, or $classes unchanged.
	 */
	public static function primary_nav_item_classes( array $classes, $item, $args ): array {
		if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
			return $classes;
		}

		$classes[] = 'site-header__menu-item';

		return $classes;
	}

	/**
	 * Ensures a real menu item's `<a>` carries the same `site-header__menu-link` class the fallback
	 * uses, for the same parity reason as self::primary_nav_item_classes() above.
	 *
	 * @param array     $atts Existing HTML attributes for this menu item's `<a>`.
	 * @param \WP_Post  $item Menu item object.
	 * @param \stdClass $args `wp_nav_menu()` arguments, including `theme_location`.
	 * @return array Attributes with `site-header__menu-link` appended to `class`.
	 */
	public static function primary_nav_link_attributes( array $atts, $item, $args ): array {
		if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
			return $atts;
		}

		$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' site-header__menu-link' );

		return $atts;
	}
}
