<?php
/**
 * Created: 2026-09-28 17:00 CEST
 * Role: Unit test for Solar_Template\Product\ProductBadges.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert for_product() — the single source of truth shared by the product page panel and
 *          Catalog\ProductCardMapper (see each class' own docblock) — resolves every badge
 *          combination correctly, in priority order (New, Premium, Sale), with every applicable
 *          badge returned together rather than one suppressing another (David's explicit product
 *          decision, see the étape's own DECISIONS.md entry).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Product;

use PHPUnit\Framework\TestCase;
use Solar_Template\Product\ProductBadges;

final class ProductBadgesTest extends TestCase {

	/**
	 * @param bool $is_new     Whether the product should look recently published.
	 * @param bool $featured   Whether the product is marked "Featured".
	 * @param bool $on_sale    Whether the product is on sale.
	 * @return \WC_Product
	 */
	private function product( bool $is_new, bool $featured, bool $on_sale ): \WC_Product {
		$product = new \WC_Product( 1 );
		$product->solar_template_test_set_date_created( $is_new ? new \DateTime( '-1 day' ) : new \DateTime( '-30 days' ) );
		$product->solar_template_test_set_featured( $featured );
		$product->solar_template_test_set_on_sale( $on_sale );

		return $product;
	}

	/**
	 * @return void
	 */
	public function test_no_badge_applies(): void {
		$this->assertSame( array(), ProductBadges::for_product( $this->product( false, false, false ) ) );
	}

	/**
	 * @return void
	 */
	public function test_only_new_applies(): void {
		$badges = ProductBadges::for_product( $this->product( true, false, false ) );

		$this->assertSame( array( 'new' ), array_column( $badges, 'type' ) );
	}

	/**
	 * @return void
	 */
	public function test_only_premium_applies(): void {
		$badges = ProductBadges::for_product( $this->product( false, true, false ) );

		$this->assertSame( array( 'premium' ), array_column( $badges, 'type' ) );
	}

	/**
	 * @return void
	 */
	public function test_only_sale_applies(): void {
		$badges = ProductBadges::for_product( $this->product( false, false, true ) );

		$this->assertSame( array( 'sale' ), array_column( $badges, 'type' ) );
	}

	/**
	 * The combined case this whole étape exists for: a product simultaneously "New" and "On Sale"
	 * shows both badges together, in New-then-Sale priority order, rather than one replacing the
	 * other.
	 *
	 * @return void
	 */
	public function test_new_and_sale_coexist(): void {
		$badges = ProductBadges::for_product( $this->product( true, false, true ) );

		$this->assertSame( array( 'new', 'sale' ), array_column( $badges, 'type' ) );
	}

	/**
	 * @return void
	 */
	public function test_all_three_badges_coexist_in_priority_order(): void {
		$badges = ProductBadges::for_product( $this->product( true, true, true ) );

		$this->assertSame( array( 'new', 'premium', 'sale' ), array_column( $badges, 'type' ) );
	}

	/**
	 * @return void
	 */
	public function test_premium_and_sale_coexist_without_new(): void {
		$badges = ProductBadges::for_product( $this->product( false, true, true ) );

		$this->assertSame( array( 'premium', 'sale' ), array_column( $badges, 'type' ) );
	}

	/**
	 * A product with no publication date at all (WooCommerce's own "no date" case) is never "New",
	 * rather than erroring.
	 *
	 * @return void
	 */
	public function test_a_product_with_no_creation_date_is_never_new(): void {
		$product = new \WC_Product( 1 );
		$product->solar_template_test_set_date_created( null );

		$this->assertSame( array(), ProductBadges::for_product( $product ) );
	}

	/**
	 * @return void
	 */
	public function test_each_badge_carries_the_expected_label(): void {
		$badges = ProductBadges::for_product( $this->product( true, true, true ) );

		$labels = array_column( $badges, 'label', 'type' );

		$this->assertSame( 'New', $labels['new'] );
		$this->assertSame( 'Solar Premium', $labels['premium'] );
		$this->assertSame( 'On Sale', $labels['sale'] );
	}
}
