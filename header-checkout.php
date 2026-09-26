<?php
/**
 * Created: 2026-09-26 09:15 CEST
 * Role: Sales tunnel minimal header template (header-checkout.php), loaded via get_header('checkout')
 *       for the cart/checkout/order-received pages only.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Open the document with a stripped-down header (centered brand link, "100% secure
 *          payment" note) instead of the full sticky navigation — the tunnel intentionally has no
 *          primary nav/mega menu/search to avoid distracting the customer mid-purchase.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'solar-template checkout-flow' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'solar-template' ); ?></a>

<header class="checkout-header">
	<div class="checkout-header__inner">
		<a class="checkout-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php bloginfo( 'name' ); ?>
		</a>
		<div class="checkout-header__secure">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
			<?php esc_html_e( '100% secure payment', 'solar-template' ); ?>
		</div>
	</div>
</header>
