<?php
/**
 * Created: 2026-09-26 09:20 CEST
 * Role: Sales tunnel page template (checkout.php), served instead of the generic page template for
 *       the cart, checkout and order-received pages (Solar_Template\Checkout\CheckoutController::
 *       template_include(), hooked in inc/Theme.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Wrap the real WooCommerce page content (rendered by the_content(), same shortcode/block
 *          every one of these pages already contains) with the tunnel's own minimal header/footer
 *          and the step indicator, so cart.php/form-login.php/form-checkout.php/payment.php/
 *          thankyou.php overrides only have to render their own section, not the surrounding
 *          page chrome. Same top-level-template convention as single-product.php/front-page.php.
 *
 * @package Solar_Template
 */

use Solar_Template\Checkout\CheckoutController;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'checkout' );

$solar_checkout_current_step = CheckoutController::current_step();
?>
<div class="checkout-steps-bar">
	<div class="checkout-steps-bar__inner">
		<?php get_template_part( 'template-parts/checkout/step-indicator', null, array( 'current_step' => $solar_checkout_current_step ) ); ?>
	</div>
</div>

<main class="checkout-page">
	<div class="checkout-page__inner">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
</main>
<?php
get_footer( 'checkout' );
