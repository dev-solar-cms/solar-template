<?php
/**
 * Created: 2026-09-25 15:50 CEST
 * Role: Newsletter request handler (Solar_Template\Newsletter).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The newsletter form's WordPress-hook "controller" in the DECISIONS.md MVC sense —
 *          the AJAX subscription handler and its script enqueue — delegating storage to
 *          SubscriberRepository and content to Solar_Template\FrontPage\Newsletter, mirroring
 *          Solar_Template\Catalog\CatalogController.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Newsletter;

use Solar_Template\FrontPage\Newsletter as NewsletterContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the newsletter form's AJAX submission into WordPress.
 */
final class NewsletterController {

	/**
	 * Handles the front page newsletter form's AJAX submission (`solar_template_newsletter_subscribe`
	 * action): validates the nonce and the submitted email address, records the subscription, and
	 * responds with the JSON feedback message the front-end (assets/js/newsletter.js) displays.
	 *
	 * @return void
	 */
	public static function handle_subscription(): void {
		check_ajax_referer( 'solar_template_newsletter', 'nonce' );

		$config = NewsletterContent::config();
		$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => $config['invalid_email_message'] ) );
		}

		$subscribed = self::repository()->subscribe( $email );

		if ( $subscribed ) {
			wp_send_json_success( array( 'message' => $config['success_message'] ) );
		}

		wp_send_json_error( array( 'message' => $config['already_subscribed_message'] ) );
	}

	/**
	 * Enqueues the front page newsletter form's own script and localizes the AJAX endpoint/nonce it
	 * needs, on top of the theme's compiled main script.
	 *
	 * @return void
	 */
	public static function enqueue_script(): void {
		wp_localize_script(
			'solar-template',
			'solarTemplateNewsletter',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'solar_template_newsletter' ),
			)
		);
	}

	/**
	 * Builds the theme's newsletter subscriber repository, wired to the real WordPress database.
	 *
	 * @return SubscriberRepository
	 */
	private static function repository(): SubscriberRepository {
		global $wpdb;

		static $repository = null;

		if ( null === $repository ) {
			$repository = new SubscriberRepository( $wpdb );
		}

		return $repository;
	}
}
