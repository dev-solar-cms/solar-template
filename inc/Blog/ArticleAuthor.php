<?php
/**
 * Created: 2026-09-26 21:30 CEST
 * Role: Single article author bar/card (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the author bar (below the title) and the larger author card (after the tags) from
 *          the post author's own real WordPress user profile — display name, avatar, biographical
 *          info ("Biographical Info" in the user's own profile screen, a native WordPress field) and
 *          their author archive URL.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the single article page's author bar/card content.
 */
final class ArticleAuthor {

	/**
	 * @param \WP_Post $post Article to build the author bar for.
	 * @return array{name: string, avatar_url: string|null}
	 */
	public static function bar( \WP_Post $post ): array {
		return array(
			'name'       => get_the_author_meta( 'display_name', $post->post_author ),
			'avatar_url' => get_avatar_url( $post->post_author ),
		);
	}

	/**
	 * @param \WP_Post $post Article to build the author card for.
	 * @return array{name: string, avatar_url: string|null, initial: string, bio: string, archive_url: string}
	 */
	public static function card( \WP_Post $post ): array {
		$name = get_the_author_meta( 'display_name', $post->post_author );

		return array(
			'name'        => $name,
			'avatar_url'  => get_avatar_url( $post->post_author ),
			'initial'     => mb_strtoupper( mb_substr( $name, 0, 1 ) ),
			'bio'         => get_the_author_meta( 'description', $post->post_author ),
			'archive_url' => get_author_posts_url( $post->post_author ),
		);
	}
}
