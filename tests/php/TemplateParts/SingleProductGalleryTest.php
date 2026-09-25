<?php
/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Regression test for template-parts/single-product/gallery.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product gallery template-part with a plain, already-computed image list (no
 *          real WordPress/WooCommerce install available here) and assert on the resulting markup:
 *          the main image, the thumbnail strip (only rendered past a single image), output
 *          escaping, and the single-image case.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class SingleProductGalleryTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/single-product/gallery.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A gallery with several images renders the first as the main image and every image as a
	 * thumbnail, the first marked active.
	 *
	 * @return void
	 */
	public function test_renders_main_image_and_thumbnail_strip(): void {
		$html = $this->render(
			array(
				'images' => array(
					array(
						'id'   => 1,
						'full' => 'https://example.test/one.jpg',
						'alt'  => 'View one <of> product',
					),
					array(
						'id'   => 2,
						'full' => 'https://example.test/two.jpg',
						'alt'  => 'View two',
					),
				),
			)
		);

		$this->assertStringContainsString( 'product-gallery__main-image', $html );
		$this->assertStringContainsString( 'src="https://example.test/one.jpg"', $html );
		// Escaped: a raw "<of>" must never appear unescaped in the alt attribute.
		$this->assertStringContainsString( 'View one &lt;of&gt; product', $html );
		$this->assertStringNotContainsString( 'View one <of> product', $html );
		$this->assertStringContainsString( 'product-gallery__thumbnails', $html );
		$this->assertStringContainsString( 'data-full="https://example.test/two.jpg"', $html );
		$this->assertMatchesRegularExpression( '/product-gallery__thumbnail is-active"[^>]*data-full="https:\/\/example\.test\/one\.jpg"/', $html );
	}

	/**
	 * A gallery with a single image renders only the main image, no thumbnail strip.
	 *
	 * @return void
	 */
	public function test_skips_thumbnail_strip_with_a_single_image(): void {
		$html = $this->render(
			array(
				'images' => array(
					array(
						'id'   => 1,
						'full' => 'https://example.test/only.jpg',
						'alt'  => 'Only image',
					),
				),
			)
		);

		$this->assertStringContainsString( 'src="https://example.test/only.jpg"', $html );
		$this->assertStringNotContainsString( 'product-gallery__thumbnails', $html );
	}

	/**
	 * An empty image list (no product image at all) renders nothing rather than a broken markup
	 * shell — single-product.php is responsible for substituting a placeholder image before calling
	 * this template-part.
	 *
	 * @return void
	 */
	public function test_renders_nothing_with_no_image(): void {
		$html = $this->render( array( 'images' => array() ) );

		$this->assertSame( '', $html );
	}
}
