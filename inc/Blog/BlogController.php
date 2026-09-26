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
	 * `pre_get_posts`. Left untouched on every other query (category/tag archives, search, admin…),
	 * and a no-op when the featured block itself is disabled from the administration "Blog" tab
	 * (Solar_Template\Admin\BlogSettings) — the sticky post then appears in the grid like any other,
	 * rather than disappearing from the page entirely.
	 *
	 * @param \WP_Query $query The query being filtered.
	 * @return void
	 */
	public static function exclude_featured_post( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
			return;
		}

		/** This filter is documented in inc/Blog/FeaturedPost.php. */
		if ( ! apply_filters( 'solar_template_blog_featured_enabled', true ) ) {
			// With the featured block off, a sticky post should sort like any other by date, not be
			// pinned to page 1 by WordPress' own default sticky handling (which — since it isn't
			// excluded from this query the way it is below — would otherwise still prepend it ahead
			// of $query's own configured posts_per_page count).
			$query->set( 'ignore_sticky_posts', true );

			return;
		}

		$featured_id = FeaturedPost::id();

		if ( 0 !== $featured_id ) {
			$query->set( 'post__not_in', array( $featured_id ) );
		}
	}
}
