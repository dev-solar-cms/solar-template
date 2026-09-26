<?php
/**
 * Created: 2026-09-26 18:55 CEST
 * Role: Support request storage (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Store each customer's support ("S.A.V.") request in
 *          `wp_solar_template_support_requests` (see
 *          Solar_Template\Database\Installer::create_support_requests_table()). Direct `$wpdb`
 *          usage rather than a Contracts interface, same convention as
 *          Solar_Template\Newsletter\SubscriberRepository/Solar_Template\Account\WishlistRepository:
 *          this wraps no third-party library, only WordPress' own database API. Deliberately not a
 *          full ticketing system (no status workflow beyond open/closed) — see the design handoff's
 *          own explicit "no full ticketing system" scope note.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores and looks up a customer's support requests.
 */
final class SupportRequestRepository {

	/**
	 * @param object $wpdb WordPress' `$wpdb` global (untyped, same convention as
	 *                     Solar_Template\Newsletter\SubscriberRepository).
	 */
	public function __construct( private object $wpdb ) {}

	/**
	 * @param array{user_id: int, order_id: int|null, request_type: string, subject: string, message: string, attachment_url: string} $data Request data.
	 * @return int The new request's ID.
	 */
	public function create( array $data ): int {
		$this->wpdb->insert(
			$this->table_name(),
			array(
				'user_id'        => $data['user_id'],
				'order_id'       => $data['order_id'],
				'request_type'   => $data['request_type'],
				'subject'        => $data['subject'],
				'message'        => $data['message'],
				'attachment_url' => $data['attachment_url'],
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * @param int $user_id Customer's user ID.
	 * @return array<int, object> Every request this customer has submitted, newest first.
	 */
	public function for_user( int $user_id ): array {
		$table = $this->table_name();
		$sql   = $this->wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC", $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (array) $this->wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @param int $user_id Customer's user ID.
	 * @return int Number of this customer's requests still open.
	 */
	public function active_count_for( int $user_id ): int {
		$table = $this->table_name();
		$sql   = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND status = %s", $user_id, 'open' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @return string The support requests table name, with the site's table prefix.
	 */
	private function table_name(): string {
		return $this->wpdb->prefix . 'solar_template_support_requests';
	}
}
