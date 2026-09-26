<?php
/**
 * Created: 2026-09-26 15:35 CEST
 * Role: Order confirmation page override (woocommerce/checkout/thankyou.php), rendered on the
 *       "confirmation" step of the sales tunnel (Solar_Template\Checkout\CheckoutController::
 *       current_step() resolves it via WooCommerce's own is_order_received_page()).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Replace WooCommerce's default order-received markup with the design handoff's own
 *          centered confirmation layout (gold checkmark, order number/estimated delivery/total/
 *          shipping address, itemized recap, "track my order"/"continue shopping" CTAs), reading
 *          the real, just-placed order through Solar_Template\Checkout\OrderConfirmation — same
 *          convention as CartView for the cart page.
 *
 * @package Solar_Template
 */

use Solar_Template\Checkout\OrderConfirmation;
use Solar_Template\Support\StoreLinks;

defined( 'ABSPATH' ) || exit;

/** @var WC_Order|false $order */
$solar_confirmation = OrderConfirmation::view_model( $order );
?>

<div class="woocommerce-order order-confirmation">

	<?php if ( $order && $order->has_status( 'failed' ) ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'solar-template' ); ?></p>

		<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn--primary"><?php esc_html_e( 'Pay', 'solar-template' ); ?></a>
			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'My account', 'solar-template' ); ?></a>
			<?php endif; ?>
		</p>

	<?php elseif ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<div class="order-confirmation__icon" aria-hidden="true">
			<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
		</div>

		<h1 class="order-confirmation__title"><?php esc_html_e( 'Order confirmed!', 'solar-template' ); ?></h1>

		<p class="order-confirmation__subtitle">
			<?php
			printf(
				/* translators: %s: customer first name. */
				esc_html__( 'Thank you for your purchase, %s! Your order has been placed and is being prepared.', 'solar-template' ),
				esc_html( $solar_confirmation['customer_first_name'] ? $solar_confirmation['customer_first_name'] : __( 'valued customer', 'solar-template' ) )
			);
			?>
		</p>

		<div class="order-confirmation__card">
			<div class="order-confirmation__grid">
				<div class="order-confirmation__field">
					<div class="order-confirmation__label"><?php esc_html_e( 'Order number', 'solar-template' ); ?></div>
					<div class="order-confirmation__value">#<?php echo esc_html( $solar_confirmation['order_number'] ); ?></div>
				</div>
				<?php if ( $solar_confirmation['delivery_estimate'] ) : ?>
					<div class="order-confirmation__field">
						<div class="order-confirmation__label"><?php esc_html_e( 'Estimated delivery', 'solar-template' ); ?></div>
						<div class="order-confirmation__value"><?php echo esc_html( $solar_confirmation['delivery_estimate'] ); ?></div>
					</div>
				<?php endif; ?>
				<div class="order-confirmation__field">
					<div class="order-confirmation__label"><?php esc_html_e( 'Total paid', 'solar-template' ); ?></div>
					<div class="order-confirmation__value"><?php echo wp_kses_post( $solar_confirmation['total_html'] ); ?></div>
				</div>
				<?php if ( $solar_confirmation['shipping_address'] ) : ?>
					<div class="order-confirmation__field">
						<div class="order-confirmation__label"><?php esc_html_e( 'Shipping address', 'solar-template' ); ?></div>
						<div class="order-confirmation__value"><?php echo wp_kses_post( $solar_confirmation['shipping_address'] ); ?></div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $solar_confirmation['items'] ) : ?>
				<div class="order-confirmation__items">
					<div class="order-confirmation__items-label"><?php esc_html_e( 'Items ordered', 'solar-template' ); ?></div>
					<div class="order-confirmation__items-list">
						<?php foreach ( $solar_confirmation['items'] as $solar_item ) : ?>
							<div class="order-confirmation__item">
								<span class="order-confirmation__item-name">
									<?php echo esc_html( $solar_item['name'] ); ?>
									<?php if ( $solar_item['meta'] ) : ?>
										<span class="order-confirmation__item-meta">(<?php echo esc_html( $solar_item['meta'] ); ?>)</span>
									<?php endif; ?>
								</span>
								<span class="order-confirmation__item-total"><?php echo wp_kses_post( $solar_item['total_html'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $solar_confirmation['customer_email'] ) : ?>
			<p class="order-confirmation__email-note">
				<?php
				printf(
					/* translators: %s: customer email address. */
					esc_html__( 'A confirmation email has been sent to %s', 'solar-template' ),
					'<strong>' . esc_html( $solar_confirmation['customer_email'] ) . '</strong>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above, fixed wrapper markup only.
				);
				?>
			</p>
		<?php endif; ?>

		<div class="order-confirmation__actions">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'Track my order', 'solar-template' ); ?></a>
			<a href="<?php echo esc_url( StoreLinks::shop_url() ); ?>" class="btn btn--primary"><?php esc_html_e( 'Continue shopping', 'solar-template' ); ?></a>
		</div>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<p class="order-confirmation__subtitle"><?php esc_html_e( 'We could not find this order.', 'solar-template' ); ?></p>

	<?php endif; ?>

</div>
