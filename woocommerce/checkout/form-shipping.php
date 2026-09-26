<?php
/**
 * Created: 2026-09-26 12:15 CEST
 * Role: Checkout "ship to a different address" + order notes override
 *       (woocommerce/checkout/form-shipping.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Style the optional "ship to a different address" toggle (most orders ship to the
 *          billing address entered in form-billing.php, so this stays a collapsed opt-in rather
 *          than a second address form always on screen) and the order notes field, keeping every
 *          native field/hook from WooCommerce's own template.
 *
 * @package Solar_Template
 * @global WC_Checkout $checkout
 */

use Solar_Template\Checkout\CheckoutFieldsLayout;

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<label class="checkout-address__different-address-toggle">
			<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" />
			<span><?php esc_html_e( 'Ship to a different address?', 'solar-template' ); ?></span>
		</label>

		<div class="shipping_address checkout-address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="checkout-address__rows">
				<?php foreach ( CheckoutFieldsLayout::rows( $checkout->get_checkout_fields( 'shipping' ) ) as $solar_row ) : ?>
					<div class="checkout-address__row checkout-address__row--<?php echo 1 === count( $solar_row ) ? 'single' : 'double'; ?>">
						<?php foreach ( $solar_row as $solar_column ) : ?>
							<?php woocommerce_form_field( $solar_column['key'], $solar_column['field'], $checkout->get_value( $solar_column['key'] ) ); ?>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

	<?php endif; ?>
</div>
<div class="woocommerce-additional-fields">
	<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

	<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>

		<div class="checkout-address__rows">
			<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $solar_key => $solar_field ) : ?>
				<?php woocommerce_form_field( $solar_key, $solar_field, $checkout->get_value( $solar_key ) ); ?>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</div>
