<?php
/**
 * Created: 2026-09-26 21:20 CEST
 * Role: Blog archive "controller" (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exclude the featured (sticky) post from the main blog index's own grid query — it is
 *          already shown once, in the featured block above the grid.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the blog index's main query into WordPress.
 */
final class BlogController {

	/**
	 * Excludes the featured (sticky) post from the main blog index's own query, hooked to
	 * `pre_get_posts`. Left untouched on every other query (category/tag archives, search, admin…).
	 *
	 * @param \WP_Query $query The query being filtered.
	 * @return void
	 */
	public static function exclude_featured_post( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
			return;
		}

		$featured_id = FeaturedPost::id();

		if ( 0 !== $featured_id ) {
			$query->set( 'post__not_in', array( $featured_id ) );
		}
	}
}
