<?php
/**
 * Created: 2026-09-26 10:35 CEST
 * Role: Cart page override (woocommerce/cart/cart.php), replacing WooCommerce's own template for
 *       the `[woocommerce_cart]` shortcode/block.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the cart's real line items (image, name, variant/engraving meta, quantity
 *          stepper, price, remove link) and the coupon form, fed by
 *          Solar_Template\Checkout\CartView. Keeps every native WooCommerce form field name/action/
 *          nonce WooCommerce's own cart processing depends on (quantity update, coupon
 *          application, item removal all keep working as a plain form submission), only the
 *          surrounding markup/classes are the theme's own. The order summary sidebar is rendered by
 *          the `woocommerce_cart_collaterals` hook, which loads the cart/cart-totals.php override.
 *
 * @package Solar_Template
 */

use Solar_Template\Checkout\CartView;
use Solar_Template\Support\StoreLinks;

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$solar_cart_items = CartView::items();
?>
<div class="cart-page">
	<div class="cart-page__items">
		<h1 class="cart-page__heading">
			<?php esc_html_e( 'My cart', 'solar-template' ); ?>
			<span class="cart-page__heading-count">(<?php echo esc_html( CartView::heading_label() ); ?>)</span>
		</h1>

		<form class="woocommerce-cart-form cart-page__form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<div class="cart-page__rows">
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php foreach ( $solar_cart_items as $solar_cart_item ) : ?>
					<div class="cart-page__row">
						<div class="cart-page__thumbnail">
							<?php if ( $solar_cart_item['permalink'] ) : ?>
								<a href="<?php echo esc_url( $solar_cart_item['permalink'] ); ?>">
									<?php echo wp_kses_post( $solar_cart_item['thumbnail'] ); ?>
								</a>
							<?php else : ?>
								<?php echo wp_kses_post( $solar_cart_item['thumbnail'] ); ?>
							<?php endif; ?>
						</div>

						<div class="cart-page__details">
							<div class="cart-page__name">
								<?php if ( $solar_cart_item['permalink'] ) : ?>
									<a href="<?php echo esc_url( $solar_cart_item['permalink'] ); ?>"><?php echo wp_kses_post( $solar_cart_item['name'] ); ?></a>
								<?php else : ?>
									<?php echo wp_kses_post( $solar_cart_item['name'] ); ?>
								<?php endif; ?>
							</div>
							<?php if ( $solar_cart_item['meta'] ) : ?>
								<div class="cart-page__meta"><?php echo wp_kses_post( $solar_cart_item['meta'] ); ?></div>
							<?php endif; ?>
							<div class="cart-page__qty">
								<button type="button" class="cart-page__qty-decrease" aria-label="<?php echo esc_attr__( 'Decrease quantity', 'solar-template' ); ?>">&minus;</button>
								<?php echo $solar_cart_item['quantity_input']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- woocommerce_quantity_input() already escapes its own output; wp_kses_post() would strip the <input> itself. ?>
								<button type="button" class="cart-page__qty-increase" aria-label="<?php echo esc_attr__( 'Increase quantity', 'solar-template' ); ?>">+</button>
							</div>
						</div>

						<div class="cart-page__price">
							<div class="cart-page__subtotal"><?php echo wp_kses_post( $solar_cart_item['subtotal_html'] ); ?></div>
							<?php if ( $solar_cart_item['quantity'] > 1 ) : ?>
								<div class="cart-page__unit-price">
									<?php
									printf(
										/* translators: 1: quantity, 2: unit price. */
										esc_html__( '%1$d × %2$s', 'solar-template' ),
										(int) $solar_cart_item['quantity'],
										wp_kses_post( $solar_cart_item['unit_price_html'] )
									);
									?>
								</div>
							<?php endif; ?>
							<a role="button" href="<?php echo esc_url( $solar_cart_item['remove_url'] ); ?>" class="cart-page__remove remove" aria-label="<?php echo esc_attr( $solar_cart_item['remove_label'] ); ?>">
								<?php esc_html_e( 'Remove', 'solar-template' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>

				<?php do_action( 'woocommerce_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</div>

			<div class="cart-page__actions">
				<?php if ( wc_coupons_enabled() ) : ?>
					<div class="cart-page__coupon">
						<div class="cart-page__coupon-label"><?php esc_html_e( 'Promo code', 'solar-template' ); ?></div>
						<div class="cart-page__coupon-row">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'solar-template' ); ?></label>
							<input type="text" name="coupon_code" class="cart-page__coupon-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'solar-template' ); ?>" />
							<button type="submit" class="btn btn--dark cart-page__coupon-submit" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'solar-template' ); ?>"><?php esc_html_e( 'Apply', 'solar-template' ); ?></button>
						</div>
						<?php do_action( 'woocommerce_cart_coupon' ); ?>
					</div>
				<?php endif; ?>

				<button type="submit" class="screen-reader-text" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'solar-template' ); ?>"><?php esc_html_e( 'Update cart', 'solar-template' ); ?></button>

				<?php do_action( 'woocommerce_cart_actions' ); ?>
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>

		<a class="cart-page__continue" href="<?php echo esc_url( StoreLinks::shop_url() ); ?>">
			← <?php esc_html_e( 'Continue shopping', 'solar-template' ); ?>
		</a>
	</div>

	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

	<div class="cart-page__summary">
		<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
		?>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
