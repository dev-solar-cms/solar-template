<?php
/**
 * Created: 2026-09-26 10:45 CEST
 * Role: Cart page order summary sidebar override (woocommerce/cart/cart-totals.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the sticky order summary (subtotal, applied coupons, shipping, tax, total, the
 *          "proceed to checkout" button, payment icons and an SSL note), reusing WooCommerce's own
 *          `wc_cart_totals_*_html()` helpers so every value (and the AJAX shipping-calculator
 *          refresh already wired to the `.cart_totals` wrapper by WooCommerce core) keeps working
 *          exactly like the default template — only the markup/classes around them are the
 *          theme's own.
 *
 * @package Solar_Template
 */

use Solar_Template\Footer\Footer;

defined( 'ABSPATH' ) || exit;
?>
<div class="cart_totals checkout-summary <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2 class="checkout-summary__heading"><?php esc_html_e( 'Order summary', 'solar-template' ); ?></h2>

	<div class="checkout-summary__lines">
		<div class="checkout-summary__line">
			<span>
				<?php
				$solar_cart_count = null !== WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
				echo esc_html(
					sprintf(
						/* translators: %d: number of items in the cart. */
						_n( 'Subtotal (%d item)', 'Subtotal (%d items)', $solar_cart_count, 'solar-template' ),
						$solar_cart_count
					)
				);
				?>
			</span>
			<span class="checkout-summary__value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="checkout-summary__line checkout-summary__line--discount cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="checkout-summary__value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<div class="checkout-summary__line">
				<span><?php esc_html_e( 'Shipping', 'solar-template' ); ?></span>
				<span class="checkout-summary__value"><?php woocommerce_shipping_calculator(); ?></span>
			</div>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="checkout-summary__line fee">
				<span><?php echo esc_html( $fee->name ); ?></span>
				<span class="checkout-summary__value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<div class="checkout-summary__line tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<span><?php echo esc_html( $tax->label ); ?></span>
						<span class="checkout-summary__value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="checkout-summary__line">
					<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="checkout-summary__value"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
	</div>

	<div class="checkout-summary__total">
		<span><?php esc_html_e( 'Total incl. tax', 'solar-template' ); ?></span>
		<span class="checkout-summary__total-value"><?php wc_cart_totals_order_total_html(); ?></span>
	</div>

	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	<div class="checkout-summary__cta">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<div class="checkout-summary__payment-icons">
		<?php foreach ( Footer::payment_icons() as $solar_payment_icon ) : ?>
			<span class="checkout-summary__payment-icon"><?php echo esc_html( $solar_payment_icon ); ?></span>
		<?php endforeach; ?>
	</div>

	<div class="checkout-summary__secure-note">
		<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
		<?php esc_html_e( 'SSL 256-bit secure payment', 'solar-template' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
