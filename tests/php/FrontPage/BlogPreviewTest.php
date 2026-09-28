<?php
/**
 * Created: 2026-09-28 15:00 CEST
 * Role: Unit test for Solar_Template\FrontPage\BlogPreview.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert post_ids() reads through the theme's own cache — a second call with the same
 *          $limit hits the cache rather than re-querying, matching FrontPage\FeaturedProducts/
 *          Categories' own established (Docker-verified) convention for this pattern. Each test uses
 *          its own $limit so the shared in-memory transient stand-in (tests/php/bootstrap.php) never
 *          collides between test methods — this class has no per-test reset for the same reason
 *          Support\TransientCacheTest doesn't. self::posts()'s full WP_Post → card mapping
 *          (Blog\PostCardMapper, real-post-heavy) stays Docker-verified only.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\FrontPage;

use PHPUnit\Framework\TestCase;
use Solar_Template\FrontPage\BlogPreview;

final class BlogPreviewTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_post_ids_are_cached_after_the_first_call(): void {
		$GLOBALS['solar_template_test_get_posts_result'] = array( 11, 12, 13 );
		$GLOBALS['solar_template_test_get_posts_calls']  = 0;

		$first  = BlogPreview::post_ids( 3 );
		$second = BlogPreview::post_ids( 3 );

		$this->assertSame( array( 11, 12, 13 ), $first );
		$this->assertSame( $first, $second );
		$this->assertSame( 1, $GLOBALS['solar_template_test_get_posts_calls'] );
	}

	/**
	 * @return void
	 */
	public function test_post_ids_uses_a_distinct_cache_key_per_limit(): void {
		$GLOBALS['solar_template_test_get_posts_result'] = array( 21, 22 );
		$GLOBALS['solar_template_test_get_posts_calls']  = 0;

		BlogPreview::post_ids( 5 );
		BlogPreview::post_ids( 6 );

		$this->assertSame( 2, $GLOBALS['solar_template_test_get_posts_calls'] );
	}
}
