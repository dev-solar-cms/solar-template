<?php
/**
 * Created: 2026-09-26 11:10 CEST
 * Role: Reusable login form override (woocommerce/global/form-login.php), rendered by
 *       woocommerce_login_form() wherever WooCommerce calls it (the checkout page's login step,
 *       any other native "please log in" prompt).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Restyle WooCommerce's own login form to the theme's design language, keeping every
 *          native field name/id, the nonce and the hidden redirect field untouched — real
 *          authentication (`wp_signon()` via WC_Form_Handler::process_login(), hooked on
 *          `wp_loaded`) keeps working exactly like the default template.
 *
 * @package Solar_Template
 * @var bool   $hidden  Whether the form should render display:none.
 * @var string $message Optional message shown above the fields.
 * @var string $redirect URL to redirect to after a successful login.
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	return;
}
?>
<form class="woocommerce-form woocommerce-form-login login solar-login-form" method="post" <?php echo ( $hidden ) ? 'style="display:none;"' : ''; ?>>

	<?php do_action( 'woocommerce_login_form_start' ); ?>

	<?php echo ( $message ) ? wpautop( wptexturize( $message ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce's own filtered message. ?>

	<div class="solar-login-form__field">
		<label for="username"><?php esc_html_e( 'Email address', 'solar-template' ); ?></label>
		<input type="text" class="input-text" name="username" id="username" autocomplete="username" required aria-required="true" />
	</div>
	<div class="solar-login-form__field">
		<label for="password"><?php esc_html_e( 'Password', 'solar-template' ); ?></label>
		<input class="input-text woocommerce-Input" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
	</div>

	<?php do_action( 'woocommerce_login_form' ); ?>

	<div class="solar-login-form__row">
		<label class="solar-login-form__remember">
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php esc_html_e( 'Remember me', 'solar-template' ); ?>
		</label>
		<a class="solar-login-form__lost-password" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot your password?', 'solar-template' ); ?></a>
	</div>

	<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
	<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />
	<button type="submit" class="btn btn--dark btn--block solar-login-form__submit" name="login" value="<?php echo esc_attr__( 'Login', 'solar-template' ); ?>">
		<?php esc_html_e( 'Login', 'solar-template' ); ?> →
	</button>

	<?php do_action( 'woocommerce_login_form_end' ); ?>

</form>
