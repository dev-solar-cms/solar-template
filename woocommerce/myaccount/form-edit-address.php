<?php
/**
 * Created: 2026-09-26 18:00 CEST
 * Role: My Account address edit override (woocommerce/myaccount/form-edit-address.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a billing/shipping summary card pair above the real address edit form, styled
 *          in a 2-column grid via the checkout's own field layout helper
 *          (Solar_Template\Checkout\CheckoutFieldsLayout::rows(), fully reusable here since it only
 *          inspects field keys/order, not a checkout-specific field shape). Adapted from the design
 *          handoff's own "grid of address cards, each linking to its own edit page" concept to this
 *          WooCommerce version's real routing: `edit-address` always resolves to editing one type
 *          (billing by default) on this same page, never a separate listing page, so both summary
 *          cards stay visible while the form below switches between billing/shipping.
 *
 * Based on WooCommerce core's own myaccount/form-edit-address.php (template version 9.3.0).
 *
 * @package Solar_Template
 * @var string $load_address One of 'billing', 'shipping'.
 * @var array  $address      Fields for $load_address, as returned by `WC()->countries->get_address_fields()`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\AddressesView;
use Solar_Template\Checkout\CheckoutFieldsLayout;

// The bare `/my-account/edit-address/` URL (no billing/shipping segment) reaches this template
// with an empty $load_address (WooCommerce's own `WC_Shortcode_My_Account::edit_address()` default
// parameter never applies: the "edit-address" endpoint's query var is always explicitly passed,
// even as an empty string) and an $address array built from that same empty prefix — redirect to
// the billing sub-page rather than ever rendering fields keyed on an empty prefix.
if ( ! $load_address ) {
	wp_safe_redirect( wc_get_endpoint_url( 'edit-address', 'billing', wc_get_page_permalink( 'myaccount' ) ) );
	exit;
}

$page_title = ( 'billing' === $load_address ) ? __( 'Billing address', 'solar-template' ) : __( 'Shipping address', 'solar-template' );

do_action( 'woocommerce_before_edit_account_address_form' );
?>
<h1><?php esc_html_e( 'My addresses', 'solar-template' ); ?></h1>

<div class="account-addresses__grid">
	<?php foreach ( AddressesView::summaries( get_current_user_id(), $load_address ) as $summary ) : ?>
		<div class="address-card<?php echo $summary['is_active'] ? ' is-active' : ''; ?>">
			<?php if ( $summary['is_active'] ) : ?>
				<div class="address-card__badge"><?php esc_html_e( 'Editing', 'solar-template' ); ?></div>
			<?php endif; ?>
			<div class="address-card__label"><?php echo esc_html( $summary['label'] ); ?></div>
			<?php if ( $summary['is_empty'] ) : ?>
				<p class="address-card__empty"><?php esc_html_e( 'No address saved yet.', 'solar-template' ); ?></p>
			<?php else : ?>
				<?php if ( '' !== $summary['name'] ) : ?>
					<div class="address-card__name"><?php echo esc_html( $summary['name'] ); ?></div>
				<?php endif; ?>
				<div class="address-card__lines"><?php echo wp_kses_post( $summary['formatted_address'] ); ?></div>
			<?php endif; ?>
			<?php if ( ! $summary['is_active'] ) : ?>
				<a class="btn-link" href="<?php echo esc_url( $summary['edit_url'] ); ?>"><?php esc_html_e( 'Edit', 'solar-template' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>

<div class="account-panel account-addresses__form">
	<div class="account-panel__header">
		<h2><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?></h2>
	</div>

	<form method="post" class="account-form" novalidate>
		<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

		<div class="account-addresses__rows">
			<?php foreach ( CheckoutFieldsLayout::rows( $address ) as $row ) : ?>
				<div class="account-addresses__row<?php echo count( $row ) > 1 ? ' account-addresses__row--double' : ''; ?>">
					<?php foreach ( $row as $column ) : ?>
						<?php woocommerce_form_field( $column['key'], $column['field'], wc_get_post_data_by_key( $column['key'], $column['field']['value'] ) ); ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

		<p>
			<button type="submit" class="btn btn--dark" name="save_address" value="<?php esc_attr_e( 'Save address', 'solar-template' ); ?>">
				<?php esc_html_e( 'Save address', 'solar-template' ); ?>
			</button>
			<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
			<input type="hidden" name="action" value="edit_address" />
		</p>
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
