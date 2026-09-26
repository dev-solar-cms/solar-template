<?php
/**
 * Created: 2026-09-26 14:20 CEST
 * Role: Single payment method row override (woocommerce/checkout/payment-method.php), rendered by
 *       woocommerce/checkout/payment.php once per real, available WooCommerce gateway.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Style a gateway's radio input as a card (matching the shipping method radio cards from
 *          woocommerce/cart/cart-shipping.php) with a relevant icon
 *          (Solar_Template\Checkout\PaymentGatewayIcons) instead of WooCommerce's default bare
 *          `<li>` — every native field name/id and the `payment_box` fields container a gateway's
 *          own `payment_fields()` renders into stay exactly as WooCommerce's own checkout.js
 *          expects them.
 *
 * @package Solar_Template
 */

use Solar_Template\Checkout\PaymentGatewayIcons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$solar_icon = PaymentGatewayIcons::for_gateway( $gateway->id );
?>
<div class="payment-methods__option<?php echo $gateway->chosen ? ' is-selected' : ''; ?> wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
	<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
		<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
		<span class="payment-methods__label"><?php echo wp_kses_post( $gateway->get_title() ); ?></span>
		<?php if ( $solar_icon ) : ?>
			<span class="payment-methods__icon" aria-hidden="true"><?php echo $solar_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, theme-defined SVG set, never built from filtered/user-controlled markup (see PaymentGatewayIcons). ?></span>
		<?php endif; ?>
	</label>
	<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>"<?php echo $gateway->chosen ? '' : ' style="display:none;"'; ?>>
			<?php $gateway->payment_fields(); ?>
		</div>
	<?php endif; ?>
</div>
