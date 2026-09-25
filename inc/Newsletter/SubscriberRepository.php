<?php
/**
 * Created: 2026-09-25 12:30 CEST
 * Role: Newsletter subscriber storage (Solar_Template\Newsletter).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Store front page newsletter sign-ups in `wp_solar_template_newsletter_subscribers`
 *          (see Solar_Template\Database\Installer::create_newsletter_table()). Direct `$wpdb`
 *          usage rather than a Contracts interface, same convention as
 *          Solar_Template\Database\Installer: this wraps no third-party library, only WordPress'
 *          own database API.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Newsletter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores and looks up newsletter subscribers.
 */
final class SubscriberRepository {

	/**
	 * @param object $wpdb WordPress' `$wpdb` global (untyped: no `wpdb` class exists outside a
	 *                     real WordPress install, matching the same convention already used by
	 *                     Solar_Template\I18n\DatabaseTranslator).
	 */
	public function __construct( private object $wpdb ) {}

	/**
	 * Records a subscription, unless that email address is already subscribed.
	 *
	 * The caller is expected to have already validated $email (e.g. via WordPress' own
	 * `is_email()`) — this method only handles storage and de-duplication.
	 *
	 * @param string $email Email address to subscribe.
	 * @return bool True when a new subscription was recorded, false when this email address was
	 *              already subscribed.
	 */
	public function subscribe( string $email ): bool {
		if ( $this->is_subscribed( $email ) ) {
			return false;
		}

		$this->wpdb->insert(
			$this->table_name(),
			array( 'email' => $email ),
			array( '%s' )
		);

		return true;
	}

	/**
	 * Reports whether the given email address is already subscribed.
	 *
	 * @param string $email Email address to look up.
	 * @return bool
	 */
	public function is_subscribed( string $email ): bool {
		$table = $this->table_name();
		$sql   = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE email = %s", $email ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (bool) $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @return string The subscribers table name, with the site's table prefix.
	 */
	private function table_name(): string {
		return $this->wpdb->prefix . 'solar_template_newsletter_subscribers';
	}
}
