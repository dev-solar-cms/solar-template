<?php
/**
 * Created: 2026-09-26 11:35 CEST
 * Role: Regression test for template-parts/single-product/related-products.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the related products template-part with plain, already-computed data (no real
 *          WordPress/WooCommerce install available here) and assert on the resulting markup: it
 *          renders nothing without any related product, and renders the header plus one product
 *          card per given product otherwise.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class SingleProductRelatedProductsTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/single-product/related-products.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A product with no related product at all renders nothing.
	 *
	 * @return void
	 */
	public function test_renders_nothing_without_any_related_product(): void {
		$html = $this->render(
			array(
				'heading'  => array(
					'eyebrow' => 'Suggestions',
					'heading' => 'Related products',
				),
				'products' => array(),
			)
		);

		$this->assertSame( '', $html );
	}

	/**
	 * With related products, renders the section header and one card per product, each wrapped in
	 * its own grid item.
	 *
	 * @return void
	 */
	public function test_renders_header_and_one_card_per_related_product(): void {
		$html = $this->render(
			array(
				'heading'  => array(
					'eyebrow' => 'Suggestions',
					'heading' => 'Related products',
				),
				'products' => array(
					array(
						'image_url'        => 'https://example.test/one.jpg',
						'image_alt'        => 'Product One',
						'permalink'        => 'https://example.test/?product=one',
						'badge'            => null,
						'category'         => 'Category A',
						'name'             => 'Product One',
						'price'            => 75.0,
						'regular_price'    => 75.0,
						'currency_symbol'  => '€',
						'discount_percent' => null,
						'in_wishlist'      => false,
						'swatches'         => array(),
					),
					array(
						'image_url'        => 'https://example.test/two.jpg',
						'image_alt'        => 'Product Two',
						'permalink'        => 'https://example.test/?product=two',
						'badge'            => null,
						'category'         => 'Category B',
						'name'             => 'Product Two',
						'price'            => 49.0,
						'regular_price'    => 69.0,
						'currency_symbol'  => '€',
						'discount_percent' => 29,
						'in_wishlist'      => false,
						'swatches'         => array(),
					),
				),
			)
		);

		$this->assertStringContainsString( 'related-products__eyebrow', $html );
		$this->assertStringContainsString( 'Suggestions', $html );
		$this->assertStringContainsString( 'related-products__heading', $html );
		$this->assertStringContainsString( 'Related products', $html );
		$this->assertSame( 2, substr_count( $html, 'related-products__item' ) );
		$this->assertStringContainsString( 'Product One', $html );
		$this->assertStringContainsString( 'Product Two', $html );
	}

	/**
	 * The eyebrow is skipped entirely when empty, rather than rendering an empty element.
	 *
	 * @return void
	 */
	public function test_skips_eyebrow_when_empty(): void {
		$html = $this->render(
			array(
				'heading'  => array(
					'eyebrow' => '',
					'heading' => 'Related products',
				),
				'products' => array(
					array(
						'image_url'        => null,
						'image_alt'        => '',
						'permalink'        => 'https://example.test/?product=one',
						'badge'            => null,
						'category'         => '',
						'name'             => 'Product One',
						'price'            => 75.0,
						'regular_price'    => null,
						'currency_symbol'  => '€',
						'discount_percent' => null,
						'in_wishlist'      => false,
						'swatches'         => array(),
					),
				),
			)
		);

		$this->assertStringNotContainsString( 'related-products__eyebrow', $html );
	}
}
