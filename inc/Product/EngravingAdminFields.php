<?php
/**
 * Created: 2026-09-25 17:10 CEST
 * Role: Custom engraving admin fields (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Add a "Custom engraving" toggle, surcharge price and max-length field to the standard
 *          WooCommerce "Product data" > General panel of the product edit screen (native WordPress/
 *          WooCommerce admin field helpers, no ACF/custom meta box framework), and persist them as
 *          plain product meta on save.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders and saves the product edit screen's custom-engraving fields.
 */
final class EngravingAdminFields {

	/**
	 * Renders the fields, hooked to `woocommerce_product_options_general_product_data`.
	 *
	 * @return void
	 */
	public static function render(): void {
		global $product_object;

		$product = $product_object instanceof \WC_Product ? $product_object : null;

		echo '<div class="options_group">';

		woocommerce_wp_checkbox(
			array(
				'id'          => ProductEngraving::META_ENABLED,
				'label'       => __( 'Custom engraving', 'solar-template' ),
				'description' => __( 'Let customers add custom engraving text to this product, for an extra price.', 'solar-template' ),
				'value'       => $product && ProductEngraving::is_enabled( $product ) ? 'yes' : 'no',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => ProductEngraving::META_PRICE,
				/* translators: %s: currency symbol. */
				'label'       => sprintf( __( 'Engraving price (%s)', 'solar-template' ), get_woocommerce_currency_symbol() ),
				'data_type'   => 'price',
				'value'       => $product ? $product->get_meta( ProductEngraving::META_PRICE, true ) : '',
				'placeholder' => (string) ProductEngraving::DEFAULT_PRICE,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => ProductEngraving::META_MAX_LENGTH,
				'label'             => __( 'Max. engraving length (characters)', 'solar-template' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
				'value'             => $product ? $product->get_meta( ProductEngraving::META_MAX_LENGTH, true ) : '',
				'placeholder'       => (string) ProductEngraving::DEFAULT_MAX_LENGTH,
			)
		);

		echo '</div>';
	}

	/**
	 * Persists the fields, hooked to `woocommerce_process_product_meta`.
	 *
	 * `woocommerce_process_product_meta` only ever fires from WooCommerce's own product edit-screen
	 * save handler, which has already verified its own nonce before calling it.
	 *
	 * @param int $post_id Product (post) ID being saved.
	 * @return void
	 */
	public static function save( int $post_id ): void {
		$product = wc_get_product( $post_id );

		if ( ! $product ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- see this method's docblock.
		$product->update_meta_data( ProductEngraving::META_ENABLED, isset( $_POST[ ProductEngraving::META_ENABLED ] ) ? 'yes' : 'no' );

		if ( isset( $_POST[ ProductEngraving::META_PRICE ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see this method's docblock.
			$product->update_meta_data( ProductEngraving::META_PRICE, wc_format_decimal( wp_unslash( $_POST[ ProductEngraving::META_PRICE ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( isset( $_POST[ ProductEngraving::META_MAX_LENGTH ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see this method's docblock.
			$product->update_meta_data( ProductEngraving::META_MAX_LENGTH, absint( wp_unslash( $_POST[ ProductEngraving::META_MAX_LENGTH ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		$product->save();
	}
}
