<?php
/**
 * Created: 2026-09-26 14:40 CEST
 * Role: My Account page layout override (woocommerce/myaccount/my-account.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Wrap WooCommerce's own navigation/content action hooks in the design handoff's
 *          `260px 1fr` sidebar + content grid, instead of core's plain stacked layout. Only
 *          reached for a logged-in customer — WooCommerce's own My Account shortcode renders
 *          myaccount/form-login.php directly for a guest, never this template.
 *
 * Based on WooCommerce core's own myaccount/my-account.php (template version 3.5.0).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="account-layout">
	<?php
	/**
	 * My Account navigation (Solar_Template overrides this into the sidebar, see
	 * woocommerce/myaccount/navigation.php).
	 */
	do_action( 'woocommerce_account_navigation' );
	?>

	<div class="woocommerce-MyAccount-content account-content">
		<?php
		/**
		 * My Account content.
		 */
		do_action( 'woocommerce_account_content' );
		?>
	</div>
</div>
