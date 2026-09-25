<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Regression test for template-parts/product-card.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product card template-part with fake data (no real WordPress/WooCommerce
 *          install available here) and assert on the resulting markup: every documented `$args`
 *          key is applied, output is escaped, and including the template twice (as a catalog grid
 *          will do once it exists) does not fatal on a duplicate function declaration.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class ProductCardTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/product-card.php';
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
				'image_url'        => 'https://example.test/product.jpg',
				'image_alt'        => 'Engraved solar watch',
				'permalink'        => 'https://example.test/product/solar-watch/',
				'badge'            => array(
					'type'  => 'new',
					'label' => 'New',
				),
				'category'         => 'Watches',
				'name'             => 'Solar Watch <Gold>',
				'price'            => 89.0,
				'regular_price'    => 109.0,
				'currency_symbol'  => '€',
				'discount_percent' => 18,
				'in_wishlist'      => true,
				'swatches'         => array( '#0D0D0D', '#C9A96E' ),
			)
		);

		$this->assertStringContainsString( 'src="https://example.test/product.jpg"', $html );
		$this->assertStringContainsString( 'badge--new', $html );
		$this->assertMatchesRegularExpression( '/badge--new">\s*New\s*</', $html );
		$this->assertStringContainsString( 'Watches', $html );
		// The product name is escaped: a raw "<Gold>" must never appear unescaped.
		$this->assertStringContainsString( 'Solar Watch &lt;Gold&gt;', $html );
		$this->assertStringNotContainsString( 'Solar Watch <Gold>', $html );
		$this->assertStringContainsString( '89,00', $html );
		$this->assertStringContainsString( '109,00', $html );
		$this->assertStringContainsString( '−18%', $html );
		$this->assertStringContainsString( 'is-active', $html );
		$this->assertStringContainsString( 'background-color: #0D0D0D', $html );
		$this->assertStringContainsString( 'background-color: #C9A96E', $html );
	}

	/**
	 * A card with only the required minimum still renders without notices/errors, and skips the
	 * optional blocks (badge, price, swatches) entirely rather than rendering them empty.
	 *
	 * @return void
	 */
	public function test_renders_minimal_data_without_optional_blocks(): void {
		$html = $this->render(
			array(
				'name' => 'Bare Product',
			)
		);

		$this->assertStringContainsString( 'Bare Product', $html );
		$this->assertStringNotContainsString( 'product-card__badge', $html );
		$this->assertStringNotContainsString( 'product-card__price', $html );
		$this->assertStringNotContainsString( 'product-card__swatches', $html );
	}

	/**
	 * A catalog grid will include this template-part once per product: the guarded helper
	 * function it declares must not fatal on a duplicate declaration.
	 *
	 * @return void
	 */
	public function test_can_be_rendered_more_than_once_on_the_same_page(): void {
		$first  = $this->render( array( 'name' => 'First product' ) );
		$second = $this->render( array( 'name' => 'Second product' ) );

		$this->assertStringContainsString( 'First product', $first );
		$this->assertStringContainsString( 'Second product', $second );
	}
}
