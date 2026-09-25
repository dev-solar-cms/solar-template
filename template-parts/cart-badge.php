<?php
/**
 * Created: 2026-09-25 08:00 CEST
 * Role: Reusable cart item-count badge template-part (template-parts/cart-badge.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the small gold badge showing how many items are in the visitor's WooCommerce
 *          cart, used by the header's cart icon. Kept as its own template-part (rather than
 *          inlined in header.php) so the exact same markup can also be returned by a
 *          `woocommerce_add_to_cart_fragments` callback for the AJAX-driven live update.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_count = \Solar_Template\Header\Cart::cart_count();
?>
<span class="site-header__cart-count<?php echo $cart_count > 0 ? '' : ' is-hidden'; ?>">
	<?php echo esc_html( (string) $cart_count ); ?>
</span>
