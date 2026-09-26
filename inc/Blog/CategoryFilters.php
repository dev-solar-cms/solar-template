<?php
/**
 * Created: 2026-09-26 21:05 CEST
 * Role: Blog category filter pills (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: List every real blog category with at least one published post, as plain links (no
 *          JavaScript needed — each pill is a real link to that category's own archive, or back to
 *          the main blog index for "All"), with the currently viewed one flagged active.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the blog's category filter pill list.
 */
final class CategoryFilters {

	/**
	 * @return array<int, array{label: string, url: string, is_active: bool}> "All" first, then every
	 *                                                                        category with at least
	 *                                                                        one published post.
	 */
	public static function items(): array {
		$blog_page_id = (int) get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

		$items = array(
			array(
				'label'     => __( 'All articles', 'solar-template' ),
				'url'       => $blog_url,
				'is_active' => ! is_category() && ! is_tag(),
			),
		);

		$categories = get_categories(
			array(
				'orderby'    => 'name',
				'hide_empty' => true,
			)
		);

		foreach ( $categories as $category ) {
			$items[] = array(
				'label'     => $category->name,
				'url'       => get_category_link( $category ),
				'is_active' => is_category( $category->term_id ),
			);
		}

		return $items;
	}
}
