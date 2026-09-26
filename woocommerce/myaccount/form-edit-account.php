<?php
/**
 * Created: 2026-09-26 20:15 CEST
 * Role: My Account settings page override (woocommerce/myaccount/form-edit-account.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Restyle the account settings page into the design handoff's three cards (personal
 *          information, password change, danger zone), while keeping WooCommerce's own native
 *          `save_account_details` form/nonce/action untouched for name/email/password so
 *          `WC_Form_Handler::save_account_details()` keeps processing it exactly as core does. Two
 *          extra fields (phone, birthdate) ride along in the same form as plain user meta, saved by
 *          Solar_Template\Account\AccountSettingsFields on the `woocommerce_save_account_details`
 *          hook that fires right after. The danger zone is a second, separate form handled by
 *          Solar_Template\Account\AccountDeletion — real, permanent deletion (customer's own orders
 *          force-deleted, then the account itself via `wp_delete_user()`), gated behind a required
 *          confirmation checkbox (works with no JavaScript) plus a native `confirm()` dialog as a
 *          second layer (assets/js/account.js, `initAccountDeletionConfirm()`).
 *
 * Based on WooCommerce core's own myaccount/form-edit-account.php (template version 11.0.0). The
 * "Display name" field from that core template is not part of the design handoff's own form — it
 * rides along as a hidden input (defaulting to the current value) rather than being dropped, since
 * WooCommerce's own required-fields check otherwise blocks every save with no such field present.
 *
 * @package Solar_Template
 * @var \WP_User $user Currently logged-in customer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\AccountDeletion;
use Solar_Template\Account\AccountSettingsFields;

do_action( 'woocommerce_before_edit_account_form' );
?>
<h1><?php esc_html_e( 'Account settings', 'solar-template' ); ?></h1>

<?php wc_print_notices(); ?>

<form class="account-form" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

	<div class="account-panel account-settings__panel">
		<div class="account-panel__header">
			<h2><?php esc_html_e( 'Personal information', 'solar-template' ); ?></h2>
		</div>
		<div class="account-settings__body">
			<div class="account-settings__row account-settings__row--double">
				<div class="form-row">
					<label for="account_first_name"><?php esc_html_e( 'First name', 'solar-template' ); ?></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" required />
				</div>
				<div class="form-row">
					<label for="account_last_name"><?php esc_html_e( 'Last name', 'solar-template' ); ?></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" required />
				</div>
			</div>

			<div class="account-settings__row">
				<div class="form-row">
					<label for="account_email"><?php esc_html_e( 'Email address', 'solar-template' ); ?></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" required />
				</div>
			</div>

			<div class="account-settings__row">
				<div class="form-row">
					<label for="solar_template_phone"><?php esc_html_e( 'Phone', 'solar-template' ); ?></label>
					<input type="tel" class="input-text" name="solar_template_phone" id="solar_template_phone" autocomplete="tel" value="<?php echo esc_attr( AccountSettingsFields::phone( $user->ID ) ); ?>" />
				</div>
			</div>

			<div class="account-settings__row account-settings__row--narrow">
				<div class="form-row">
					<label for="solar_template_birthdate">
						<?php esc_html_e( 'Date of birth', 'solar-template' ); ?>
						<span class="account-settings__optional">(<?php esc_html_e( 'optional — birthday offer', 'solar-template' ); ?>)</span>
					</label>
					<input type="date" class="input-text" name="solar_template_birthdate" id="solar_template_birthdate" value="<?php echo esc_attr( AccountSettingsFields::birthdate( $user->ID ) ); ?>" />
				</div>
			</div>

			<?php // Not part of the design handoff's own form — kept as a hidden field so WooCommerce's own required-fields check (account_display_name) still passes on every save. ?>
			<input type="hidden" name="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />

			<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>

			<button type="submit" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'solar-template' ); ?>" class="btn btn--dark">
				<?php esc_html_e( 'Save changes', 'solar-template' ); ?>
			</button>
		</div>
	</div>

	<div class="account-panel account-settings__panel">
		<div class="account-panel__header">
			<h2><?php esc_html_e( 'Change password', 'solar-template' ); ?></h2>
		</div>
		<div class="account-settings__body">
			<div class="account-settings__row">
				<div class="form-row">
					<label for="password_current"><?php esc_html_e( 'Current password', 'solar-template' ); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="current-password" placeholder="••••••••" />
				</div>
			</div>
			<div class="account-settings__row account-settings__row--double">
				<div class="form-row">
					<label for="password_1"><?php esc_html_e( 'New password', 'solar-template' ); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" placeholder="••••••••" />
				</div>
				<div class="form-row">
					<label for="password_2"><?php esc_html_e( 'Confirm', 'solar-template' ); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" placeholder="••••••••" />
				</div>
			</div>

			<button type="submit" name="save_account_details" value="<?php esc_attr_e( 'Update password', 'solar-template' ); ?>" class="btn btn--dark">
				<?php esc_html_e( 'Update password', 'solar-template' ); ?>
			</button>
		</div>
	</div>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
	<input type="hidden" name="action" value="save_account_details" />

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>

<div class="account-panel account-danger-zone">
	<h2 class="account-danger-zone__title"><?php esc_html_e( 'Danger zone', 'solar-template' ); ?></h2>
	<p class="account-danger-zone__text"><?php esc_html_e( 'Deleting your account is permanent. All of your data, orders and order history will be permanently erased.', 'solar-template' ); ?></p>

	<form method="post" class="account-danger-zone__form" data-confirm="<?php esc_attr_e( 'Are you sure you want to permanently delete your account? This cannot be undone.', 'solar-template' ); ?>">
		<?php wp_nonce_field( AccountDeletion::NONCE_ACTION ); ?>
		<label class="account-danger-zone__confirm">
			<input type="checkbox" name="solar_template_confirm_deletion" value="1" required />
			<?php esc_html_e( 'I understand this action is irreversible.', 'solar-template' ); ?>
		</label>
		<button type="submit" name="solar_template_delete_account" value="1" class="btn btn--danger">
			<?php esc_html_e( 'Delete my account', 'solar-template' ); ?>
		</button>
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
