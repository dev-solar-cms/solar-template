<?php
/**
 * Created: 2026-09-26 21:00 CEST
 * Role: Blog page hero content (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the dark hero's eyebrow/heading/subtitle for the main blog index (editorial,
 *          filterable content — same convention as Solar_Template\FrontPage\Hero/BrandStory) and,
 *          separately, for a category/tag archive (real term data, no editorial placeholder).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the blog hero's content for the main index and for a taxonomy archive.
 */
final class BlogHero {

	/**
	 * Returns the main blog index's hero content.
	 *
	 * @return array{eyebrow: string, heading: string, subtitle: string}
	 */
	public static function heading(): array {
		$defaults = array(
			'eyebrow'  => __( 'Journal & inspiration', 'solar-template' ),
			'heading'  => __( 'Blog & News', 'solar-template' ),
			'subtitle' => __( 'Style tips, care guides, trends and a look behind the scenes of our selection.', 'solar-template' ),
		);

		/**
		 * Filters the main blog index's hero content.
		 *
		 * @param array $config See self::heading()'s return type.
		 */
		return apply_filters( 'solar_template_blog_hero', $defaults );
	}

	/**
	 * Returns a category/tag archive's hero content: the real term's own name/description rather
	 * than editorial placeholder content.
	 *
	 * @return array{eyebrow: string, heading: string, subtitle: string}
	 */
	public static function archive_heading(): array {
		$term = get_queried_object();

		$heading  = ( $term instanceof \WP_Term ) ? $term->name : get_the_archive_title();
		$subtitle = ( $term instanceof \WP_Term ) ? term_description( $term ) : '';

		return array(
			'eyebrow'  => __( 'Journal & inspiration', 'solar-template' ),
			'heading'  => $heading,
			'subtitle' => wp_strip_all_tags( $subtitle ),
		);
	}
}
