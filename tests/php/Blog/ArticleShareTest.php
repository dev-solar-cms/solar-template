<?php
/**
 * Created: 2026-09-26 14:35 CEST
 * Role: Unit test for Solar_Template\Blog\ArticleShare.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure share-URL building logic behind the article page's share buttons
 *          (verified end to end in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Blog;

use PHPUnit\Framework\TestCase;
use Solar_Template\Blog\ArticleShare;

final class ArticleShareTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_builds_a_facebook_share_url(): void {
		$this->assertSame(
			'https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fexample.test%2Farticle',
			ArticleShare::share_url( 'facebook', 'My article', 'https://example.test/article' )
		);
	}

	/**
	 * @return void
	 */
	public function test_builds_an_x_share_url_with_the_title(): void {
		$this->assertSame(
			'https://twitter.com/intent/tweet?url=https%3A%2F%2Fexample.test%2Farticle&text=My%20article',
			ArticleShare::share_url( 'x', 'My article', 'https://example.test/article' )
		);
	}

	/**
	 * @return void
	 */
	public function test_builds_a_pinterest_share_url(): void {
		$this->assertSame(
			'https://www.pinterest.com/pin/create/button/?url=https%3A%2F%2Fexample.test%2Farticle&description=My%20article',
			ArticleShare::share_url( 'pinterest', 'My article', 'https://example.test/article' )
		);
	}

	/**
	 * @return void
	 */
	public function test_copy_link_has_no_url_of_its_own(): void {
		$this->assertSame( '', ArticleShare::share_url( 'copy_link', 'My article', 'https://example.test/article' ) );
	}
}
