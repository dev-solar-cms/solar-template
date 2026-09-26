<?php
/**
 * Created: 2026-09-26 17:45 CEST
 * Role: Regression test for template-parts/account/wishlist.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the wishlist template-part with plain, already-computed data and assert on the
 *          resulting markup: product count, reused product cards, and the empty state.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class AccountWishlistTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/account/wishlist.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A non-empty wishlist renders the product count and one product card per product.
	 *
	 * @return void
	 */
	public function test_renders_product_count_and_cards(): void {
		$html = $this->render(
			array(
				'products' => array(
					array(
						'product_id' => 41,
						'name'       => 'Écharpe Cachemire',
						'permalink'  => 'https://example.test/?p=41',
					),
					array(
						'product_id' => 40,
						'name'       => 'Montre Sport',
						'permalink'  => 'https://example.test/?p=40',
					),
				),
			)
		);

		$this->assertStringContainsString( '(2)', $html );
		$this->assertStringContainsString( 'Écharpe Cachemire', $html );
		$this->assertStringContainsString( 'Montre Sport', $html );
		$this->assertStringContainsString( 'data-product-id="41"', $html );
		$this->assertStringContainsString( 'account-wishlist__grid', $html );
	}

	/**
	 * An empty wishlist shows the empty-state message instead of a grid.
	 *
	 * @return void
	 */
	public function test_shows_empty_state_with_no_products(): void {
		$html = $this->render( array( 'products' => array() ) );

		$this->assertStringContainsString( 'You have not saved any product yet.', $html );
		$this->assertStringNotContainsString( 'account-wishlist__grid', $html );
	}
}
