<?php
/**
 * Created: 2026-09-26 14:15 CEST
 * Role: Checkout payment section override (woocommerce/checkout/payment.php), rendered by a direct
 *       call to `woocommerce_checkout_payment()` from woocommerce/checkout/form-checkout.php's own
 *       "payment" step panel (not through the default `woocommerce_checkout_order_review` hook,
 *       detached in Solar_Template\Checkout\CheckoutController::detach_default_checkout_hooks()).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the store's real, configured payment gateways as styled radio cards
 *          (woocommerce/checkout/payment-method.php) instead of WooCommerce's default bare list,
 *          followed by the terms notice and the real "place order" submit button — every native
 *          field name/hook/nonce WooCommerce's own checkout AJAX depends on stays exactly in
 *          place, only the surrounding markup changes.
 *
 * @package Solar_Template
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<h1 class="checkout-step__title checkout-step__title--left"><?php esc_html_e( 'Payment', 'solar-template' ); ?></h1>

<div id="payment" class="woocommerce-checkout-payment checkout-payment">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<?php if ( ! empty( $available_gateways ) ) : ?>
			<div class="payment-methods" role="radiogroup" aria-label="<?php echo esc_attr__( 'Payment methods', 'solar-template' ); ?>">
				<?php
				foreach ( $available_gateways as $solar_gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $solar_gateway ) );
				}
				?>
			</div>
		<?php else : ?>
			<p class="checkout-payment__notice">
				<?php
				echo esc_html(
					apply_filters(
						'woocommerce_no_available_payment_methods_message',
						WC()->customer->get_billing_country()
							? __( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'solar-template' )
							: __( 'Please fill in your details above to see available payment methods.', 'solar-template' )
					)
				);
				?>
			</p>
		<?php endif; ?>
	<?php else : ?>
		<p class="checkout-payment__notice"><?php esc_html_e( 'This order requires no payment.', 'solar-template' ); ?></p>
	<?php endif; ?>

	<div class="checkout-payment__secure-note">
		<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
		<?php esc_html_e( 'Your data is protected by SSL 256-bit encryption.', 'solar-template' ); ?>
	</div>

	<div class="checkout-step__actions">
		<button type="button" class="btn btn--outline" data-goto-step="shipping">← <?php esc_html_e( 'Back', 'solar-template' ); ?></button>

		<div class="checkout-payment__submit">
			<noscript>
				<?php
				printf(
					/* translators: $1 and $2 opening and closing emphasis tags respectively */
					esc_html__( 'If JavaScript is disabled in your browser, click %1$sUpdate Totals%2$s before placing your order — you may otherwise be charged more than the amount shown above.', 'solar-template' ),
					'<em>',
					'</em>'
				);
				?>
				<br/><button type="submit" class="btn btn--outline" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'solar-template' ); ?>"><?php esc_html_e( 'Update totals', 'solar-template' ); ?></button>
			</noscript>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="btn btn--primary btn--lg checkout-payment__place-order" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered core button HTML, already escaped above. ?>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
	</div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
