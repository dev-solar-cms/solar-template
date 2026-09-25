<?php
/**
 * Created: 2026-09-25 14:42 CEST
 * Role: Read-only, selection-independent catalog descriptors (Solar_Template\Catalog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Centralize the catalog's static/derived option data (grid columns, per-page count,
 *          attribute slugs, filter/sort option lists, price bounds, filter bar position, result
 *          count label) so CatalogFilters/CatalogController can read it without depending on
 *          procedural functions in functions.php.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Catalog;

use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only catalog option/descriptor lookups, independent of any active filter selection.
 */
final class CatalogOptions {

	/**
	 * Returns the number of columns the product catalog grid (archive-product.php) renders.
	 *
	 * Defaults to 4, matching the design handoff, clamped to the 2–6 range it documents as valid
	 * regardless of what a filter returns. Filterable so a future "Products" administration tab
	 * (Group 10 of the project roadmap) can expose it as a site owner setting without touching this
	 * method.
	 *
	 * @return int Column count, between 2 and 6 inclusive.
	 */
	public static function columns(): int {
		/**
		 * Filters the product catalog grid's column count.
		 *
		 * @param int $columns Column count, expected between 2 and 6.
		 */
		$columns = (int) apply_filters( 'solar_template_catalog_columns', 4 );

		return max( 2, min( 6, $columns ) );
	}

	/**
	 * Returns the number of products the catalog shows per page, explicitly, rather than leaving it
	 * to be set as a side effect of WooCommerce's own `WC_Query::product_query()` (hooked on
	 * `pre_get_posts`, applying its own `loop_shop_per_page`-filtered default): that side effect does
	 * not reliably apply to the AJAX handler's programmatically-built query the way it does the real
	 * main query (see CatalogFilters::query_args()'s docblock for the same class of issue with
	 * ordering), which would silently mismatch the "load more" page size between a plain page load
	 * and the next page fetched via AJAX, duplicating/skipping products between the two. Reuses
	 * WooCommerce's own filter/defaults, just called directly instead of relying on that hook.
	 *
	 * @return int
	 */
	public static function products_per_page(): int {
		return (int) apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
	}

	/**
	 * Returns the product catalog's result count label ("N products available"), with correct
	 * singular/plural agreement.
	 *
	 * @param int $count Number of products currently matching the catalog query.
	 * @return string Translated, ready-to-escape label.
	 */
	public static function result_count_label( int $count ): string {
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
	public static function color_attribute_slug(): string {
		/**
		 * Filters the taxonomy slug used as the catalog's "Color" filter.
		 *
		 * @param string $taxonomy Taxonomy slug, e.g. `pa_color`.
		 */
		return (string) apply_filters( 'solar_template_catalog_color_attribute_slug', 'pa_color' );
	}

	/**
	 * Returns the slug of the WooCommerce global attribute taxonomy used as the catalog's "Size"
	 * filter. See self::color_attribute_slug() for why this is filterable rather than hardcoded.
	 *
	 * @return string Taxonomy slug.
	 */
	public static function size_attribute_slug(): string {
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
	 * Same graceful-degradation convention as Solar_Template\FrontPage\Categories::categories(): an empty array
	 * when WooCommerce is missing/inactive or the store has no populated category, so the calling
	 * template-part can skip rendering this filter group entirely.
	 *
	 * @return array<int, array{slug: string, name: string, count: int}>
	 */
	public static function category_options(): array {
		if ( ! WooCommerceStatus::is_active() ) {
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
	 * @param string $taxonomy Attribute taxonomy slug (see self::color_attribute_slug()/
	 *                          self::size_attribute_slug()).
	 * @return array<int, array{slug: string, name: string}> Empty when the taxonomy does not exist
	 *                                                         (not registered by the store) or has no
	 *                                                         populated term yet.
	 */
	public static function attribute_options( string $taxonomy ): array {
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
	 * CatalogFilters::query_args()), and checking more than one option here simply ORs their
	 * buckets together, same as that widget.
	 *
	 * @return array<int, array{value: int, label: string}>
	 */
	public static function rating_options(): array {
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
	public static function price_bounds(): array {
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
	public static function filters_position(): string {
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
	public static function sort_options(): array {
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
}
