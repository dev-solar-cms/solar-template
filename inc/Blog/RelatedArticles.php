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

use Solar_Template\Contracts\CacheInterface;
use Solar_Template\Support\TransientCache;

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
	 * @return int Configured number of related articles to show, from the administration "Blog" tab
	 *              (Solar_Template\Admin\BlogSettings). 0 disables the section entirely.
	 */
	public static function default_limit(): int {
		/**
		 * Filters the number of related articles shown on the article page.
		 *
		 * @param int $limit 3 by default.
		 */
		return max( 0, (int) apply_filters( 'solar_template_related_articles_count', 3 ) );
	}

	/**
	 * @param \WP_Post $post   Current article.
	 * @param int|null $limit  Maximum number of related articles to return, self::default_limit()
	 *                          when null. 0 always returns an empty list.
	 * @return array<int, array> List of template-parts/blog-card.php `$args` arrays.
	 */
	public static function posts( \WP_Post $post, ?int $limit = null ): array {
		$limit        = $limit ?? self::default_limit();
		$category_ids = wp_get_post_categories( $post->ID );

		if ( $limit <= 0 || empty( $category_ids ) ) {
			return array();
		}

		$posts = array_filter( array_map( 'get_post', self::related_post_ids( $post->ID, $category_ids, $limit ) ) );

		return array_map( array( PostCardMapper::class, 'map' ), $posts );
	}

	/**
	 * Returns the given article's related post IDs, through the theme's own cache.
	 *
	 * Only the query's post IDs are cached, not the mapped, translated card data self::posts()
	 * returns — same convention as Solar_Template\Product\ProductRelated for the product page's own
	 * related products. See Solar_Template\Cache\CacheInvalidator for when this cache is purged.
	 *
	 * @param int             $post_id      Current article's post ID.
	 * @param array<int, int> $category_ids Current article's category term IDs.
	 * @param int             $limit        Maximum number of related articles to return.
	 * @return array<int, int> Related post IDs.
	 */
	public static function related_post_ids( int $post_id, array $category_ids, int $limit ): array {
		$cache = self::cache();
		$key   = "related_article_ids_{$post_id}_{$limit}";
		$ids   = $cache->get( $key );

		if ( is_array( $ids ) ) {
			return $ids;
		}

		$ids = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'post__not_in'   => array( $post_id ),
				'category__in'   => $category_ids,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);

		$cache->set( $key, $ids );

		return $ids;
	}

	/**
	 * @return CacheInterface
	 */
	private static function cache(): CacheInterface {
		return new TransientCache();
	}
}
