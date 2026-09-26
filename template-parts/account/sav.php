<?php
/**
 * Created: 2026-09-26 19:10 CEST
 * Role: My Account support request template-part (template-parts/account/sav.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the "new support request" form (order/type selects, subject, message, optional
 *          attachment) and the customer's own past requests, from the plain view-model built by
 *          Solar_Template\Account\SupportRequestController. A plain form submission is enough — no
 *          JavaScript/AJAX required for this step.
 *
 * Expected `$args` keys:
 * - order_options (array<int, string>) order ID => "#number"
 * - type_options (array<string, string>) request type slug => translated label
 * - requests (array<int, array{subject: string, type_label: string, created_date: string, status: string}>)
 * - feedback (string|null) 'success', 'error', or null
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\SupportRequestController;

$defaults = array(
	'order_options' => array(),
	'type_options'  => array(),
	'requests'      => array(),
	'feedback'      => null,
);

$data = wp_parse_args( $args ?? array(), $defaults );
?>
<h1><?php esc_html_e( 'Support request', 'solar-template' ); ?></h1>

<?php if ( 'success' === $data['feedback'] ) : ?>
	<div class="account-sav__notice account-sav__notice--success"><?php esc_html_e( 'Your request has been sent. Our team will get back to you shortly.', 'solar-template' ); ?></div>
<?php elseif ( 'error' === $data['feedback'] ) : ?>
	<div class="account-sav__notice account-sav__notice--error"><?php esc_html_e( 'Please fill in every required field.', 'solar-template' ); ?></div>
<?php endif; ?>

<div class="account-panel account-sav__form">
	<div class="account-panel__header">
		<h2><?php esc_html_e( 'New request', 'solar-template' ); ?></h2>
	</div>

	<form method="post" enctype="multipart/form-data" class="account-form">
		<?php wp_nonce_field( SupportRequestController::NONCE_ACTION ); ?>

		<div class="account-sav__row account-sav__row--double">
			<div class="form-row">
				<label for="sav_order_id"><?php esc_html_e( 'Related order', 'solar-template' ); ?></label>
				<select name="order_id" id="sav_order_id">
					<option value=""><?php esc_html_e( 'Select an order', 'solar-template' ); ?></option>
					<?php foreach ( $data['order_options'] as $order_id => $order_label ) : ?>
						<option value="<?php echo esc_attr( (string) $order_id ); ?>"><?php echo esc_html( $order_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-row">
				<label for="sav_request_type"><?php esc_html_e( 'Request type', 'solar-template' ); ?></label>
				<select name="request_type" id="sav_request_type" required>
					<option value=""><?php esc_html_e( 'Select a type', 'solar-template' ); ?></option>
					<?php foreach ( $data['type_options'] as $type_slug => $type_label ) : ?>
						<option value="<?php echo esc_attr( $type_slug ); ?>"><?php echo esc_html( $type_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="account-sav__row">
			<div class="form-row">
				<label for="sav_subject"><?php esc_html_e( 'Subject', 'solar-template' ); ?></label>
				<input type="text" name="subject" id="sav_subject" required placeholder="<?php esc_attr_e( 'Briefly describe your issue', 'solar-template' ); ?>" />
			</div>
		</div>

		<div class="account-sav__row">
			<div class="form-row">
				<label for="sav_message"><?php esc_html_e( 'Detailed description', 'solar-template' ); ?></label>
				<textarea name="message" id="sav_message" rows="5" required placeholder="<?php esc_attr_e( 'Explain your situation in detail…', 'solar-template' ); ?>"></textarea>
			</div>
		</div>

		<div class="account-sav__row">
			<div class="form-row">
				<label for="sav_attachment">
					<?php esc_html_e( 'Photos / documents', 'solar-template' ); ?>
					<span class="account-sav__optional">(<?php esc_html_e( 'optional', 'solar-template' ); ?>)</span>
				</label>
				<input type="file" name="attachment" id="sav_attachment" accept=".png,.jpg,.jpeg,.pdf" class="account-sav__file-input" />
				<p class="account-sav__file-hint"><?php esc_html_e( 'PNG, JPG, PDF — max. 10 MB', 'solar-template' ); ?></p>
			</div>
		</div>

		<button type="submit" name="solar_template_sav_submit" value="1" class="btn btn--dark">
			<?php esc_html_e( 'Send request', 'solar-template' ); ?>
		</button>
	</form>
</div>

<?php if ( empty( $data['requests'] ) ) : ?>
	<p class="account-panel__empty"><?php esc_html_e( 'You have no active support request at the moment.', 'solar-template' ); ?></p>
<?php else : ?>
	<div class="account-panel account-sav__list">
		<div class="account-panel__header">
			<h2><?php esc_html_e( 'Your requests', 'solar-template' ); ?></h2>
		</div>
		<?php foreach ( $data['requests'] as $request ) : ?>
			<div class="sav-request-row">
				<div>
					<div class="sav-request-row__subject"><?php echo esc_html( $request['subject'] ); ?></div>
					<div class="sav-request-row__meta"><?php echo esc_html( $request['type_label'] ); ?> · <?php echo esc_html( $request['created_date'] ); ?></div>
				</div>
				<span class="order-status-badge order-status-badge--<?php echo 'open' === $request['status'] ? 'transit' : 'delivered'; ?>">
					<?php echo 'open' === $request['status'] ? esc_html__( 'Open', 'solar-template' ) : esc_html__( 'Closed', 'solar-template' ); ?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
