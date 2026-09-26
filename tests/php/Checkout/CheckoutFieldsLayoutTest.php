<?php
/**
 * Created: 2026-09-26 12:05 CEST
 * Role: Unit test for Solar_Template\Checkout\CheckoutFieldsLayout.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert known field pairs are grouped together (matched by their bare name, regardless of
 *          the real `billing_`/`shipping_` prefix WooCommerce's own field keys carry), unknown/
 *          unpaired fields still render full width in their original order, and a field missing its
 *          usual partner degrades to a full-width row instead of being dropped or crashing.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Checkout;

use PHPUnit\Framework\TestCase;
use Solar_Template\Checkout\CheckoutFieldsLayout;

final class CheckoutFieldsLayoutTest extends TestCase {

	/**
	 * Builds a minimal fake field definition, only the shape CheckoutFieldsLayout itself cares
	 * about (it never reads into the field array — it just carries it through).
	 *
	 * @param string $label Field label, only used to make assertions readable.
	 * @return array{label: string}
	 */
	private function field( string $label ): array {
		return array( 'label' => $label );
	}

	/**
	 * Known pairs (first/last name, postcode/city, country/phone) are grouped into one row each
	 * even though WooCommerce's real field keys carry a `billing_` prefix, an unpaired field in
	 * between stays on its own full-width row, in the original order.
	 *
	 * @return void
	 */
	public function test_groups_known_pairs_and_keeps_others_full_width(): void {
		$fields = array(
			'billing_first_name' => $this->field( 'First name' ),
			'billing_last_name'  => $this->field( 'Last name' ),
			'billing_address_1'  => $this->field( 'Address' ),
			'billing_postcode'   => $this->field( 'Postcode' ),
			'billing_city'       => $this->field( 'City' ),
			'billing_country'    => $this->field( 'Country' ),
			'billing_phone'      => $this->field( 'Phone' ),
		);

		$rows = CheckoutFieldsLayout::rows( $fields );

		$this->assertSame(
			array(
				array( 'billing_first_name', 'billing_last_name' ),
				array( 'billing_address_1' ),
				array( 'billing_postcode', 'billing_city' ),
				array( 'billing_country', 'billing_phone' ),
			),
			array_map(
				static fn( $row ) => array_column( $row, 'key' ),
				$rows
			)
		);
	}

	/**
	 * The same pairing also applies to the "shipping" fieldset's own `shipping_`-prefixed keys.
	 *
	 * @return void
	 */
	public function test_groups_known_pairs_for_the_shipping_prefix_too(): void {
		$fields = array(
			'shipping_first_name' => $this->field( 'First name' ),
			'shipping_last_name'  => $this->field( 'Last name' ),
		);

		$rows = CheckoutFieldsLayout::rows( $fields );

		$this->assertSame(
			array( array( 'shipping_first_name', 'shipping_last_name' ) ),
			array_map(
				static fn( $row ) => array_column( $row, 'key' ),
				$rows
			)
		);
	}

	/**
	 * A field whose usual partner is absent (e.g. a store without a "phone" field) renders alone,
	 * full width, rather than being paired with something unrelated or dropped.
	 *
	 * @return void
	 */
	public function test_field_without_its_partner_renders_full_width(): void {
		$fields = array(
			'billing_first_name' => $this->field( 'First name' ),
			'billing_country'    => $this->field( 'Country' ),
		);

		$rows = CheckoutFieldsLayout::rows( $fields );

		$this->assertSame(
			array( array( 'billing_first_name' ), array( 'billing_country' ) ),
			array_map(
				static fn( $row ) => array_column( $row, 'key' ),
				$rows
			)
		);
	}

	/**
	 * An unrecognized field (a custom field a store/plugin added) still renders, full width, in its
	 * original relative position.
	 *
	 * @return void
	 */
	public function test_unknown_field_still_renders_full_width_in_place(): void {
		$fields = array(
			'billing_first_name' => $this->field( 'First name' ),
			'billing_last_name'  => $this->field( 'Last name' ),
			'billing_vat_number' => $this->field( 'VAT number' ),
			'billing_address_1'  => $this->field( 'Address' ),
		);

		$rows = CheckoutFieldsLayout::rows( $fields );

		$this->assertSame(
			array(
				array( 'billing_first_name', 'billing_last_name' ),
				array( 'billing_vat_number' ),
				array( 'billing_address_1' ),
			),
			array_map(
				static fn( $row ) => array_column( $row, 'key' ),
				$rows
			)
		);
	}

	/**
	 * An empty field set returns no rows.
	 *
	 * @return void
	 */
	public function test_empty_fields_return_no_rows(): void {
		$this->assertSame( array(), CheckoutFieldsLayout::rows( array() ) );
	}

	/**
	 * reorder_address_fields() reassigns billing/shipping address field priorities so that, sorted
	 * by priority, they read name, address, postcode/city, country/phone — matching the design
	 * handoff rather than WooCommerce's own default order (country right after the name fields).
	 *
	 * @return void
	 */
	public function test_reorder_address_fields_puts_country_and_phone_after_the_address(): void {
		$fields = array(
			'billing'  => array(
				'billing_first_name' => array_merge( $this->field( 'First name' ), array( 'priority' => 10 ) ),
				'billing_last_name'  => array_merge( $this->field( 'Last name' ), array( 'priority' => 20 ) ),
				'billing_country'    => array_merge( $this->field( 'Country' ), array( 'priority' => 40 ) ),
				'billing_address_1'  => array_merge( $this->field( 'Address' ), array( 'priority' => 50 ) ),
				'billing_address_2'  => array_merge( $this->field( 'Address 2' ), array( 'priority' => 60 ) ),
				'billing_postcode'   => array_merge( $this->field( 'Postcode' ), array( 'priority' => 65 ) ),
				'billing_city'       => array_merge( $this->field( 'City' ), array( 'priority' => 70 ) ),
				'billing_phone'      => array_merge( $this->field( 'Phone' ), array( 'priority' => 100 ) ),
			),
			'shipping' => array(
				'shipping_country' => array_merge( $this->field( 'Country' ), array( 'priority' => 40 ) ),
			),
			'account'  => array(
				'account_password' => $this->field( 'Password' ),
			),
		);

		$reordered = CheckoutFieldsLayout::reorder_address_fields( $fields );

		$billing_keys_by_priority = $reordered['billing'];
		uasort( $billing_keys_by_priority, static fn( $a, $b ) => $a['priority'] <=> $b['priority'] );

		$this->assertSame(
			array(
				'billing_first_name',
				'billing_last_name',
				'billing_address_1',
				'billing_address_2',
				'billing_postcode',
				'billing_city',
				'billing_country',
				'billing_phone',
			),
			array_keys( $billing_keys_by_priority )
		);
		// The shipping fieldset's own country field is reordered the same way.
		$this->assertSame( 70, $reordered['shipping']['shipping_country']['priority'] );
		// A fieldset this class doesn't touch (account) is left completely untouched.
		$this->assertSame( $fields['account'], $reordered['account'] );
	}
}
