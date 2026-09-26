<?php
/**
 * Created: 2026-09-26 21:15 CEST
 * Role: Blog "featured article" lookup (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Return the post shown in the featured block at the top of the main blog index, backed by
 *          WordPress' own native "Sticky" post feature rather than a new custom field/flag.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the blog's currently featured (sticky) post.
 */
final class FeaturedPost {

	/**
	 * @return int 0 when no post is marked sticky, the most recently stickied post's ID otherwise
	 *             (WordPress lists `sticky_posts` most-recent-first).
	 */
	public static function id(): int {
		$sticky = get_option( 'sticky_posts' );

		return ! empty( $sticky ) ? (int) $sticky[0] : 0;
	}

	/**
	 * @return array|null template-parts/blog/featured-article.php's `$args`
	 *                     (see PostCardMapper::map_featured()), or null when no post is sticky, it is
	 *                     not published, or the section is disabled from the administration "Blog" tab
	 *                     (Solar_Template\Admin\BlogSettings).
	 */
	public static function current(): ?array {
		/**
		 * Filters whether the blog index's featured article block is enabled.
		 *
		 * @param bool $enabled True by default.
		 */
		if ( ! apply_filters( 'solar_template_blog_featured_enabled', true ) ) {
			return null;
		}

		$post_id = self::id();

		if ( 0 === $post_id ) {
			return null;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || 'publish' !== $post->post_status ) {
			return null;
		}

		return PostCardMapper::map_featured( $post );
	}
}
