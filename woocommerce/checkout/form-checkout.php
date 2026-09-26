<?php
/**
 * Created: 2026-09-26 11:15 CEST
 * Role: Checkout page structural override (woocommerce/checkout/form-checkout.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Organize the login/customer-details/payment sections WooCommerce already renders into
 *          three step panels toggled by assets/js/checkout.js (initCheckoutSteps()) — "login"
 *          (skipped entirely for an already logged-in customer), "shipping" (the address form) and
 *          "payment" (gateway selection/fields, woocommerce/checkout/payment.php) — sharing one
 *          sticky order summary sidebar that stays visible across both of the form's two panels
 *          (only the "login" panel, itself outside the form, hides it). Every native hook/action/
 *          field from WooCommerce's own form-checkout.php stays in place, only the surrounding
 *          structure changes, so the real checkout AJAX submission (WooCommerce's own
 *          `wc-checkout` script) keeps working unmodified.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'solar-template' ) ) );
	return;
}

$solar_show_login_step = ! is_user_logged_in();
?>

<?php if ( $solar_show_login_step ) : ?>
	<div class="checkout-step" data-step-panel="login">
		<div class="checkout-step__intro">
			<h1 class="checkout-step__title"><?php esc_html_e( 'Login', 'solar-template' ); ?></h1>
			<p class="checkout-step__subtitle"><?php esc_html_e( 'Log in to speed up your order', 'solar-template' ); ?></p>
		</div>

		<div class="checkout-login-panel">
			<?php woocommerce_login_form( array( 'redirect' => wc_get_checkout_url() ) ); ?>

			<div class="checkout-login-panel__divider">
				<span><?php esc_html_e( 'or continue with', 'solar-template' ); ?></span>
			</div>
			<div class="checkout-login-panel__social">
				<button type="button" class="checkout-login-panel__social-button" disabled aria-disabled="true" title="<?php echo esc_attr__( 'Not available yet', 'solar-template' ); ?>">Google</button>
				<button type="button" class="checkout-login-panel__social-button" disabled aria-disabled="true" title="<?php echo esc_attr__( 'Not available yet', 'solar-template' ); ?>">Facebook</button>
			</div>

			<?php if ( $checkout->is_registration_enabled() ) : ?>
				<div class="checkout-login-panel__create-account">
					<?php esc_html_e( "Don't have an account?", 'solar-template' ); ?>
					<button type="button" class="checkout-login-panel__create-account-link" data-goto-step="shipping" data-check-create-account="1"><?php esc_html_e( 'Create an account', 'solar-template' ); ?></button>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<button type="button" class="btn btn--outline btn--block checkout-login-panel__guest" data-goto-step="shipping">
				<?php esc_html_e( 'Continue as guest', 'solar-template' ); ?> →
			</button>
		<?php endif; ?>

		<a class="checkout-step__back" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			← <?php esc_html_e( 'Back to cart', 'solar-template' ); ?>
		</a>
	</div>
<?php endif; ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'solar-template' ); ?>">

	<div class="checkout-page__layout">
		<div class="checkout-page__main">
			<div class="checkout-step" data-step-panel="shipping" <?php echo $solar_show_login_step ? 'hidden' : ''; ?>>
				<?php if ( $checkout->get_checkout_fields() ) : ?>

					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div id="customer_details" class="checkout-customer-details">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<?php endif; ?>

				<div class="checkout-step__actions">
					<button type="button" class="btn btn--dark checkout-step__actions-primary" data-goto-step="payment">
						<?php esc_html_e( 'Proceed to payment', 'solar-template' ); ?> →
					</button>
				</div>
			</div>

			<div class="checkout-step" data-step-panel="payment" hidden>
				<?php woocommerce_checkout_payment(); ?>
			</div>
		</div>

		<div class="checkout-page__sidebar" <?php echo $solar_show_login_step ? 'hidden' : ''; ?>>
			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
