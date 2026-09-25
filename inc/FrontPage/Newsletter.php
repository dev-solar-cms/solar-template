<?php
/**
 * Created: 2026-09-25 15:46 CEST
 * Role: Front page newsletter section content (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the newsletter section's copy (eyebrow, heading, description, form
 *          placeholder/button labels, feedback messages) — everything except the submission
 *          handling itself, see Solar_Template\Newsletter\NewsletterController.
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page "Newsletter" section content.
 */
final class Newsletter {

	/**
	 * Returns the front page "Newsletter" section's content.
	 *
	 * @return array{
	 *     eyebrow: string,
	 *     heading: string,
	 *     description: string,
	 *     email_placeholder: string,
	 *     submit_label: string,
	 *     privacy_note: string,
	 *     success_message: string,
	 *     already_subscribed_message: string,
	 *     invalid_email_message: string,
	 *     error_message: string,
	 * }
	 */
	public static function config(): array {
		$defaults = array(
			'eyebrow'                    => __( 'Newsletter', 'solar-template' ),
			'heading'                    => __( 'Stay in the loop', 'solar-template' ),
			'description'                => __( 'Get our new arrivals, exclusive offers and inspiration straight to your inbox.', 'solar-template' ),
			'email_placeholder'          => __( 'your@email.com', 'solar-template' ),
			'submit_label'               => __( 'Subscribe', 'solar-template' ),
			'privacy_note'               => __( 'Unsubscribe at any time. No spam, ever.', 'solar-template' ),
			'success_message'            => __( 'Thank you for subscribing!', 'solar-template' ),
			'already_subscribed_message' => __( 'This email address is already subscribed.', 'solar-template' ),
			'invalid_email_message'      => __( 'Please enter a valid email address.', 'solar-template' ),
			'error_message'              => __( 'Something went wrong. Please try again.', 'solar-template' ),
		);

		/**
		 * Filters the front page "Newsletter" section's content.
		 *
		 * @param array $config See self::config()'s return type.
		 */
		return apply_filters( 'solar_template_newsletter_config', $defaults );
	}
}
