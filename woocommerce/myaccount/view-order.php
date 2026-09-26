<?php
/**
 * Created: 2026-09-26 16:25 CEST
 * Role: My Account order detail override (woocommerce/myaccount/view-order.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Restyle the order detail page's own heading/status (order number, date, status badge)
 *          and customer order-note history, keeping WooCommerce's own `woocommerce_view_order`
 *          action untouched below it — that hook is what renders the real order details table,
 *          any downloadable file links and the "Order again" button
 *          (`wc_get_template('order/order-details.php')`), reused as-is rather than reimplemented.
 *
 * Based on WooCommerce core's own myaccount/view-order.php (template version 10.6.0).
 *
 * @package Solar_Template
 * @var \WC_Order $order
 * @var int        $order_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\OrderStatusPresenter;

$notes             = $order->get_customer_order_notes();
$status_descriptor = OrderStatusPresenter::describe_order( $order );
?>
<div class="view-order">
	<a class="view-order__back" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
		<?php esc_html_e( '← Back to orders', 'solar-template' ); ?>
	</a>

	<div class="view-order__heading">
		<div>
			<h1>
				<?php
				printf(
					/* translators: %s: order number. */
					esc_html__( 'Order #%s', 'solar-template' ),
					esc_html( $order->get_order_number() )
				);
				?>
			</h1>
			<p class="view-order__date"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></p>
		</div>
		<span class="order-status-badge order-status-badge--<?php echo esc_attr( $status_descriptor['badge'] ); ?>">
			<?php echo esc_html( $status_descriptor['icon'] . ' ' . $status_descriptor['label'] ); ?>
		</span>
	</div>

	<?php if ( $notes ) : ?>
		<div class="account-panel view-order__notes">
			<div class="account-panel__header">
				<h2><?php esc_html_e( 'Order updates', 'solar-template' ); ?></h2>
			</div>
			<ol class="woocommerce-OrderUpdates commentlist notes">
				<?php foreach ( $notes as $note ) : ?>
					<li class="woocommerce-OrderUpdate comment note">
						<div class="woocommerce-OrderUpdate-inner comment_container">
							<div class="woocommerce-OrderUpdate-text comment-text">
								<p class="woocommerce-OrderUpdate-meta meta"><?php echo esc_html( date_i18n( __( 'l jS \o\f F Y, h:ia', 'solar-template' ), strtotime( $note->comment_date ) ) ); ?></p>
								<div class="woocommerce-OrderUpdate-description description">
									<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
								</div>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>

	<?php
	/**
	 * Renders WooCommerce's own order details table, downloadable file links (if any) and "Order
	 * again" button — kept native rather than reimplemented.
	 */
	do_action( 'woocommerce_view_order', $order_id );
	?>
</div>
