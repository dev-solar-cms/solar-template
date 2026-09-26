<?php
/**
 * Created: 2026-09-26 21:45 CEST
 * Role: Contact form template-part (template-parts/pages/contact-form.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the native contact form (a plain, non-AJAX POST handled by
 *          Solar_Template\Pages\ContactController::maybe_handle_submission()) or the success state
 *          after a legitimate submission, from the plain data page-contact.php passes in.
 *
 * Expected `$args` keys:
 * - subjects (array<string, string>) subject slug => translated label
 * - feedback (string|null) 'success', 'error', or null
 *
 * @package Solar_Template
 * @var array $args
 */

use Solar_Template\Pages\ContactController;
use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$args ?? array(),
	array(
		'subjects' => array(),
		'feedback' => null,
	)
);
?>
<div class="contact-form-column">
	<?php if ( 'success' === $data['feedback'] ) : ?>
		<div class="contact-form-success">
			<div class="contact-form-success__icon" aria-hidden="true">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
			</div>
			<h2 class="contact-form-success__title"><?php esc_html_e( 'Message sent!', 'solar-template' ); ?></h2>
			<p class="contact-form-success__text"><?php esc_html_e( 'Thank you for your message. Our team will get back to you as soon as possible, within 24 business hours.', 'solar-template' ); ?></p>
			<a class="btn btn--outline" href="<?php echo esc_url( StoreLinks::page_url_by_slug( 'contact' ) ); ?>">
				<?php esc_html_e( 'Send another message', 'solar-template' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="contact-form-card">
			<h2 class="contact-form-card__title"><?php esc_html_e( 'Send a message', 'solar-template' ); ?></h2>

			<?php if ( 'error' === $data['feedback'] ) : ?>
				<div class="contact-form__notice contact-form__notice--error"><?php esc_html_e( 'Please fill in every required field with a valid email address.', 'solar-template' ); ?></div>
			<?php endif; ?>

			<form method="post" class="contact-form">
				<?php wp_nonce_field( ContactController::NONCE_ACTION ); ?>

				<p class="contact-form__honeypot" aria-hidden="true">
					<label for="contact_company"><?php esc_html_e( 'Company', 'solar-template' ); ?></label>
					<input type="text" id="contact_company" name="<?php echo esc_attr( ContactController::HONEYPOT_FIELD ); ?>" tabindex="-1" autocomplete="off" />
				</p>

				<div class="contact-form__row contact-form__row--double">
					<div class="contact-form__field">
						<label for="contact_first_name"><?php esc_html_e( 'First name', 'solar-template' ); ?></label>
						<input type="text" id="contact_first_name" name="first_name" required />
					</div>
					<div class="contact-form__field">
						<label for="contact_last_name"><?php esc_html_e( 'Last name', 'solar-template' ); ?></label>
						<input type="text" id="contact_last_name" name="last_name" required />
					</div>
				</div>

				<div class="contact-form__field">
					<label for="contact_email"><?php esc_html_e( 'Email address', 'solar-template' ); ?></label>
					<input type="email" id="contact_email" name="email" required />
				</div>

				<div class="contact-form__field">
					<label for="contact_subject"><?php esc_html_e( 'Subject', 'solar-template' ); ?></label>
					<select id="contact_subject" name="subject" required>
						<option value=""><?php esc_html_e( 'Select a subject', 'solar-template' ); ?></option>
						<?php foreach ( $data['subjects'] as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="contact-form__field">
					<label for="contact_message"><?php esc_html_e( 'Message', 'solar-template' ); ?></label>
					<textarea id="contact_message" name="message" rows="6" required placeholder="<?php esc_attr_e( 'Describe your request in detail…', 'solar-template' ); ?>"></textarea>
				</div>

				<div class="contact-form__consent">
					<input type="checkbox" id="contact_consent" name="consent" value="1" required />
					<label for="contact_consent">
						<?php
						printf(
							wp_kses(
								/* translators: %s: "privacy policy" link. */
								__( 'I agree that my data will be used to process my request, in accordance with the %s.', 'solar-template' ),
								array( 'a' => array( 'href' => array() ) )
							),
							'<a href="' . esc_url( StoreLinks::page_url_by_slug( 'privacy-policy' ) ) . '">' . esc_html__( 'privacy policy', 'solar-template' ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inserted as the %s placeholder for the wp_kses()-filtered string above, already escaped/allow-listed.
						);
						?>
					</label>
				</div>

				<button type="submit" name="solar_template_contact_submit" value="1" class="btn btn--dark contact-form__submit">
					<?php esc_html_e( 'Send message', 'solar-template' ); ?> →
				</button>
			</form>

			<p class="contact-form__lock-note">🔒 <?php esc_html_e( 'Your data is protected · Guaranteed reply within 24 business hours', 'solar-template' ); ?></p>
		</div>
	<?php endif; ?>
</div>
