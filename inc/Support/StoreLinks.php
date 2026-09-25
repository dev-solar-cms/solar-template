<?php
/**
 * Created: 2026-09-25 15:00 CEST
 * Role: Shared WooCommerce/page link helpers (Solar_Template\Support).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Centralize the shop URL and "page by slug" permalink lookups shared by the header
 *          navigation, mega menu, footer, and several front page sections, so they all resolve a
 *          "closest existing page" link the same way without duplicating this logic.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the WooCommerce shop URL and generic page-by-slug permalinks.
 */
final class StoreLinks {

	/**
	 * Returns the WooCommerce shop page URL, or the site's front page when WooCommerce is missing/
	 * inactive.
	 *
	 * @return string
	 */
	public static function shop_url(): string {
		if ( WooCommerceStatus::is_active() && function_exists( 'wc_get_page_permalink' ) ) {
			return wc_get_page_permalink( 'shop' );
		}

		return home_url( '/' );
	}

	/**
	 * Returns the permalink of the page whose slug is $slug, or $fallback (the site's front page by
	 * default) when no such page exists yet.
	 *
	 * @param string $slug     Page slug to look up.
	 * @param string $fallback URL returned when no page with this slug exists; the site's front page
	 *                         when left empty.
	 * @return string
	 */
	public static function page_url_by_slug( string $slug, string $fallback = '' ): string {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			return get_permalink( $page );
		}

		return '' !== $fallback ? $fallback : home_url( '/' );
	}
}
