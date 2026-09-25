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
}
