<?php
/**
 * Created: 2026-09-25 14:42 CEST
 * Role: Catalog filter/sort selection handling (Solar_Template\Catalog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Validate a raw filter/sort request against the catalog's real options, read it from
 *          the request URL, build the filter/removal/clear URLs and active filter chips, and
 *          convert a validated selection into WP_Query arguments.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates, reads, and converts the catalog's active filter/sort selection.
 */
final class CatalogFilters {

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
	public static function sanitize( array $raw ): array {
		$category_slugs = wp_list_pluck( CatalogOptions::category_options(), 'slug' );
		$color_slugs    = wp_list_pluck( CatalogOptions::attribute_options( CatalogOptions::color_attribute_slug() ), 'slug' );
		$size_slugs     = wp_list_pluck( CatalogOptions::attribute_options( CatalogOptions::size_attribute_slug() ), 'slug' );

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

		$price_bounds = CatalogOptions::price_bounds();
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
			// effect — a callback that would silently override this method's own, more reliable
			// `orderby`/`meta_key` handling (see self::sort_query_args()) whenever the two names
			// collided.
			'orderby'   => self::sanitize_sort( (string) ( $raw['catalog_orderby'] ?? '' ) ),
		);
	}

	/**
	 * Normalizes and validates a raw `orderby` request value against
	 * CatalogOptions::sort_options()'s real option values, defaulting to the first one
	 * (`menu_order`) for anything else (missing, tampered with, or simply not one of the options).
	 *
	 * @param string $raw Raw `orderby` request value.
	 * @return string A valid sort option value.
	 */
	public static function sanitize_sort( string $raw ): string {
		$valid_values = wp_list_pluck( CatalogOptions::sort_options(), 'value' );

		return in_array( $raw, $valid_values, true ) ? $raw : $valid_values[0];
	}

	/**
	 * Maps a validated sort option value (see self::sanitize_sort()) to the `WP_Query` args that
	 * apply it. Deliberately not WooCommerce's own `WC_Query::get_catalog_ordering_args()` for
	 * "price"/"popularity": that relies on `posts_clauses` callbacks gated on
	 * `$wp_query->is_main_query()`, which — like WooCommerce's native price filter (see
	 * self::query_args()) — does not reliably re-engage for the AJAX handler's programmatically-built
	 * query. Ordering by a plain, real WooCommerce meta key (`_price`, `total_sales`) via `WP_Query`'s
	 * own native `meta_value_num` support needs no such hook, so it behaves identically in both call
	 * sites.
	 *
	 * @param string $sort A valid sort option value.
	 * @return array{orderby: string, order: string, meta_key?: string}
	 */
	public static function sort_query_args( string $sort ): array {
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
	public static function group_is_active( array $group ): bool {
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
	 * @return array See self::sanitize()'s return type.
	 */
	public static function active(): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter state (not a state-changing request), sanitized/validated below against the store's real filter options.
		return self::sanitize( wp_unslash( $_GET ) );
	}

	/**
	 * Returns the URL for the current catalog view with the given, already-sanitized filter
	 * selection applied as query args (`filter_category`/`min_price`/etc.), replacing whatever
	 * filters are currently in the request URL rather than adding to them.
	 *
	 * @param array       $filters  See self::sanitize()'s return type.
	 * @param string|null $base_url Catalog page URL to build from; the current request URL (correct
	 *                                for a plain page load) when null. The AJAX handler passes the
	 *                                real catalog page URL explicitly instead, since the current
	 *                                request URL there is `admin-ajax.php`, not a page a chip should
	 *                                ever link back to.
	 * @return string
	 */
	public static function url( array $filters, ?string $base_url = null ): string {
		$base_url = self::clear_url( $base_url );

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

		// Omitted from the URL entirely when it's the default sort (self::sanitize_sort('')'s own fallback), for a clean, sort-less URL until the visitor actually picks one.
		if ( isset( $filters['orderby'] ) && self::sanitize_sort( '' ) !== $filters['orderby'] ) {
			$query_args['catalog_orderby'] = $filters['orderby'];
		}

		return empty( $query_args ) ? $base_url : add_query_arg( $query_args, $base_url );
	}

	/**
	 * Returns the current catalog view's URL with every filter query arg removed, used as the
	 * "Clear all" link and as the base URL for self::url().
	 *
	 * @param string|null $base_url See self::url()'s $base_url parameter.
	 * @return string
	 */
	public static function clear_url( ?string $base_url = null ): string {
		$keys = array( 'filter_category', 'filter_color', 'filter_size', 'filter_rating', 'min_price', 'max_price' );

		return null !== $base_url ? remove_query_arg( $keys, $base_url ) : remove_query_arg( $keys );
	}

	/**
	 * Returns the URL that removes a single active filter value (one category/color/size/rating
	 * choice, or the whole price range as one unit — it has a single chip/removal control), keeping
	 * every other currently active filter untouched.
	 *
	 * @param array       $filters   See self::sanitize()'s return type.
	 * @param string      $dimension One of `category`, `color`, `size`, `rating`, `price`.
	 * @param string|int  $value     The value to remove; ignored when $dimension is `price`.
	 * @param string|null $base_url  See self::url()'s $base_url parameter.
	 * @return string
	 */
	public static function remove_url( array $filters, string $dimension, $value, ?string $base_url = null ): string {
		if ( 'price' === $dimension ) {
			$filters['min_price'] = null;
			$filters['max_price'] = null;
		} elseif ( isset( $filters[ $dimension ] ) && is_array( $filters[ $dimension ] ) ) {
			$filters[ $dimension ] = array_values( array_diff( $filters[ $dimension ], array( $value ) ) );
		}

		return self::url( $filters, $base_url );
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
	public static function price_chip_label( ?float $min_price, ?float $max_price ): string {
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
	 *                                request URL (via self::active()) when null — the AJAX handler
	 *                                passes its own `$_POST`-derived filters instead, since they
	 *                                never reach `$_GET`.
	 * @param string|null $base_url See self::url()'s $base_url parameter.
	 * @return array<int, array{label: string, url: string}>
	 */
	public static function active_chips( ?array $filters = null, ?string $base_url = null ): array {
		$filters = $filters ?? self::active();
		$chips   = array();

		$category_names = wp_list_pluck( CatalogOptions::category_options(), 'name', 'slug' );

		foreach ( $filters['category'] as $slug ) {
			if ( isset( $category_names[ $slug ] ) ) {
				$chips[] = array(
					'label' => $category_names[ $slug ],
					'url'   => self::remove_url( $filters, 'category', $slug, $base_url ),
				);
			}
		}

		$color_names = wp_list_pluck( CatalogOptions::attribute_options( CatalogOptions::color_attribute_slug() ), 'name', 'slug' );

		foreach ( $filters['color'] as $slug ) {
			if ( isset( $color_names[ $slug ] ) ) {
				$chips[] = array(
					'label' => $color_names[ $slug ],
					'url'   => self::remove_url( $filters, 'color', $slug, $base_url ),
				);
			}
		}

		$size_names = wp_list_pluck( CatalogOptions::attribute_options( CatalogOptions::size_attribute_slug() ), 'name', 'slug' );

		foreach ( $filters['size'] as $slug ) {
			if ( isset( $size_names[ $slug ] ) ) {
				$chips[] = array(
					'label' => $size_names[ $slug ],
					'url'   => self::remove_url( $filters, 'size', $slug, $base_url ),
				);
			}
		}

		$rating_labels = wp_list_pluck( CatalogOptions::rating_options(), 'label', 'value' );

		foreach ( $filters['rating'] as $stars ) {
			if ( isset( $rating_labels[ $stars ] ) ) {
				$chips[] = array(
					'label' => $rating_labels[ $stars ],
					'url'   => self::remove_url( $filters, 'rating', $stars, $base_url ),
				);
			}
		}

		if ( null !== $filters['min_price'] || null !== $filters['max_price'] ) {
			$chips[] = array(
				'label' => self::price_chip_label( $filters['min_price'], $filters['max_price'] ),
				'url'   => self::remove_url( $filters, 'price', '', $base_url ),
			);
		}

		return $chips;
	}

	/**
	 * Builds `WP_Query` `tax_query`/`meta_query`/ordering arguments for the given, already-sanitized
	 * filter selection. Shared by CatalogController::apply_filters_to_main_query() (the initial page
	 * load) and CatalogController::handle_filter_request() (the AJAX re-render), so both always
	 * filter/sort identically — this is deliberately self-contained rather than relying on
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
	 * @param array $filters See self::sanitize()'s return type.
	 * @return array{tax_query?: array, meta_query?: array, orderby: string, order: string, meta_key?: string}
	 */
	public static function query_args( array $filters ): array {
		$args = array();

		$tax_query = array();

		if ( ! empty( $filters['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $filters['category'],
			);
		}

		$color_taxonomy = CatalogOptions::color_attribute_slug();

		if ( ! empty( $filters['color'] ) && taxonomy_exists( $color_taxonomy ) ) {
			$tax_query[] = array(
				'taxonomy' => $color_taxonomy,
				'field'    => 'slug',
				'terms'    => $filters['color'],
			);
		}

		$size_taxonomy = CatalogOptions::size_attribute_slug();

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

		return array_merge( $args, self::sort_query_args( $filters['orderby'] ?? '' ) );
	}
}
