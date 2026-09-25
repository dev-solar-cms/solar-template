<?php
/**
 * Created: 2026-09-25 15:31 CEST
 * Role: Blog post reading time estimate (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Estimate a post's reading time from its word count.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Estimates a post's reading time.
 */
final class ReadingTime {

	/**
	 * Estimates a post's reading time, in whole minutes (minimum 1), from its word count at a
	 * conventional average reading speed of 200 words per minute.
	 *
	 * @param \WP_Post $post Post to estimate.
	 * @return int Reading time, in minutes.
	 */
	public static function estimate( \WP_Post $post ): int {
		$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );

		return max( 1, (int) ceil( $word_count / 200 ) );
	}
}
