<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Regression test for template-parts/blog-card.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the blog card template-part with fake data and assert on the resulting markup:
 *          every documented `$args` key is applied, output is escaped, and optional blocks (badge,
 *          excerpt, author) are skipped rather than rendered empty when their data is missing.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class BlogCardTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/blog-card.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A fully-populated card renders every element with the values it was given.
	 *
	 * @return void
	 */
	public function test_renders_every_element_with_given_data(): void {
		$html = $this->render(
			array(
				'image_url'         => 'https://example.test/post.jpg',
				'image_alt'         => 'A workshop bench',
				'permalink'         => 'https://example.test/blog/caring-for-solar-gold/',
				'badge'             => array(
					'type'  => 'outline-gold',
					'label' => 'Guides',
				),
				'meta'              => 'Jan 12, 2026 · 5 min read',
				'title'             => 'Caring for <Solar> Gold',
				'excerpt'           => 'A short excerpt about the article content.',
				'author_name'       => 'Amara Diallo',
				'author_avatar_url' => 'https://example.test/avatar.jpg',
			)
		);

		$this->assertStringContainsString( 'src="https://example.test/post.jpg"', $html );
		$this->assertStringContainsString( 'badge--outline-gold', $html );
		$this->assertMatchesRegularExpression( '/badge--outline-gold">\s*Guides\s*</', $html );
		$this->assertStringContainsString( 'Jan 12, 2026', $html );
		// The title is escaped: a raw "<Solar>" must never appear unescaped.
		$this->assertStringContainsString( 'Caring for &lt;Solar&gt; Gold', $html );
		$this->assertStringNotContainsString( 'Caring for <Solar> Gold', $html );
		$this->assertStringContainsString( 'A short excerpt about the article content.', $html );
		$this->assertStringContainsString( 'Amara Diallo', $html );
		$this->assertStringContainsString( 'src="https://example.test/avatar.jpg"', $html );
	}

	/**
	 * A card with only the required minimum still renders without notices/errors, and skips the
	 * optional blocks (badge, meta, excerpt, author) entirely rather than rendering them empty.
	 *
	 * @return void
	 */
	public function test_renders_minimal_data_without_optional_blocks(): void {
		$html = $this->render(
			array(
				'title' => 'Bare Post',
			)
		);

		$this->assertStringContainsString( 'Bare Post', $html );
		$this->assertStringNotContainsString( 'blog-card__badge', $html );
		$this->assertStringNotContainsString( 'blog-card__meta', $html );
		$this->assertStringNotContainsString( 'blog-card__excerpt', $html );
		$this->assertStringNotContainsString( 'blog-card__author', $html );
	}

	/**
	 * A blog grid will include this template-part once per post: rendering it twice must not
	 * fatal or leak state between renders.
	 *
	 * @return void
	 */
	public function test_can_be_rendered_more_than_once_on_the_same_page(): void {
		$first  = $this->render( array( 'title' => 'First post' ) );
		$second = $this->render( array( 'title' => 'Second post' ) );

		$this->assertStringContainsString( 'First post', $first );
		$this->assertStringContainsString( 'Second post', $second );
	}
}
