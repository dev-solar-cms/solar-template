<?php
/**
 * Created: 2026-09-25 15:41 CEST
 * Role: Front page featured products section (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the "Featured products" section header and the real WooCommerce products
 *          marked "Featured" from the product edit screen, mapped for the product card
 *          template-part.
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

use Solar_Template\Catalog\ProductCardMapper;
use Solar_Template\Support\StoreLinks;
use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page "Featured products" section header + product query.
 */
final class FeaturedProducts {

	/**
	 * Returns the front page's "Featured products" section header (eyebrow label, heading, "view
	 * all" link) — everything except the product data itself, see self::products().
	 *
	 * @return array{eyebrow: string, heading: string, view_all: array{label: string, url: string}}
	 */
	public static function heading(): array {
		$defaults = array(
			'eyebrow'  => __( 'Selection', 'solar-template' ),
			'heading'  => __( 'Favorites', 'solar-template' ),
			'view_all' => array(
				'label' => __( 'View all →', 'solar-template' ),
				'url'   => StoreLinks::shop_url(),
			),
		);

		/**
		 * Filters the front page's "Featured products" section header.
		 *
		 * @param array $config See self::heading()'s return type.
		 */
		return apply_filters( 'solar_template_featured_products_heading', $defaults );
	}

	/**
	 * Returns the front page's featured products, mapped for template-parts/product-card.php.
	 *
	 * Reads real WooCommerce data (products marked "Featured" from the product edit screen) —
	 * unlike self::heading()/Hero::config(), this is not editorial placeholder content. Returns an
	 * empty array when WooCommerce is missing/inactive, or when no product is currently marked as
	 * featured, so the calling template-part can skip rendering the section entirely rather than
	 * showing an empty grid.
	 *
	 * @param int $limit Maximum number of products to return.
	 * @return array<int, array> List of template-parts/product-card.php `$args` arrays.
	 */
	public static function products( int $limit = 4 ): array {
		if ( ! WooCommerceStatus::is_active() || ! function_exists( 'wc_get_products' ) ) {
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

		return array_map( array( ProductCardMapper::class, 'map' ), $products );
	}
}
