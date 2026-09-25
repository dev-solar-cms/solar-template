<?php
/**
 * Created: 2026-09-25 12:30 CEST
 * Role: Front page newsletter section template-part
 *       (template-parts/front-page/newsletter.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the newsletter sign-up form (email input + submit button, on a dark background),
 *          submitted via AJAX by assets/js/newsletter.js against
 *          solar_template_handle_newsletter_subscription() in functions.php.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$newsletter = solar_template_newsletter_config();
?>
<section class="home-newsletter">
	<div class="home-newsletter__inner">
		<?php if ( '' !== $newsletter['eyebrow'] ) : ?>
			<p class="home-newsletter__eyebrow"><?php echo esc_html( $newsletter['eyebrow'] ); ?></p>
		<?php endif; ?>

		<h2 class="home-newsletter__heading"><?php echo esc_html( $newsletter['heading'] ); ?></h2>

		<?php if ( '' !== $newsletter['description'] ) : ?>
			<p class="home-newsletter__description"><?php echo esc_html( $newsletter['description'] ); ?></p>
		<?php endif; ?>

		<form class="home-newsletter__form js-newsletter-form" novalidate>
			<label class="screen-reader-text" for="home-newsletter-email">
				<?php esc_html_e( 'Email address', 'solar-template' ); ?>
			</label>
			<input
				type="email"
				id="home-newsletter-email"
				name="email"
				class="home-newsletter__input"
				placeholder="<?php echo esc_attr( $newsletter['email_placeholder'] ); ?>"
				required
			/>
			<button type="submit" class="home-newsletter__submit">
				<?php echo esc_html( $newsletter['submit_label'] ); ?>
			</button>
		</form>

		<p class="home-newsletter__feedback js-newsletter-feedback" role="status" aria-live="polite"></p>

		<?php if ( '' !== $newsletter['privacy_note'] ) : ?>
			<p class="home-newsletter__privacy"><?php echo esc_html( $newsletter['privacy_note'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
