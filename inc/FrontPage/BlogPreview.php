<?php
/**
 * Created: 2026-09-25 15:45 CEST
 * Role: Front page blog preview section (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the "Blog preview" section header and the latest published blog posts, mapped
 *          for the blog card template-part.
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

use Solar_Template\Blog\PostCardMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page "Blog preview" section header + post query.
 */
final class BlogPreview {

	/**
	 * Returns the front page's "Blog preview" section header (eyebrow label, heading, "view all"
	 * link) — everything except the post data itself, see self::posts().
	 *
	 * @return array{eyebrow: string, heading: string, view_all: array{label: string, url: string}}
	 */
	public static function heading(): array {
		$blog_page_id = (int) get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

		$defaults = array(
			'eyebrow'  => __( 'News', 'solar-template' ),
			'heading'  => __( 'Inspiration & tips', 'solar-template' ),
			'view_all' => array(
				'label' => __( 'View all articles →', 'solar-template' ),
				'url'   => $blog_url,
			),
		);

		/**
		 * Filters the front page's "Blog preview" section header.
		 *
		 * @param array $config See self::heading()'s return type.
		 */
		return apply_filters( 'solar_template_blog_preview_heading', $defaults );
	}

	/**
	 * Returns the front page's latest published blog posts, mapped for template-parts/blog-card.php.
	 *
	 * Reads real WordPress post data (not editorial placeholder content). Returns an empty array
	 * when the site has no published post yet, so the calling template-part can skip rendering the
	 * section entirely rather than showing an empty grid.
	 *
	 * @param int $limit Maximum number of posts to return.
	 * @return array<int, array> List of template-parts/blog-card.php `$args` arrays.
	 */
	public static function posts( int $limit = 3 ): array {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		return array_map( array( PostCardMapper::class, 'map' ), $posts );
	}
}
