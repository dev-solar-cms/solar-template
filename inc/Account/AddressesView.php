<?php
/**
 * Created: 2026-09-26 17:55 CEST
 * Role: My Account addresses view-model (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the billing/shipping address summary cards shown above the address edit form,
 *          from the logged-in customer's real, already-saved addresses.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the address summary cards for the edit-address page.
 */
final class AddressesView {

	/**
	 * @param int    $user_id     Logged-in customer's user ID.
	 * @param string $active_type Address type currently being edited ('billing' or 'shipping').
	 * @return array<int, array{type: string, label: string, name: string, formatted_address: string, is_empty: bool, is_active: bool, edit_url: string}>
	 */
	public static function summaries( int $user_id, string $active_type ): array {
		$customer = new \WC_Customer( $user_id );

		$types = array(
			'billing'  => __( 'Billing address', 'solar-template' ),
			'shipping' => __( 'Shipping address', 'solar-template' ),
		);

		$summaries = array();

		foreach ( $types as $type => $label ) {
			$first_name_getter = "get_{$type}_first_name";
			$last_name_getter  = "get_{$type}_last_name";
			$name              = trim( $customer->$first_name_getter() . ' ' . $customer->$last_name_getter() );
			$formatted_address = wc_get_account_formatted_address( $type, $user_id );

			$summaries[] = array(
				'type'              => $type,
				'label'             => $label,
				'name'              => $name,
				'formatted_address' => $formatted_address,
				'is_empty'          => '' === $formatted_address,
				'is_active'         => $type === $active_type,
				'edit_url'          => wc_get_endpoint_url( 'edit-address', $type, wc_get_page_permalink( 'myaccount' ) ),
			);
		}

		return $summaries;
	}
}
