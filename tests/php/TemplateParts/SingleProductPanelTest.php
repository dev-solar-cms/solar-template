<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Regression test for template-parts/single-product/panel.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product panel template-part with plain, already-computed data (no real
 *          WordPress/WooCommerce install available here) and assert on the resulting markup:
 *          badges, rating stars/average/review link, stock status, price, Color/Size selectors,
 *          "Add to cart" form, short description, trust badges, accordion, and that every optional
 *          block is skipped when empty.
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
		$this->assertStringNotContainsString( 'product-panel__cart-form', $html );
		$this->assertStringNotContainsString( 'product-panel__variations', $html );
		$this->assertStringContainsString( 'product-panel__stock--out-of-stock', $html );
	}

	/**
	 * A simple product's cart form renders its price and an enabled "Add to cart" button, no
	 * Color/Size selectors.
	 *
	 * @return void
	 */
	public function test_renders_simple_product_cart_form(): void {
		$html = $this->render(
			array(
				'title'     => 'Simple Product',
				'rating'    => array(
					'average'       => 0.0,
					'rounded_stars' => 0,
					'review_count'  => 0,
				),
				'stock'     => array(
					'label'    => 'In stock',
					'modifier' => 'in-stock',
				),
				'cart_form' => array(
					'product_id'       => 42,
					'is_variable'      => false,
					'price_html'       => '<span class="amount">42,00&nbsp;€</span>',
					'can_add_to_cart'  => true,
					'variation_groups' => array(),
				),
			)
		);

		$this->assertStringContainsString( 'data-product-price', $html );
		$this->assertStringContainsString( '42,00', $html );
		$this->assertStringContainsString( 'name="add-to-cart"', $html );
		$this->assertStringContainsString( 'value="42"', $html );
		$this->assertStringNotContainsString( 'product-panel__variations', $html );
		$this->assertDoesNotMatchRegularExpression( '/product-panel__add-to-cart"\s*disabled/', $html );
	}

	/**
	 * A variable product's cart form renders Color/Size selectors (with an unavailable option
	 * disabled) and a disabled "Add to cart" button until a selection resolves a real variation
	 * (see tests/js/product.test.js for that resolution logic).
	 *
	 * @return void
	 */
	public function test_renders_variable_product_selectors_disabled_until_resolved(): void {
		$html = $this->render(
			array(
				'title'     => 'Variable Product',
				'rating'    => array(
					'average'       => 0.0,
					'rounded_stars' => 0,
					'review_count'  => 0,
				),
				'stock'     => array(
					'label'    => 'In stock',
					'modifier' => 'in-stock',
				),
				'cart_form' => array(
					'product_id'       => 7,
					'is_variable'      => true,
					'price_html'       => '<span class="amount">20,00&nbsp;€ – 50,00&nbsp;€</span>',
					'can_add_to_cart'  => false,
					'variation_groups' => array(
						array(
							'taxonomy' => 'pa_color',
							'type'     => 'color',
							'label'    => 'Color',
							'options'  => array(
								array(
									'slug'      => 'black',
									'name'      => 'Black',
									'color'     => '#0d0d0d',
									'available' => true,
								),
							),
						),
						array(
							'taxonomy' => 'pa_size',
							'type'     => 'size',
							'label'    => 'Size',
							'options'  => array(
								array(
									'slug'      => 'm',
									'name'      => 'M',
									'color'     => null,
									'available' => true,
								),
								array(
									'slug'      => 'xxl',
									'name'      => 'XXL',
									'color'     => null,
									'available' => false,
								),
							),
						),
					),
				),
			)
		);

		$this->assertStringContainsString( 'product-panel__variations', $html );
		$this->assertStringContainsString( 'data-attribute="pa_color"', $html );
		$this->assertStringContainsString( 'product-panel__swatch', $html );
		$this->assertStringContainsString( 'data-attribute="pa_size"', $html );
		$this->assertStringContainsString( 'product-panel__variation-id', $html );
		$this->assertMatchesRegularExpression( '/product-panel__size-option is-unavailable"[^>]*disabled/', $html );
		$this->assertMatchesRegularExpression( '/product-panel__add-to-cart"\s*\n?\s*disabled/', $html );
	}

	/**
	 * A product with custom engraving enabled renders the toggle/field inside the cart form (so
	 * its values submit with "Add to cart"), with the configured surcharge/max length applied; a
	 * product without it renders neither.
	 *
	 * @return void
	 */
	public function test_renders_engraving_section_only_when_enabled(): void {
		$base_args = array(
			'title'     => 'Engravable Product',
			'rating'    => array(
				'average'       => 0.0,
				'rounded_stars' => 0,
				'review_count'  => 0,
			),
			'stock'     => array(
				'label'    => 'In stock',
				'modifier' => 'in-stock',
			),
			'cart_form' => array(
				'product_id'       => 9,
				'is_variable'      => false,
				'price_html'       => '<span class="amount">30,00&nbsp;€</span>',
				'can_add_to_cart'  => true,
				'variation_groups' => array(),
			),
		);

		$with_engraving = $this->render(
			array_merge(
				$base_args,
				array(
					'engraving' => array(
						'price'      => 25.0,
						'max_length' => 20,
					),
				)
			)
		);

		$this->assertStringContainsString( 'product-panel__engraving', $with_engraving );
		$this->assertStringContainsString( 'name="solar_template_engraving_enabled"', $with_engraving );
		$this->assertStringContainsString( 'name="solar_template_engraving_text"', $with_engraving );
		$this->assertStringContainsString( 'maxlength="20"', $with_engraving );
		$this->assertStringContainsString( 'data-surcharge="25"', $with_engraving );
		$this->assertStringContainsString( '25.00', $with_engraving );

		$without_engraving = $this->render( array_merge( $base_args, array( 'engraving' => null ) ) );

		$this->assertStringNotContainsString( 'product-panel__engraving', $without_engraving );
		$this->assertStringNotContainsString( 'solar_template_engraving', $without_engraving );
	}
}
