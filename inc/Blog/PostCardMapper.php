<?php
/**
 * Created: 2026-09-25 15:32 CEST
 * Role: WP_Post -> blog-card mapping (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map a `WP_Post` to the `$args` shape expected by template-parts/blog-card.php.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps a `WP_Post` to template-parts/blog-card.php's `$args`.
 */
final class PostCardMapper {

	/**
	 * @param \WP_Post $post Post to map.
	 * @return array See template-parts/blog-card.php's documented `$args` keys.
	 */
	public static function map( \WP_Post $post ): array {
		$categories = get_the_category( $post->ID );
		$category   = ! empty( $categories ) ? $categories[0]->name : '';

		$meta = sprintf(
			/* translators: 1: publication date, 2: estimated reading time in minutes. */
			__( '%1$s · %2$d min read', 'solar-template' ),
			get_the_date( '', $post ),
			ReadingTime::estimate( $post )
		);

		$thumbnail_url = get_the_post_thumbnail_url( $post, 'medium_large' );

		return array(
			'image_url'         => false !== $thumbnail_url ? $thumbnail_url : null,
			'image_alt'         => get_the_title( $post ),
			'permalink'         => get_permalink( $post ),
			'badge'             => '' !== $category ? array(
				'type'  => 'outline-gold',
				'label' => $category,
			) : null,
			'meta'              => $meta,
			'title'             => get_the_title( $post ),
			'excerpt'           => get_the_excerpt( $post ),
			'author_name'       => get_the_author_meta( 'display_name', $post->post_author ),
			'author_avatar_url' => get_avatar_url( $post->post_author ),
		);
	}

	/**
	 * Maps a `WP_Post` to template-parts/blog/featured-article.php's `$args` — the same underlying
	 * data as self::map(), reshaped for that template's own three separate meta fields (category,
	 * date, reading time) rather than one pre-formatted line.
	 *
	 * @param \WP_Post $post Post to map.
	 * @return array{image_url: string|null, image_alt: string, permalink: string, category: string,
	 *               date: string, reading_time_label: string, title: string, excerpt: string,
	 *               author_name: string}
	 */
	public static function map_featured( \WP_Post $post ): array {
		$categories = get_the_category( $post->ID );
		$category   = ! empty( $categories ) ? $categories[0]->name : '';

		$reading_minutes = ReadingTime::estimate( $post );

		$thumbnail_url = get_the_post_thumbnail_url( $post, 'large' );

		return array(
			'image_url'          => false !== $thumbnail_url ? $thumbnail_url : null,
			'image_alt'          => get_the_title( $post ),
			'permalink'          => get_permalink( $post ),
			'category'           => $category,
			'date'               => get_the_date( '', $post ),
			'reading_time_label' => sprintf(
				/* translators: %d: estimated reading time in minutes. */
				_n( '%d min read', '%d min read', $reading_minutes, 'solar-template' ),
				$reading_minutes
			),
			'title'              => get_the_title( $post ),
			'excerpt'            => get_the_excerpt( $post ),
			'author_name'        => get_the_author_meta( 'display_name', $post->post_author ),
		);
	}
}
