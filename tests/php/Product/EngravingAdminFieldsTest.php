<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Unit test for Solar_Template\Product\EngravingAdminFields.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Confirm save() persists exactly the submitted fields onto the product with no independent
 *          nonce check of its own — by design, it trusts the native `woocommerce_process_product_meta`
 *          hook it's exclusively registered on (Solar_Template\Theme::boot()) to have already verified
 *          one; that trust boundary itself is enforced by
 *          tests/php/Security/NonceTrustSweepTest.php, not re-implemented here.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Product;

use PHPUnit\Framework\TestCase;
use Solar_Template\Product\EngravingAdminFields;
use Solar_Template\Product\ProductEngraving;

final class EngravingAdminFieldsTest extends TestCase {

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$_POST                                   = array();
		$GLOBALS['solar_template_test_products'] = array();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_save_persists_every_submitted_field_onto_the_product(): void {
		$product                                     = new \WC_Product( 42 );
		$GLOBALS['solar_template_test_products'][42] = $product;

		$_POST[ ProductEngraving::META_ENABLED ]    = '1';
		$_POST[ ProductEngraving::META_PRICE ]      = '30';
		$_POST[ ProductEngraving::META_MAX_LENGTH ] = '15';

		EngravingAdminFields::save( 42 );

		$this->assertSame( 'yes', $product->get_meta( ProductEngraving::META_ENABLED ) );
		$this->assertSame( '30', $product->get_meta( ProductEngraving::META_PRICE ) );
		$this->assertSame( 15, $product->get_meta( ProductEngraving::META_MAX_LENGTH ) );
	}

	/**
	 * The checkbox meta is explicitly set to 'no' when the field is absent from the request — a
	 * checkbox that's unchecked is simply never submitted by a browser, so save() must not leave a
	 * stale 'yes' value in place.
	 *
	 * @return void
	 */
	public function test_save_disables_engraving_when_the_checkbox_is_not_submitted(): void {
		$product = new \WC_Product( 42 );
		$product->solar_template_test_set_meta( ProductEngraving::META_ENABLED, 'yes' );
		$GLOBALS['solar_template_test_products'][42] = $product;

		EngravingAdminFields::save( 42 );

		$this->assertSame( 'no', $product->get_meta( ProductEngraving::META_ENABLED ) );
	}

	/**
	 * @return void
	 */
	public function test_save_does_nothing_when_the_product_cannot_be_found(): void {
		$_POST[ ProductEngraving::META_ENABLED ] = '1';

		// wc_get_product() returns false for an unregistered ID — save() must return without error.
		EngravingAdminFields::save( 999 );

		$this->assertArrayNotHasKey( 999, $GLOBALS['solar_template_test_products'] );
	}
}
