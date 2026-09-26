<?php
/**
 * Created: 2026-09-26 12:10 CEST
 * Role: Checkout address fields override (woocommerce/checkout/form-billing.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the store's real checkout fields (whatever WooCommerce/a plugin has configured)
 *          in the design handoff's 2-column grid, grouped by
 *          Solar_Template\Checkout\CheckoutFieldsLayout::rows() — every field keeps rendering
 *          through `woocommerce_form_field()`, WooCommerce's own field markup/name/validation
 *          attributes untouched, only the surrounding grid/row wrapper is this theme's own.
 *
 * @package Solar_Template
 * @global WC_Checkout $checkout
 */

use Solar_Template\Checkout\CheckoutFieldsLayout;

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-billing-fields checkout-address">
	<h1 class="checkout-step__title checkout-step__title--left"><?php esc_html_e( 'Shipping address', 'solar-template' ); ?></h1>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="checkout-address__rows">
		<?php foreach ( CheckoutFieldsLayout::rows( $checkout->get_checkout_fields( 'billing' ) ) as $solar_row ) : ?>
			<div class="checkout-address__row checkout-address__row--<?php echo 1 === count( $solar_row ) ? 'single' : 'double'; ?>">
				<?php foreach ( $solar_row as $solar_column ) : ?>
					<?php woocommerce_form_field( $solar_column['key'], $solar_column['field'], $checkout->get_value( $solar_column['key'] ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields checkout-address__account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'solar-template' ); ?></span>
				</label>
			</p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
			<div class="create-account checkout-address__rows">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $solar_key => $solar_field ) : ?>
					<?php woocommerce_form_field( $solar_key, $solar_field, $checkout->get_value( $solar_key ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
