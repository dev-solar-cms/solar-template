<?php
/**
 * Created: 2026-09-26 12:00 CEST
 * Role: Checkout address fields layout (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Group WooCommerce's own real checkout fields (whatever a store has configured — core
 *          fields plus any third-party addition) into display rows of one or two fields each, so
 *          woocommerce/checkout/form-billing.php and form-shipping.php can render the design
 *          handoff's 2-column grid (first/last name, postcode/city, country/phone) without assuming
 *          a fixed, hardcoded set of fields — a field this theme doesn't know about still renders,
 *          full width, in WooCommerce's own relative order. Field keys are matched by their bare
 *          name (the part after a `billing_`/`shipping_` prefix), since both fieldsets share this
 *          same layout logic but use a different prefix for otherwise identical fields.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Groups a WooCommerce checkout field set into 1- or 2-column display rows.
 */
final class CheckoutFieldsLayout {

	/**
	 * Field key pairs shown side by side when both are present, in the design handoff's own order.
	 *
	 * @var array<int, array{0: string, 1: string}>
	 */
	private const PAIRS = array(
		array( 'first_name', 'last_name' ),
		array( 'postcode', 'city' ),
		array( 'country', 'phone' ),
	);

	/**
	 * Field display order the design handoff expects (name, then address, then postcode/city, then
	 * country/phone last) — WooCommerce's own default priorities put the country select right after
	 * the name fields (needed early for tax/shipping calculation purposes), which would otherwise
	 * pair it with "phone" ahead of the address fields instead of after them.
	 *
	 * @var array<string, int>
	 */
	private const PRIORITIES = array(
		'address_1' => 30,
		'address_2' => 40,
		'postcode'  => 50,
		'city'      => 51,
		'state'     => 60,
		'country'   => 70,
		'phone'     => 80,
	);

	/**
	 * Groups $fields into display rows.
	 *
	 * @param array $fields Checkout fields, as returned by WC_Checkout::get_checkout_fields( 'billing' ).
	 * @return array<int, array<int, array{key: string, field: array}>> Rows, each a list of 1 or 2
	 *                                                                   {key, field} columns.
	 */
	public static function rows( array $fields ): array {
		$bare_name_to_key = array();

		foreach ( array_keys( $fields ) as $key ) {
			$bare_name_to_key[ self::bare_name( $key ) ] = $key;
		}

		$partner_of = array();

		foreach ( self::PAIRS as [ $first_bare_name, $second_bare_name ] ) {
			if ( isset( $bare_name_to_key[ $first_bare_name ], $bare_name_to_key[ $second_bare_name ] ) ) {
				$first_key  = $bare_name_to_key[ $first_bare_name ];
				$second_key = $bare_name_to_key[ $second_bare_name ];

				$partner_of[ $first_key ]  = $second_key;
				$partner_of[ $second_key ] = $first_key;
			}
		}

		$rows     = array();
		$consumed = array();

		foreach ( array_keys( $fields ) as $key ) {
			if ( isset( $consumed[ $key ] ) ) {
				continue;
			}

			$partner_key = $partner_of[ $key ] ?? null;

			if ( null !== $partner_key && ! isset( $consumed[ $partner_key ] ) ) {
				$rows[]                   = array(
					array(
						'key'   => $key,
						'field' => $fields[ $key ],
					),
					array(
						'key'   => $partner_key,
						'field' => $fields[ $partner_key ],
					),
				);
				$consumed[ $key ]         = true;
				$consumed[ $partner_key ] = true;
			} else {
				$rows[]           = array(
					array(
						'key'   => $key,
						'field' => $fields[ $key ],
					),
				);
				$consumed[ $key ] = true;
			}
		}

		return $rows;
	}

	/**
	 * Reassigns the priority of every billing/shipping field self::PRIORITIES knows about, so the
	 * checkout address form's field order matches the design handoff regardless of WooCommerce's own
	 * defaults. Hooked to the `woocommerce_checkout_fields` filter (Solar_Template\Theme::boot()).
	 *
	 * @param array $fields Full checkout fields array (billing/shipping/account/order), as passed by
	 *                       the `woocommerce_checkout_fields` filter.
	 * @return array $fields with billing/shipping field priorities reassigned.
	 */
	public static function reorder_address_fields( array $fields ): array {
		foreach ( array( 'billing', 'shipping' ) as $fieldset ) {
			if ( ! isset( $fields[ $fieldset ] ) ) {
				continue;
			}

			foreach ( $fields[ $fieldset ] as $key => $field ) {
				$priority = self::PRIORITIES[ self::bare_name( $key ) ] ?? null;

				if ( null !== $priority ) {
					$fields[ $fieldset ][ $key ]['priority'] = $priority;
				}
			}
		}

		return $fields;
	}

	/**
	 * Strips a field key's `billing_`/`shipping_` fieldset prefix, if any, so both fieldsets can be
	 * matched against the same self::PAIRS list.
	 *
	 * @param string $key Field key, e.g. `billing_first_name`.
	 * @return string e.g. `first_name`.
	 */
	private static function bare_name( string $key ): string {
		return preg_replace( '/^(billing|shipping)_/', '', $key );
	}
}
