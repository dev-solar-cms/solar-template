<?php
/**
 * Created: 2026-09-25 15:43 CEST
 * Role: Front page brand story section content (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the "Brand story" section's content (eyebrow, heading, paragraphs, image, a
 *          floating stat highlight card, a row of three stats, and a CTA).
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page "Brand story" section content.
 */
final class BrandStory {

	/**
	 * Returns the front page "Brand story" section's content.
	 *
	 * All editorial content, exposed via a single filterable array — same convention as
	 * Hero::config(): a future "Home page" administration tab is expected to hook into this filter
	 * with the site owner's actual copy/image.
	 *
	 * @return array{
	 *     eyebrow: string,
	 *     heading: string,
	 *     paragraphs: string[],
	 *     image_url: string|null,
	 *     image_alt: string,
	 *     highlight: array{value: string, label: string},
	 *     stats: array<int, array{value: string, label: string}>,
	 *     cta: array{label: string, url: string},
	 * }
	 */
	public static function config(): array {
		$defaults = array(
			'eyebrow'    => __( 'Our story', 'solar-template' ),
			'heading'    => __( 'Craftsmanship, quality and contemporary design.', 'solar-template' ),
			'paragraphs' => array(
				__( 'For over a decade, we have been selecting and offering products that combine artisanal quality, durability and modern aesthetics.', 'solar-template' ),
				__( 'Every item is chosen with care, paying close attention to materials, manufacturing and environmental impact.', 'solar-template' ),
			),
			'image_url'  => null,
			'image_alt'  => '',
			'highlight'  => array(
				'value' => __( '10+', 'solar-template' ),
				'label' => __( 'years of expertise', 'solar-template' ),
			),
			'stats'      => array(
				array(
					'value' => __( '50K+', 'solar-template' ),
					'label' => __( 'Happy customers', 'solar-template' ),
				),
				array(
					'value' => __( '200+', 'solar-template' ),
					'label' => __( 'Products available', 'solar-template' ),
				),
				array(
					'value' => __( '4.9★', 'solar-template' ),
					'label' => __( 'Average rating', 'solar-template' ),
				),
			),
			'cta'        => array(
				'label' => __( 'Learn more', 'solar-template' ),
				'url'   => StoreLinks::page_url_by_slug( 'about' ),
			),
		);

		/**
		 * Filters the front page "Brand story" section's content.
		 *
		 * @param array $config See self::config()'s return type.
		 */
		return apply_filters( 'solar_template_brand_story_config', $defaults );
	}
}
