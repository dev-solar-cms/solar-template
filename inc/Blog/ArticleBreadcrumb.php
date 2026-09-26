<?php
/**
 * Created: 2026-09-26 21:25 CEST
 * Role: Single article breadcrumb (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the single article page's "Home > Blog > Article title" breadcrumb trail.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the single article page's breadcrumb trail.
 */
final class ArticleBreadcrumb {

	/**
	 * @param \WP_Post $post Article to build the trail for.
	 * @return array<int, array{label: string, url: string|null}> The last item's `url` is always
	 *                                                              null (current page).
	 */
	public static function items( \WP_Post $post ): array {
		$blog_page_id = (int) get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

		return array(
			array(
				'label' => __( 'Home', 'solar-template' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Blog', 'solar-template' ),
				'url'   => $blog_url,
			),
			array(
				'label' => wp_trim_words( get_the_title( $post ), 6 ),
				'url'   => null,
			),
		);
	}
}
