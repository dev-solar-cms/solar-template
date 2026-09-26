<?php
/**
 * Created: 2026-09-26 21:20 CEST
 * Role: Native contact form handler (Solar_Template\Pages).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The contact form's WordPress-hook "controller" in the DECISIONS.md MVC sense — validates
 *          and processes the plain (non-AJAX) POST submitted from page-contact.php, with a nonce
 *          plus a honeypot field as basic anti-spam, and emails the site admin via native
 *          `wp_mail()` — no third-party form plugin (DECISIONS.md §2). Field validation and the
 *          honeypot check are pure functions with no WordPress dependency, so they can be unit
 *          tested directly (see tests/php/Pages/ContactControllerTest.php).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Pages;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the contact form's plain submission into WordPress.
 */
final class ContactController {

	public const NONCE_ACTION          = 'solar_template_contact_submit';
	public const HONEYPOT_FIELD        = 'solar_template_contact_company';
	private const REQUIRED_TEXT_FIELDS = array( 'first_name', 'last_name', 'message' );

	/**
	 * @return array<string, string> Subject slug => translated label.
	 */
	public static function subjects(): array {
		return array(
			'product_question' => __( 'Product question', 'solar-template' ),
			'order_tracking'   => __( 'Order tracking', 'solar-template' ),
			'return_exchange'  => __( 'Return / Exchange', 'solar-template' ),
			'partnership'      => __( 'Partnership / Press', 'solar-template' ),
			'other'            => __( 'Other request', 'solar-template' ),
		);
	}

	/**
	 * Handles the contact form's plain POST submission: verifies the nonce, checks the honeypot
	 * field, validates the required fields, emails the site admin, and redirects back to the contact
	 * page with a success/error flag. Silently does nothing for any other request.
	 *
	 * @return void
	 */
	public static function maybe_handle_submission(): void {
		if ( ! isset( $_POST['solar_template_contact_submit'] ) ) {
			return;
		}

		check_admin_referer( self::NONCE_ACTION );

		$honeypot = isset( $_POST[ self::HONEYPOT_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::HONEYPOT_FIELD ] ) ) : '';

		if ( self::is_spam( $honeypot ) ) {
			// Redirect to the same success state a legitimate visitor would see, without sending any
			// mail — never tips off a bot that its submission was rejected.
			self::redirect_with_feedback( 'success' );
		}

		$data = array(
			'first_name' => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
			'last_name'  => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
			'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'subject'    => isset( $_POST['subject'] ) && array_key_exists( sanitize_key( wp_unslash( $_POST['subject'] ) ), self::subjects() )
				? sanitize_key( wp_unslash( $_POST['subject'] ) )
				: '',
			'message'    => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
			'consent'    => ! empty( $_POST['consent'] ),
		);

		if ( ! empty( self::validate( $data ) ) ) {
			self::redirect_with_feedback( 'error' );
		}

		self::notify_admin( $data );

		self::redirect_with_feedback( 'success' );
	}

	/**
	 * Whether the hidden honeypot field was filled in — a real visitor never sees or fills it, so any
	 * non-empty value means the submission came from a bot.
	 *
	 * @param string $honeypot_value Submitted value of the honeypot field.
	 * @return bool
	 */
	public static function is_spam( string $honeypot_value ): bool {
		return '' !== trim( $honeypot_value );
	}

	/**
	 * Validates the submitted (already sanitized) contact form data.
	 *
	 * Pure function — no WordPress dependency — so it can be unit tested directly.
	 *
	 * @param array{first_name: string, last_name: string, email: string, subject: string, message: string, consent: bool} $data Sanitized form data.
	 * @return array<int, string> Names of the invalid/missing fields; empty when $data is valid.
	 */
	public static function validate( array $data ): array {
		$errors = array();

		foreach ( self::REQUIRED_TEXT_FIELDS as $field ) {
			if ( '' === trim( (string) ( $data[ $field ] ?? '' ) ) ) {
				$errors[] = $field;
			}
		}

		if ( ! filter_var( $data['email'] ?? '', FILTER_VALIDATE_EMAIL ) ) {
			$errors[] = 'email';
		}

		if ( '' === (string) ( $data['subject'] ?? '' ) ) {
			$errors[] = 'subject';
		}

		if ( empty( $data['consent'] ) ) {
			$errors[] = 'consent';
		}

		return $errors;
	}

	/**
	 * @return string|null 'success', 'error', or null when the current request has no feedback flag.
	 */
	public static function feedback_from_query(): ?string {
		if ( ! isset( $_GET['contact'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.
			return null;
		}

		$status = sanitize_key( wp_unslash( $_GET['contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.

		return in_array( $status, array( 'success', 'error' ), true ) ? $status : null;
	}

	/**
	 * Emails the site admin about a new contact message, with the visitor's own address set as the
	 * Reply-To so a direct reply reaches them.
	 *
	 * @param array{first_name: string, last_name: string, email: string, subject: string, message: string} $data Validated form data.
	 * @return void
	 */
	private static function notify_admin( array $data ): void {
		$subjects      = self::subjects();
		$subject_label = $subjects[ $data['subject'] ] ?? $data['subject'];

		$body = sprintf(
			/* translators: 1: sender name, 2: sender email, 3: message subject, 4: message body. */
			__( "New contact message from %1\$s (%2\$s)\nSubject: %3\$s\n\n%4\$s", 'solar-template' ),
			trim( $data['first_name'] . ' ' . $data['last_name'] ),
			$data['email'],
			$subject_label,
			$data['message']
		);

		wp_mail(
			get_option( 'admin_email' ),
			sprintf(
				/* translators: %s: message subject. */
				__( 'New contact message: %s', 'solar-template' ),
				$subject_label
			),
			$body,
			array( 'Reply-To: ' . $data['email'] )
		);
	}

	/**
	 * Redirects back to the contact page with a `?contact=success|error` feedback flag.
	 *
	 * @param string $status 'success' or 'error'.
	 * @return void
	 */
	private static function redirect_with_feedback( string $status ): void {
		wp_safe_redirect( add_query_arg( 'contact', $status, StoreLinks::page_url_by_slug( 'contact' ) ) );
		exit;
	}
}
