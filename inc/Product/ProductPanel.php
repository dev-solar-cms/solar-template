<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Product page panel data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compute the product panel's rating summary from a real WC_Product, and expose its
 *          static, editorial blocks (trust badges, shipping/size/care accordion) as filterable
 *          config — same convention as Solar_Template\FrontPage\Hero's trust badges — ready for a
 *          future "Products" administration tab (Group 10 of the project roadmap) without one
 *          being built now.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Computes/exposes the product panel's rating summary and editorial config blocks.
 */
final class ProductPanel {

	/**
	 * @param \WC_Product $product Product to summarize the rating of.
	 * @return array{average: float, rounded_stars: int, review_count: int}
	 */
	public static function rating_summary( \WC_Product $product ): array {
		$average = (float) $product->get_average_rating();

		return array(
			'average'       => $average,
			'rounded_stars' => (int) round( $average ),
			'review_count'  => (int) $product->get_review_count(),
		);
	}

	/**
	 * Returns the product panel's trust badges (icon, title, subtitle), filterable for a future
	 * "Products" administration tab.
	 *
	 * @return array<int, array{icon: string, title: string, subtitle: string}>
	 */
	public static function trust_badges(): array {
		$defaults = array(
			array(
				'icon'     => '🚚',
				'title'    => __( 'Free shipping', 'solar-template' ),
				'subtitle' => __( 'From €60', 'solar-template' ),
			),
			array(
				'icon'     => '↩',
				'title'    => __( 'Free returns', 'solar-template' ),
				'subtitle' => __( 'Within 30 days', 'solar-template' ),
			),
			array(
				'icon'     => '🔒',
				'title'    => __( 'Secure payment', 'solar-template' ),
				'subtitle' => __( 'SSL encrypted', 'solar-template' ),
			),
		);

		/**
		 * Filters the product panel's trust badges.
		 *
		 * @param array $defaults See this method's return type.
		 */
		return apply_filters( 'solar_template_product_trust_badges', $defaults );
	}

	/**
	 * Returns the product panel's accordion sections (title, body), filterable for a future
	 * "Products" administration tab.
	 *
	 * @return array<int, array{title: string, body: string}>
	 */
	public static function accordion_sections(): array {
		$defaults = array(
			array(
				'title' => __( 'Shipping & Returns', 'solar-template' ),
				'body'  => __( 'Standard delivery in 3–5 business days (free from €60). Express delivery available. Returns accepted within 30 days in their original packaging.', 'solar-template' ),
			),
			array(
				'title' => __( 'Size guide', 'solar-template' ),
				'body'  => __( 'If in doubt between two sizes, we recommend choosing the larger one. See the product\'s own attributes above for the sizes currently available.', 'solar-template' ),
			),
			array(
				'title' => __( 'Care instructions', 'solar-template' ),
				'body'  => __( 'Hand washing recommended. Do not tumble dry. Iron on a low heat setting if needed.', 'solar-template' ),
			),
		);

		/**
		 * Filters the product panel's accordion sections.
		 *
		 * @param array $defaults See this method's return type.
		 */
		return apply_filters( 'solar_template_product_accordion_sections', $defaults );
	}
}
