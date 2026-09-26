<?php
/**
 * Created: 2026-09-26 09:10 CEST
 * Role: Sales tunnel step indicator template-part (template-parts/checkout/step-indicator.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the 5-circle/label/connecting-line indicator shared by every page of the tunnel,
 *          fed by Solar_Template\Checkout\StepIndicator::steps(). A done step's circle is a link
 *          back to that real page (cart/checkout); the login/shipping/payment steps are buttons
 *          instead, since assets/js/checkout.js moves between them on the single checkout page
 *          without a reload.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type string $current_step One of Solar_Template\Checkout\StepIndicator's step keys.
 * }
 */

use Solar_Template\Checkout\StepIndicator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$step_indicator_args = wp_parse_args( $args ?? array(), array( 'current_step' => 'cart' ) );
$steps               = StepIndicator::steps( $step_indicator_args['current_step'] );
$cart_url            = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
$checkout_url        = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' );
$step_urls           = array(
	'cart'     => $cart_url,
	'login'    => $checkout_url,
	'shipping' => $checkout_url,
	'payment'  => $checkout_url,
);
?>
<nav class="checkout-steps" aria-label="<?php esc_attr_e( 'Checkout progress', 'solar-template' ); ?>">
	<ol class="checkout-steps__list">
		<?php foreach ( $steps as $step_index => $step ) : ?>
			<li class="checkout-steps__item checkout-steps__item--<?php echo esc_attr( $step['state'] ); ?>" data-step="<?php echo esc_attr( $step['key'] ); ?>">
				<?php if ( 'done' === $step['state'] && isset( $step_urls[ $step['key'] ] ) ) : ?>
					<a class="checkout-steps__circle" href="<?php echo esc_url( $step_urls[ $step['key'] ] ); ?>" data-goto-step="<?php echo esc_attr( $step['key'] ); ?>">
						<span aria-hidden="true">✓</span>
						<span class="screen-reader-text"><?php echo esc_html( $step['label'] ); ?></span>
					</a>
				<?php else : ?>
					<span class="checkout-steps__circle" data-goto-step="<?php echo esc_attr( $step['key'] ); ?>">
						<?php echo esc_html( (string) $step['number'] ); ?>
					</span>
				<?php endif; ?>
				<span class="checkout-steps__label"><?php echo esc_html( $step['label'] ); ?></span>
			</li>
			<?php if ( $step_index < count( $steps ) - 1 ) : ?>
				<li class="checkout-steps__connector checkout-steps__connector--<?php echo esc_attr( 'done' === $step['state'] ? 'done' : 'pending' ); ?>" aria-hidden="true"></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ol>
</nav>
