<?php
/**
 * Created: 2026-09-26 10:50 CEST
 * Role: Empty cart state override (woocommerce/cart/cart-empty.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Style the message WooCommerce shows in place of cart.php when the cart has no item,
 *          keeping the `woocommerce_cart_is_empty` hook (core's own "Your cart is currently
 *          empty." notice stays hooked to it) and the real shop URL for the "return to shop" link.
 *
 * @package Solar_Template
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="cart-page cart-page--empty">
	<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>

	<?php
	/**
	 * @hooked wc_empty_cart_message - 10
	 */
	do_action( 'woocommerce_cart_is_empty' );
	?>

	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<a class="btn btn--dark cart-page__continue-empty wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'solar-template' ) ) ); ?>
		</a>
	<?php endif; ?>
</div>
