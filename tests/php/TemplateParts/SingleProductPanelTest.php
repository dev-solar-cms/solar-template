<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Regression test for template-parts/single-product/panel.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product panel template-part with plain, already-computed data (no real
 *          WordPress/WooCommerce install available here) and assert on the resulting markup:
 *          badges, rating stars/average/review link, stock status, short description, trust
 *          badges, accordion, and that every optional block is skipped when empty.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class SingleProductPanelTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/single-product/panel.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A fully-populated panel renders every element with the values it was given.
	 *
	 * @return void
	 */
	public function test_renders_every_element_with_given_data(): void {
		$html = $this->render(
			array(
				'title'              => 'Solar Watch <Gold>',
				'badges'             => array(
					array(
						'type'  => 'new',
						'label' => 'New',
					),
					array(
						'type'  => 'premium',
						'label' => 'Solar Premium',
					),
				),
				'rating'             => array(
					'average'       => 4.8,
					'rounded_stars' => 5,
					'review_count'  => 124,
				),
				'stock'              => array(
					'label'    => 'In stock',
					'modifier' => 'in-stock',
				),
				'short_description'  => '<p>Crafted with premium materials.</p>',
				'trust_badges'       => array(
					array(
						'icon'     => '🚚',
						'title'    => 'Free shipping',
						'subtitle' => 'From €60',
					),
				),
				'accordion_sections' => array(
					array(
						'title' => 'Shipping & Returns',
						'body'  => 'Standard delivery in 3-5 days.',
					),
				),
			)
		);

		$this->assertStringContainsString( 'Solar Watch &lt;Gold&gt;', $html );
		$this->assertStringNotContainsString( 'Solar Watch <Gold>', $html );
		$this->assertStringContainsString( 'product-panel__badge--new', $html );
		$this->assertStringContainsString( 'product-panel__badge--premium', $html );
		$this->assertStringContainsString( 'Solar Premium', $html );
		$this->assertMatchesRegularExpression( '/(&#9733;\s*){5}(&#9734;\s*){0}/', $html );
		$this->assertStringContainsString( '4.8', $html );
		$this->assertStringContainsString( '124 reviews', $html );
		$this->assertStringContainsString( 'product-panel__stock--in-stock', $html );
		$this->assertStringContainsString( 'In stock', $html );
		$this->assertStringContainsString( 'Crafted with premium materials.', $html );
		$this->assertStringContainsString( 'Free shipping', $html );
		$this->assertStringContainsString( 'From €60', $html );
		$this->assertStringContainsString( 'Shipping &amp; Returns', $html );
		$this->assertStringContainsString( 'Standard delivery in 3-5 days.', $html );
	}

	/**
	 * A panel with only the required minimum skips every optional block (badges, review link,
	 * description, trust badges, accordion) entirely rather than rendering it empty.
	 *
	 * @return void
	 */
	public function test_skips_optional_blocks_with_minimal_data(): void {
		$html = $this->render(
			array(
				'title'  => 'Bare Product',
				'rating' => array(
					'average'       => 0.0,
					'rounded_stars' => 0,
					'review_count'  => 0,
				),
				'stock'  => array(
					'label'    => 'Out of stock',
					'modifier' => 'out-of-stock',
				),
			)
		);

		$this->assertStringContainsString( 'Bare Product', $html );
		$this->assertStringNotContainsString( 'product-panel__badges', $html );
		$this->assertStringNotContainsString( 'product-panel__reviews-link', $html );
		$this->assertStringNotContainsString( 'product-panel__description', $html );
		$this->assertStringNotContainsString( 'product-panel__trust', $html );
		$this->assertStringNotContainsString( 'product-panel__accordion', $html );
		$this->assertStringContainsString( 'product-panel__stock--out-of-stock', $html );
	}
}
