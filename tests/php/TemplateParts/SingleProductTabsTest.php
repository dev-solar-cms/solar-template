<?php
/**
 * Created: 2026-09-26 10:50 CEST
 * Role: Regression test for template-parts/single-product/tabs.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product tabs template-part with plain, already-computed data (no real
 *          WordPress/WooCommerce install available here) and assert on the resulting markup: tab
 *          list/panel ARIA wiring, description content/image, reviews summary/list, specifications
 *          table, and that the whole template-part renders nothing when there is no tab to show.
 *          `can_submit` stays false throughout: rendering the real review form calls WordPress'
 *          core `comment_form()`, unavailable in this minimal bootstrap (see
 *          tests/php/bootstrap.php's own purpose note).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class SingleProductTabsTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/single-product/tabs.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * Minimal reviews block shared by fixtures below (reviews disabled, nothing to render).
	 *
	 * @return array
	 */
	private function disabled_reviews(): array {
		return array(
			'enabled'           => false,
			'can_submit'        => false,
			'summary'           => array(
				'average'       => 0.0,
				'rounded_stars' => 0,
				'count'         => 0,
				'distribution'  => array(),
			),
			'list'              => array(),
			'comment_form_args' => array(),
		);
	}

	/**
	 * A product with no description, no open reviews and no attribute renders nothing at all.
	 *
	 * @return void
	 */
	public function test_renders_nothing_without_any_tab_content(): void {
		$html = $this->render(
			array(
				'description'    => array(
					'content' => '',
					'image'   => null,
				),
				'reviews'        => $this->disabled_reviews(),
				'specifications' => array(),
			)
		);

		$this->assertSame( '', $html );
	}

	/**
	 * The description tab renders its content and, when given one, a second image; it is marked
	 * active (only tab available).
	 *
	 * @return void
	 */
	public function test_renders_description_tab_with_and_without_secondary_image(): void {
		$html = $this->render(
			array(
				'description'    => array(
					'content' => '<p>Crafted with premium materials.</p>',
					'image'   => array(
						'id'   => 12,
						'full' => 'https://example.test/detail.jpg',
						'alt'  => 'Detail shot',
					),
				),
				'reviews'        => $this->disabled_reviews(),
				'specifications' => array(),
			)
		);

		$this->assertStringContainsString( 'role="tab"', $html );
		$this->assertStringContainsString( 'aria-selected="true"', $html );
		$this->assertStringContainsString( 'Description', $html );
		$this->assertStringContainsString( 'Crafted with premium materials.', $html );
		$this->assertStringContainsString( 'https://example.test/detail.jpg', $html );
		$this->assertStringContainsString( 'Detail shot', $html );
		$this->assertStringNotContainsString( 'Specifications', $html );

		$html_without_image = $this->render(
			array(
				'description'    => array(
					'content' => '<p>Crafted with premium materials.</p>',
					'image'   => null,
				),
				'reviews'        => $this->disabled_reviews(),
				'specifications' => array(),
			)
		);

		$this->assertStringNotContainsString( 'product-tabs__description-media', $html_without_image );
	}

	/**
	 * The reviews tab renders the rating summary/distribution and every given review, and skips the
	 * review form entirely when the visitor cannot submit one.
	 *
	 * @return void
	 */
	public function test_renders_reviews_tab_with_summary_and_list(): void {
		$html = $this->render(
			array(
				'description'    => array(
					'content' => '',
					'image'   => null,
				),
				'reviews'        => array(
					'enabled'           => true,
					'can_submit'        => false,
					'summary'           => array(
						'average'       => 4.8,
						'rounded_stars' => 5,
						'count'         => 2,
						'distribution'  => array(
							array(
								'stars'   => 5,
								'count'   => 2,
								'percent' => 100,
							),
							array(
								'stars'   => 4,
								'count'   => 0,
								'percent' => 0,
							),
						),
					),
					'list'              => array(
						array(
							'author'   => 'Marie L.',
							'avatar'   => '<img src="https://example.test/avatar.jpg" alt="" />',
							'rating'   => 5,
							'date'     => 'January 15, 2025',
							'verified' => true,
							'content'  => 'Absolutely magnificent.',
						),
					),
					'comment_form_args' => array(),
				),
				'specifications' => array(),
			)
		);

		$this->assertStringContainsString( 'Reviews (2)', $html );
		$this->assertStringContainsString( '4.8', $html );
		$this->assertStringContainsString( 'product-tabs__reviews-bar-fill', $html );
		$this->assertStringContainsString( '100', $html );
		$this->assertStringContainsString( 'Marie L.', $html );
		$this->assertStringContainsString( 'Absolutely magnificent.', $html );
		$this->assertStringContainsString( 'Verified purchase', $html );
		$this->assertStringNotContainsString( 'product-tabs__review-form', $html );
	}

	/**
	 * An empty review list renders the "no reviews yet" message instead of an empty list.
	 *
	 * @return void
	 */
	public function test_renders_empty_reviews_message_when_no_review_exists(): void {
		$html = $this->render(
			array(
				'description'    => array(
					'content' => '',
					'image'   => null,
				),
				'reviews'        => array(
					'enabled'           => true,
					'can_submit'        => false,
					'summary'           => array(
						'average'       => 0.0,
						'rounded_stars' => 0,
						'count'         => 0,
						'distribution'  => array(),
					),
					'list'              => array(),
					'comment_form_args' => array(),
				),
				'specifications' => array(),
			)
		);

		$this->assertStringContainsString( 'There are no reviews yet.', $html );
		$this->assertStringNotContainsString( 'product-tabs__reviews-list', $html );
		$this->assertStringNotContainsString( 'product-tabs__reviews-distribution', $html );
	}

	/**
	 * The specifications tab renders one row per given entry, alternating with the description tab
	 * available too (specifications is not the only/active tab in that case).
	 *
	 * @return void
	 */
	public function test_renders_specifications_table(): void {
		$html = $this->render(
			array(
				'description'    => array(
					'content' => '<p>Real description.</p>',
					'image'   => null,
				),
				'reviews'        => $this->disabled_reviews(),
				'specifications' => array(
					array(
						'label' => 'Weight',
						'value' => '320 g',
					),
					array(
						'label' => 'Colors',
						'value' => 'Black, Gold',
					),
				),
			)
		);

		$this->assertStringContainsString( 'Specifications', $html );
		$this->assertStringContainsString( 'Weight', $html );
		$this->assertStringContainsString( '320 g', $html );
		$this->assertStringContainsString( 'Colors', $html );
		$this->assertStringContainsString( 'Black, Gold', $html );
		// Description is listed first, so it is the active tab; specifications stays hidden.
		$this->assertMatchesRegularExpression( '/id="product-tabpanel-specifications"[^>]*hidden/', $html );
	}
}
