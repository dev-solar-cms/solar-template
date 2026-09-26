<?php
/**
 * Created: 2026-09-26 10:42 CEST
 * Role: Cart page "proceed to checkout" button override
 *       (woocommerce/cart/proceed-to-checkout-button.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Link to the real checkout URL with a label naming the next step the customer will
 *          actually see there (login for a guest, shipping for an already logged-in customer),
 *          rather than WooCommerce's generic "Proceed to checkout" — consistent with the tunnel's
 *          step indicator (Solar_Template\Checkout\StepIndicator).
 *
 * @package Solar_Template
 */

defined( 'ABSPATH' ) || exit;

$solar_next_step_label = is_user_logged_in()
	? __( 'Proceed to shipping', 'solar-template' )
	: __( 'Proceed to login', 'solar-template' );
?>
<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn--dark btn--block wc-forward">
	<?php echo esc_html( $solar_next_step_label ); ?> →
</a>
