<?php
/**
 * Created: 2026-09-26 21:35 CEST
 * Role: Single article "related articles" section (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the section heading and the real posts sharing at least one of the current
 *          article's categories, mapped for the existing blog card component. Renders nothing when
 *          no related post exists (see template-parts/single-article/related.php).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the single article page's "related articles" section.
 */
final class RelatedArticles {

	/**
	 * @return array{eyebrow: string, heading: string, view_all: array{label: string, url: string}}
	 */
	public static function heading(): array {
		$blog_page_id = (int) get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

		return array(
			'eyebrow'  => __( 'Also worth reading', 'solar-template' ),
			'heading'  => __( 'Related articles', 'solar-template' ),
			'view_all' => array(
				'label' => __( 'View all →', 'solar-template' ),
				'url'   => $blog_url,
			),
		);
	}

	/**
	 * @param \WP_Post $post   Current article.
	 * @param int      $limit  Maximum number of related articles to return.
	 * @return array<int, array> List of template-parts/blog-card.php `$args` arrays.
	 */
	public static function posts( \WP_Post $post, int $limit = 3 ): array {
		$category_ids = wp_get_post_categories( $post->ID );

		if ( empty( $category_ids ) ) {
			return array();
		}

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'post__not_in'   => array( $post->ID ),
				'category__in'   => $category_ids,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		return array_map( array( PostCardMapper::class, 'map' ), $posts );
	}
}
