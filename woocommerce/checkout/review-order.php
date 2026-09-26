<?php
/**
 * Created: 2026-09-26 12:25 CEST
 * Role: Checkout order summary override (woocommerce/checkout/review-order.php), rendered inside
 *       `#order_review` (woocommerce/checkout/form-checkout.php's sidebar column).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the same compact "order summary" card as the cart page's sidebar
 *          (woocommerce/cart/cart-totals.php, sharing its `.checkout-summary` styles) — a small
 *          thumbnail/name/quantity/price row per real cart item, then subtotal/discount/shipping/
 *          tax/total — instead of WooCommerce's default table, reusing the exact same
 *          `wc_cart_totals_*_html()`/hook calls so the live AJAX total/shipping-method refresh this
 *          whole `#order_review` container already gets keeps working unmodified. Keeps the
 *          `woocommerce-checkout-review-order-table` class on its root element even though it is no
 *          longer a `<table>`: WooCommerce's own checkout.js targets that exact CSS selector to
 *          replace this template's output on every AJAX totals refresh, regardless of the actual
 *          markup underneath it.
 *
 * @package Solar_Template
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="checkout-summary woocommerce-checkout-review-order-table">
	<h2 class="checkout-summary__heading"><?php esc_html_e( 'Your order', 'solar-template' ); ?></h2>

	<div class="checkout-summary__items">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $solar_cart_item_key => $solar_cart_item ) {
			$solar_product = apply_filters( 'woocommerce_cart_item_product', $solar_cart_item['data'], $solar_cart_item, $solar_cart_item_key );
			$solar_visible = apply_filters( 'woocommerce_checkout_cart_item_visible', true, $solar_cart_item, $solar_cart_item_key );

			if ( ! $solar_product instanceof WC_Product || ! $solar_product->exists() || $solar_cart_item['quantity'] <= 0 || ! $solar_visible ) {
				continue;
			}
			?>
			<div class="checkout-summary__item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $solar_cart_item, $solar_cart_item_key ) ); ?>">
				<div class="checkout-summary__item-thumbnail">
					<?php echo wp_kses_post( $solar_product->get_image( 'thumbnail' ) ); ?>
				</div>
				<div class="checkout-summary__item-name">
					<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $solar_product->get_name(), $solar_cart_item, $solar_cart_item_key ) ); ?>
					<span class="checkout-summary__item-qty">
						<?php
						echo wp_kses_post(
							apply_filters(
								'woocommerce_checkout_cart_item_quantity',
								sprintf(
									/* translators: %s: quantity. */
									' &times; %s',
									$solar_cart_item['quantity']
								),
								$solar_cart_item,
								$solar_cart_item_key
							)
						);
						?>
					</span>
					<?php echo wp_kses_post( wc_get_formatted_cart_item_data( $solar_cart_item ) ); ?>
				</div>
				<div class="checkout-summary__item-total">
					<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $solar_product, $solar_cart_item['quantity'] ), $solar_cart_item, $solar_cart_item_key ) ); ?>
				</div>
			</div>
			<?php
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</div>

	<div class="checkout-summary__lines">
		<div class="checkout-summary__line">
			<span><?php esc_html_e( 'Subtotal', 'solar-template' ); ?></span>
			<span class="checkout-summary__value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $solar_code => $solar_coupon ) : ?>
			<div class="checkout-summary__line checkout-summary__line--discount cart-discount coupon-<?php echo esc_attr( sanitize_title( $solar_code ) ); ?>">
				<span><?php wc_cart_totals_coupon_label( $solar_coupon ); ?></span>
				<span class="checkout-summary__value"><?php wc_cart_totals_coupon_html( $solar_coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $solar_fee ) : ?>
			<div class="checkout-summary__line fee">
				<span><?php echo esc_html( $solar_fee->name ); ?></span>
				<span class="checkout-summary__value"><?php wc_cart_totals_fee_html( $solar_fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $solar_tax_code => $solar_tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<div class="checkout-summary__line tax-rate-<?php echo esc_attr( sanitize_title( $solar_tax_code ) ); ?>">
						<span><?php echo esc_html( $solar_tax->label ); ?></span>
						<span class="checkout-summary__value"><?php echo wp_kses_post( $solar_tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="checkout-summary__line">
					<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="checkout-summary__value"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
	</div>

	<div class="checkout-summary__total">
		<span><?php esc_html_e( 'Total incl. tax', 'solar-template' ); ?></span>
		<span class="checkout-summary__total-value"><?php wc_cart_totals_order_total_html(); ?></span>
	</div>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
</div>
