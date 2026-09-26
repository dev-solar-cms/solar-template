<?php
/**
 * Created: 2026-09-26 16:10 CEST
 * Role: Order card template-part (template-parts/account/order-card.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a single order card (status header, item thumbnails, actions) for the My Account
 *          orders list, from the plain view-model built by Solar_Template\Account\OrdersView.
 *
 * Expected `$args` keys: see Solar_Template\Account\OrdersView::map_order()'s return shape.
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'number'           => '',
	'date'             => '',
	'total'            => '',
	'status'           => array(
		'badge' => 'pending',
		'icon'  => '',
		'label' => '',
	),
	'item_count'       => 0,
	'thumbnails'       => array(),
	'extra_item_count' => 0,
	'view_url'         => '#',
	'reorder_url'      => null,
);

$order_card = wp_parse_args( $args ?? array(), $defaults );
?>
<div class="order-card">
	<div class="order-card__header order-card__header--<?php echo esc_attr( $order_card['status']['badge'] ); ?>">
		<div class="order-card__meta">
			<div class="order-card__meta-item">
				<span class="order-card__meta-label"><?php esc_html_e( 'Order', 'solar-template' ); ?></span>
				<span class="order-card__meta-value">#<?php echo esc_html( $order_card['number'] ); ?></span>
			</div>
			<div class="order-card__meta-item">
				<span class="order-card__meta-label"><?php esc_html_e( 'Date', 'solar-template' ); ?></span>
				<span class="order-card__meta-value order-card__meta-value--regular"><?php echo esc_html( $order_card['date'] ); ?></span>
			</div>
			<div class="order-card__meta-item">
				<span class="order-card__meta-label"><?php esc_html_e( 'Total', 'solar-template' ); ?></span>
				<span class="order-card__meta-value"><?php echo wp_kses_post( $order_card['total'] ); ?></span>
			</div>
		</div>
		<span class="order-status-badge order-status-badge--<?php echo esc_attr( $order_card['status']['badge'] ); ?>">
			<?php echo esc_html( $order_card['status']['icon'] . ' ' . $order_card['status']['label'] ); ?>
		</span>
	</div>

	<div class="order-card__body">
		<?php if ( ! empty( $order_card['thumbnails'] ) ) : ?>
			<div class="order-card__thumbnails">
				<?php foreach ( $order_card['thumbnails'] as $thumbnail_url ) : ?>
					<img class="order-card__thumbnail" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="" loading="lazy" />
				<?php endforeach; ?>
				<?php if ( $order_card['extra_item_count'] > 0 ) : ?>
					<span class="order-card__extra-count">
						<?php
						printf(
							/* translators: %d: number of additional items in the order, not shown as a thumbnail. */
							esc_html__( '+%d item', 'solar-template' ),
							(int) $order_card['extra_item_count']
						);
						?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="order-card__actions">
			<a class="btn btn--outline" href="<?php echo esc_url( $order_card['view_url'] ); ?>"><?php esc_html_e( 'View detail', 'solar-template' ); ?></a>
			<?php if ( $order_card['reorder_url'] ) : ?>
				<a class="btn btn--primary" href="<?php echo esc_url( $order_card['reorder_url'] ); ?>"><?php esc_html_e( 'Reorder', 'solar-template' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
