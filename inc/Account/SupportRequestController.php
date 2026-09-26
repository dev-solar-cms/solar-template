<?php
/**
 * Created: 2026-09-26 19:00 CEST
 * Role: Support request ("S.A.V.") handler (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The support request's WordPress-hook "controller" in the DECISIONS.md MVC sense — the
 *          "sav" My Account endpoint's content and its plain (non-AJAX) form submission handler,
 *          delegating storage to SupportRequestRepository and rendering to
 *          template-parts/account/sav.php. Notifies the site admin by native `wp_mail()` rather than
 *          a full ticketing system, per the design handoff's own explicit scope note.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the support request form's submission and My Account page into WordPress.
 */
final class SupportRequestController {

	public const NONCE_ACTION         = 'solar_template_sav_submit';
	private const ALLOWED_MIME_TYPES  = array(
		'png'  => 'image/png',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'pdf'  => 'application/pdf',
	);
	private const MAX_ATTACHMENT_SIZE = 10 * 1024 * 1024; // 10 MB, matching the design handoff's own limit.

	/**
	 * @return array<string, string> Request type slug => translated label.
	 */
	public static function types(): array {
		return array(
			'defective'       => __( 'Defective product', 'solar-template' ),
			'shipping_issue'  => __( 'Delivery issue', 'solar-template' ),
			'return_exchange' => __( 'Return / Exchange', 'solar-template' ),
			'other'           => __( 'Other', 'solar-template' ),
		);
	}

	/**
	 * Renders the "sav" My Account endpoint's content: the new request form and the customer's own
	 * past requests.
	 *
	 * @return void
	 */
	public static function render_sav_page(): void {
		$user_id = get_current_user_id();
		$orders  = wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
				'orderby'  => 'date',
				'order'    => 'DESC',
			)
		);

		$order_options = array();

		foreach ( $orders as $order ) {
			$order_options[ $order->get_id() ] = '#' . $order->get_order_number();
		}

		$requests = array_map(
			static function ( object $request ): array {
				return array(
					'subject'      => $request->subject,
					'type_label'   => SupportRequestController::types()[ $request->request_type ] ?? $request->request_type,
					'created_date' => wp_date( get_option( 'date_format' ), strtotime( $request->created_at ) ),
					'status'       => $request->status,
				);
			},
			self::repository()->for_user( $user_id )
		);

		get_template_part(
			'template-parts/account/sav',
			null,
			array(
				'order_options' => $order_options,
				'type_options'  => self::types(),
				'requests'      => $requests,
				'feedback'      => self::feedback_from_query(),
			)
		);
	}

	/**
	 * Handles the support request form's plain POST submission: verifies the nonce, validates the
	 * required fields, stores an optional attachment, records the request, emails the site admin, and
	 * redirects back to the "sav" page with a success/error flag. Silently does nothing for any other
	 * request.
	 *
	 * @return void
	 */
	public static function maybe_handle_submission(): void {
		if ( ! isset( $_POST['solar_template_sav_submit'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		check_admin_referer( self::NONCE_ACTION );

		$subject  = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$type     = isset( $_POST['request_type'] ) && array_key_exists( wp_unslash( $_POST['request_type'] ), self::types() )
			? sanitize_key( wp_unslash( $_POST['request_type'] ) )
			: '';
		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

		if ( '' === $subject || '' === $message || '' === $type ) {
			self::redirect_with_feedback( 'error' );
		}

		$attachment_url = self::maybe_handle_attachment();

		$user_id = get_current_user_id();

		self::repository()->create(
			array(
				'user_id'        => $user_id,
				'order_id'       => $order_id > 0 ? $order_id : null,
				'request_type'   => $type,
				'subject'        => $subject,
				'message'        => $message,
				'attachment_url' => $attachment_url,
			)
		);

		self::notify_admin( wp_get_current_user(), $type, $subject, $message, $order_id );

		self::redirect_with_feedback( 'success' );
	}

	/**
	 * Handles the form's optional file attachment via WordPress' own upload handler.
	 *
	 * @return string The uploaded file's URL, or an empty string when no valid file was submitted.
	 */
	private static function maybe_handle_attachment(): string {
		if ( empty( $_FILES['attachment']['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified in maybe_handle_submission().
			return '';
		}

		if ( $_FILES['attachment']['size'] > self::MAX_ATTACHMENT_SIZE ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified in maybe_handle_submission().
			return '';
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$uploaded = wp_handle_upload(
			$_FILES['attachment'], // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified in maybe_handle_submission().
			array(
				'test_form' => false,
				'mimes'     => self::ALLOWED_MIME_TYPES,
			)
		);

		return isset( $uploaded['url'] ) ? esc_url_raw( $uploaded['url'] ) : '';
	}

	/**
	 * Emails the site admin about a new support request.
	 *
	 * @param \WP_User $user     Customer who submitted the request.
	 * @param string   $type     Request type slug.
	 * @param string   $subject  Request subject.
	 * @param string   $message  Request message.
	 * @param int      $order_id Related order ID, or 0 when none was selected.
	 * @return void
	 */
	private static function notify_admin( \WP_User $user, string $type, string $subject, string $message, int $order_id ): void {
		$order_reference = $order_id > 0 && wc_get_order( $order_id )
			? '#' . wc_get_order( $order_id )->get_order_number()
			: __( 'None', 'solar-template' );

		$body = sprintf(
			/* translators: 1: customer name, 2: customer email, 3: request type, 4: related order, 5: message. */
			__( "New support request from %1\$s (%2\$s)\nType: %3\$s\nOrder: %4\$s\n\n%5\$s", 'solar-template' ),
			$user->display_name,
			$user->user_email,
			self::types()[ $type ] ?? $type,
			$order_reference,
			$message
		);

		wp_mail(
			get_option( 'admin_email' ),
			sprintf(
				/* translators: %s: request subject. */
				__( 'New support request: %s', 'solar-template' ),
				$subject
			),
			$body
		);
	}

	/**
	 * Redirects back to the "sav" account page with a `?sav=success|error` feedback flag.
	 *
	 * @param string $status 'success' or 'error'.
	 * @return void
	 */
	private static function redirect_with_feedback( string $status ): void {
		wp_safe_redirect( add_query_arg( 'sav', $status, wc_get_account_endpoint_url( 'sav' ) ) );
		exit;
	}

	/**
	 * @return string|null 'success', 'error', or null when the current request has no feedback flag.
	 */
	private static function feedback_from_query(): ?string {
		if ( ! isset( $_GET['sav'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.
			return null;
		}

		$status = sanitize_key( wp_unslash( $_GET['sav'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.

		return in_array( $status, array( 'success', 'error' ), true ) ? $status : null;
	}

	/**
	 * Builds the theme's support request repository, wired to the real WordPress database.
	 *
	 * @return SupportRequestRepository
	 */
	private static function repository(): SupportRequestRepository {
		global $wpdb;

		static $repository = null;

		if ( null === $repository ) {
			$repository = new SupportRequestRepository( $wpdb );
		}

		return $repository;
	}
}
