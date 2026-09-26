<?php
/**
 * Created: 2026-09-26 09:16 CEST
 * Role: Sales tunnel minimal footer template (footer-checkout.php), loaded via
 *       get_footer('checkout') for the cart/checkout/order-received pages only.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Close the document opened by header-checkout.php with a stripped-down footer (legal
 *          links, contact, copyright, secure payment note) instead of the full multi-column
 *          footer, same reasoning as the minimal header.
 *
 * @package Solar_Template
 */

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="checkout-footer">
	<div class="checkout-footer__inner">
		<a href="<?php echo esc_url( StoreLinks::page_url_by_slug( 'terms-and-conditions' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'solar-template' ); ?></a>
		<a href="<?php echo esc_url( StoreLinks::page_url_by_slug( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'solar-template' ); ?></a>
		<a href="<?php echo esc_url( StoreLinks::page_url_by_slug( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'solar-template' ); ?></a>
		<span><?php echo esc_html( \Solar_Template\Footer\Footer::copyright() ); ?></span>
		<span><?php esc_html_e( 'Secure SSL payment', 'solar-template' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
