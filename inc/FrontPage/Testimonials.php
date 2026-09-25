<?php
/**
 * Created: 2026-09-25 15:44 CEST
 * Role: Front page testimonials section (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the "Testimonials" section header and the customer testimonials.
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page "Testimonials" section header + content.
 */
final class Testimonials {

	/**
	 * Returns the front page "Testimonials" section header (eyebrow label, heading).
	 *
	 * @return array{eyebrow: string, heading: string}
	 */
	public static function heading(): array {
		$defaults = array(
			'eyebrow' => __( 'Testimonials', 'solar-template' ),
			'heading' => __( 'What our customers say', 'solar-template' ),
		);

		/**
		 * Filters the front page "Testimonials" section header.
		 *
		 * @param array $config See self::heading()'s return type.
		 */
		return apply_filters( 'solar_template_testimonials_heading', $defaults );
	}

	/**
	 * Returns the front page's customer testimonials.
	 *
	 * Editorial placeholder content, exposed via a single filterable array — same convention as
	 * Hero::config(): no dedicated custom post type or admin screen is introduced for this step; a
	 * future "Home page" administration tab is expected to hook into this filter with the site
	 * owner's actual reviews.
	 *
	 * @return array<int, array{quote: string, rating: int, author_name: string, author_since: string, avatar_url: string|null}>
	 */
	public static function testimonials(): array {
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
		 * @param array $testimonials See self::testimonials()'s return type.
		 */
		return apply_filters( 'solar_template_testimonials', $defaults );
	}
}
