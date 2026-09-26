<?php
/**
 * Created: 2026-09-26 12:20 CEST
 * Role: Shipping method selector override (woocommerce/cart/cart-shipping.php), shared by the cart
 *       sidebar (woocommerce/cart/cart-totals.php) and the checkout order summary
 *       (woocommerce/checkout/review-order.php) via `wc_cart_totals_shipping_html()`.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a store's real, configured shipping methods (e.g. Standard/Express flat rates) as
 *          radio cards instead of WooCommerce's default `<tr>`/list markup — this theme's own
 *          `cart-totals.php`/`review-order.php` overrides are already `<div>`-based (not a
 *          `<table>`), so this override also drops the default's `<tr>` root, which would otherwise
 *          be invalid markup floating outside of any table. Keeps every native field name/id and
 *          the `woocommerce_after_shipping_rate` hook WooCommerce's own checkout AJAX (`update_checkout`
 *          on a `.shipping_method` change) depends on.
 *
 * @package Solar_Template
 */

defined( 'ABSPATH' ) || exit;

$solar_formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$solar_has_calculated_shipping  = ! empty( $has_calculated_shipping );
$solar_show_shipping_calculator = ! empty( $show_shipping_calculator );
$solar_calculator_text          = '';
?>
<div class="checkout-summary__line checkout-summary__line--shipping-methods">
	<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
		<div class="shipping-methods" role="radiogroup" aria-label="<?php echo esc_attr( wp_strip_all_tags( $package_name ) ); ?>">
			<?php foreach ( $available_methods as $solar_method ) : ?>
				<label class="shipping-methods__option<?php echo esc_attr( $solar_method->id === $chosen_method ? ' is-selected' : '' ); ?>">
					<?php
					if ( count( $available_methods ) > 1 ) {
						printf(
							'<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />',
							esc_attr( $index ),
							esc_attr( sanitize_title( $solar_method->id ) ),
							esc_attr( $solar_method->id ),
							checked( $solar_method->id, $chosen_method, false )
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values already escaped above.
					} else {
						printf(
							'<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />',
							esc_attr( $index ),
							esc_attr( sanitize_title( $solar_method->id ) ),
							esc_attr( $solar_method->id )
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values already escaped above.
					}
					?>
					<span class="shipping-methods__label"><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $solar_method ) ); ?></span>
					<?php do_action( 'woocommerce_after_shipping_rate', $solar_method, $index ); ?>
				</label>
			<?php endforeach; ?>
		</div>
		<?php if ( is_cart() ) : ?>
			<p class="woocommerce-shipping-destination">
				<?php
				if ( $solar_formatted_destination ) {
					printf(
						/* translators: %s: shipping destination. */
						esc_html__( 'Shipping to %s.', 'solar-template' ) . ' ',
						'<strong>' . esc_html( $solar_formatted_destination ) . '</strong>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped above.
					);
					$solar_calculator_text = esc_html__( 'Change address', 'solar-template' );
				} else {
					echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', __( 'Shipping options will be updated during checkout.', 'solar-template' ) ) );
				}
				?>
			</p>
		<?php endif; ?>
	<?php elseif ( ! $solar_has_calculated_shipping || ! $solar_formatted_destination ) : ?>
		<?php if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<?php echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'solar-template' ) ) ); ?>
		<?php else : ?>
			<?php echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'solar-template' ) ) ); ?>
		<?php endif; ?>
	<?php elseif ( ! is_cart() ) : ?>
		<?php echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'solar-template' ) ) ); ?>
	<?php else : ?>
		<?php
		echo wp_kses_post(
			apply_filters(
				'woocommerce_cart_no_shipping_available_html',
				/* translators: %s: shipping destination. */
				sprintf( esc_html__( 'No shipping options were found for %s.', 'solar-template' ) . ' ', '<strong>' . esc_html( $solar_formatted_destination ) . '</strong>' ),
				$solar_formatted_destination
			)
		);
		$solar_calculator_text = esc_html__( 'Enter a different address', 'solar-template' );
		?>
	<?php endif; ?>

	<?php if ( $solar_show_shipping_calculator ) : ?>
		<?php woocommerce_shipping_calculator( $solar_calculator_text ); ?>
	<?php endif; ?>
</div>
