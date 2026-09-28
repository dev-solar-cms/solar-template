<?php
/**
 * Created: 2026-09-28 15:00 CEST
 * Role: Unit test for Solar_Template\Blog\RelatedArticles.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert related_post_ids() reads through the theme's own cache — a second call with the
 *          same post/limit hits the cache rather than re-querying, matching Product\ProductRelated's
 *          own established (Docker-verified) convention for this pattern. Each test uses its own
 *          post ID so the shared in-memory transient stand-in (tests/php/bootstrap.php) never
 *          collides between test methods. self::posts()'s full WP_Post → card mapping
 *          (Blog\PostCardMapper, real-post-heavy) stays Docker-verified only.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Blog;

use PHPUnit\Framework\TestCase;
use Solar_Template\Blog\RelatedArticles;

final class RelatedArticlesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_related_post_ids_are_cached_after_the_first_call(): void {
		$GLOBALS['solar_template_test_get_posts_result'] = array( 31, 32 );
		$GLOBALS['solar_template_test_get_posts_calls']  = 0;

		$first  = RelatedArticles::related_post_ids( 100, array( 7 ), 3 );
		$second = RelatedArticles::related_post_ids( 100, array( 7 ), 3 );

		$this->assertSame( array( 31, 32 ), $first );
		$this->assertSame( $first, $second );
		$this->assertSame( 1, $GLOBALS['solar_template_test_get_posts_calls'] );
	}

	/**
	 * @return void
	 */
	public function test_related_post_ids_uses_a_distinct_cache_key_per_article(): void {
		$GLOBALS['solar_template_test_get_posts_result'] = array( 41 );
		$GLOBALS['solar_template_test_get_posts_calls']  = 0;

		RelatedArticles::related_post_ids( 101, array( 7 ), 3 );
		RelatedArticles::related_post_ids( 102, array( 7 ), 3 );

		$this->assertSame( 2, $GLOBALS['solar_template_test_get_posts_calls'] );
	}
}
