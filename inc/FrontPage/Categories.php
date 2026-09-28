<?php
/**
 * Created: 2026-09-25 15:42 CEST
 * Role: Front page categories section (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the "Categories" section header and the top-level WooCommerce product
 *          categories shown on the front page.
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

use Solar_Template\Contracts\CacheInterface;
use Solar_Template\Support\TransientCache;
use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page "Categories" section header + category query.
 */
final class Categories {

	/**
	 * Returns the front page's "Categories" section header (eyebrow label, heading).
	 *
	 * @return array{eyebrow: string, heading: string}
	 */
	public static function heading(): array {
		$defaults = array(
			'eyebrow' => __( 'Collections', 'solar-template' ),
			'heading' => __( 'Explore our worlds', 'solar-template' ),
		);

		/**
		 * Filters the front page's "Categories" section header.
		 *
		 * @param array $config See self::heading()'s return type.
		 */
		return apply_filters( 'solar_template_categories_heading', $defaults );
	}

	/**
	 * Returns the top-level WooCommerce product categories shown on the front page's "Categories"
	 * section, mapped to {name, url, image_url, image_alt}.
	 *
	 * Reads real WooCommerce taxonomy data, ordered by product count (most populated first) so the
	 * featured categories are the ones with actual products in them. Returns an empty array when
	 * WooCommerce is missing/inactive, or when the store has no product category yet (beyond the
	 * default "Uncategorized" one), so the calling template-part can skip rendering the section
	 * entirely rather than showing an empty grid.
	 *
	 * @param int $limit Maximum number of categories to return.
	 * @return array<int, array{name: string, url: string, image_url: string|null, image_alt: string}>
	 */
	public static function categories( int $limit = 5 ): array {
		if ( ! WooCommerceStatus::is_active() ) {
			return array();
		}

		$ids = self::term_ids( $limit );

		if ( empty( $ids ) ) {
			return array();
		}

		$terms = self::terms_for_ids( $ids );

		if ( empty( $terms ) ) {
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
	 * Resolves $ids to their real `WP_Term` objects with a single `get_terms(['include' => $ids])`
	 * call, instead of one `get_term()` call per ID — reordered to match $ids' own order (the
	 * product-count order self::term_ids() already determined), since `get_terms()` does not
	 * otherwise preserve it. `get_terms()`'s own default `update_term_meta_cache` (true) also primes
	 * every returned term's meta in that same single query, so the `thumbnail_id` lookups in
	 * self::categories()'s own `array_map()` below never issue a separate query per term either.
	 *
	 * @param array<int, int> $ids Term IDs, in the order they should be returned.
	 * @return array<int, \WP_Term>
	 */
	public static function terms_for_ids( array $ids ): array {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'include'    => $ids,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$terms_by_id = array();

		foreach ( $terms as $term ) {
			if ( $term instanceof \WP_Term ) {
				$terms_by_id[ $term->term_id ] = $term;
			}
		}

		$ordered_terms = array();

		foreach ( $ids as $id ) {
			if ( isset( $terms_by_id[ $id ] ) ) {
				$ordered_terms[] = $terms_by_id[ $id ];
			}
		}

		return $ordered_terms;
	}

	/**
	 * Returns the IDs of the top-level product categories shown on this section, through the theme's
	 * own cache.
	 *
	 * Only the query's term IDs are cached, not the mapped {name, url, image_url, image_alt} data
	 * self::categories() returns — the `get_terms()` count-ordered lookup is the actual expensive
	 * part. See Solar_Template\Cache\CacheInvalidator for when this cache is purged.
	 *
	 * @param int $limit Maximum number of terms to return.
	 * @return array<int, int> Term IDs.
	 */
	private static function term_ids( int $limit ): array {
		$cache = self::cache();
		$key   = "home_category_ids_{$limit}";
		$ids   = $cache->get( $key );

		if ( is_array( $ids ) ) {
			return $ids;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => $limit,
				'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
				'fields'     => 'ids',
			)
		);

		$ids = ( is_wp_error( $terms ) || empty( $terms ) ) ? array() : array_map( 'intval', $terms );

		$cache->set( $key, $ids );

		return $ids;
	}

	/**
	 * @return CacheInterface
	 */
	private static function cache(): CacheInterface {
		return new TransientCache();
	}
}
