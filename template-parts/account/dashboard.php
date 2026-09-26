<?php
/**
 * Created: 2026-09-26 14:35 CEST
 * Role: My Account dashboard template-part (template-parts/account/dashboard.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the dashboard's greeting, 4 stat cards, recent orders table and quick links, from
 *          the plain view-model built by Solar_Template\Account\Dashboard. Used by
 *          woocommerce/myaccount/dashboard.php.
 *
 * Expected `$args` keys:
 * - greeting_name (string)
 * - stats (array{in_progress: int, total: int, wishlist: int, support_requests: int})
 * - orders_url, wishlist_url, addresses_url, downloads_url, sav_url, settings_url (string)
 * - recent_orders (array<int, array{number: string, date: string, item_count: int, status: array, total: string, view_url: string, reorder_url: string|null}>)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'greeting_name' => '',
	'stats'         => array(
		'in_progress'      => 0,
		'total'            => 0,
		'wishlist'         => 0,
		'support_requests' => 0,
	),
	'orders_url'    => '#',
	'wishlist_url'  => '#',
	'addresses_url' => '#',
	'downloads_url' => '#',
	'sav_url'       => '#',
	'settings_url'  => '#',
	'recent_orders' => array(),
);

$data = wp_parse_args( $args ?? array(), $defaults );
?>
<div class="account-dashboard">
	<div class="account-dashboard__heading">
		<h1>
			<?php
			printf(
				/* translators: %s: customer's first name. */
				esc_html__( 'Hello, %s! 👋', 'solar-template' ),
				esc_html( $data['greeting_name'] )
			);
			?>
		</h1>
		<p><?php esc_html_e( 'Welcome to your personal space', 'solar-template' ); ?></p>
	</div>

	<div class="account-stats">
		<a class="account-stat-card" href="<?php echo esc_url( $data['orders_url'] ); ?>">
			<span class="account-stat-card__value account-stat-card__value--gold"><?php echo esc_html( (string) $data['stats']['in_progress'] ); ?></span>
			<span class="account-stat-card__label"><?php esc_html_e( 'In progress', 'solar-template' ); ?></span>
			<span class="account-stat-card__sublabel"><?php esc_html_e( 'orders', 'solar-template' ); ?></span>
		</a>
		<a class="account-stat-card" href="<?php echo esc_url( $data['orders_url'] ); ?>">
			<span class="account-stat-card__value"><?php echo esc_html( (string) $data['stats']['total'] ); ?></span>
			<span class="account-stat-card__label"><?php esc_html_e( 'Total', 'solar-template' ); ?></span>
			<span class="account-stat-card__sublabel"><?php esc_html_e( 'orders placed', 'solar-template' ); ?></span>
		</a>
		<a class="account-stat-card" href="<?php echo esc_url( $data['wishlist_url'] ); ?>">
			<span class="account-stat-card__value"><?php echo esc_html( (string) $data['stats']['wishlist'] ); ?></span>
			<span class="account-stat-card__label"><?php esc_html_e( 'Wishlist', 'solar-template' ); ?></span>
			<span class="account-stat-card__sublabel"><?php esc_html_e( 'saved items', 'solar-template' ); ?></span>
		</a>
		<a class="account-stat-card" href="<?php echo esc_url( $data['sav_url'] ); ?>">
			<span class="account-stat-card__value"><?php echo esc_html( (string) $data['stats']['support_requests'] ); ?></span>
			<span class="account-stat-card__label"><?php esc_html_e( 'Support', 'solar-template' ); ?></span>
			<span class="account-stat-card__sublabel"><?php esc_html_e( 'active requests', 'solar-template' ); ?></span>
		</a>
	</div>

	<div class="account-panel">
		<div class="account-panel__header">
			<h2><?php esc_html_e( 'Recent orders', 'solar-template' ); ?></h2>
			<a class="account-panel__see-all" href="<?php echo esc_url( $data['orders_url'] ); ?>">
				<?php esc_html_e( 'View all →', 'solar-template' ); ?>
			</a>
		</div>

		<?php if ( empty( $data['recent_orders'] ) ) : ?>
			<p class="account-panel__empty"><?php esc_html_e( 'You have not placed any order yet.', 'solar-template' ); ?></p>
		<?php else : ?>
			<div class="account-orders-table__wrapper">
				<table class="account-orders-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Order', 'solar-template' ); ?></th>
							<th><?php esc_html_e( 'Date', 'solar-template' ); ?></th>
							<th><?php esc_html_e( 'Items', 'solar-template' ); ?></th>
							<th><?php esc_html_e( 'Status', 'solar-template' ); ?></th>
							<th class="account-orders-table__amount"><?php esc_html_e( 'Total', 'solar-template' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $data['recent_orders'] as $order_row ) : ?>
							<tr>
								<td><span class="account-orders-table__number">#<?php echo esc_html( $order_row['number'] ); ?></span></td>
								<td><?php echo esc_html( $order_row['date'] ); ?></td>
								<td>
									<?php
									printf(
										/* translators: %d: number of items in the order. */
										esc_html( _n( '%d item', '%d items', $order_row['item_count'], 'solar-template' ) ),
										(int) $order_row['item_count']
									);
									?>
								</td>
								<td>
									<span class="order-status-badge order-status-badge--<?php echo esc_attr( $order_row['status']['badge'] ); ?>">
										<?php echo esc_html( $order_row['status']['label'] ); ?>
									</span>
								</td>
								<td class="account-orders-table__amount"><?php echo wp_kses_post( $order_row['total'] ); ?></td>
								<td>
									<div class="account-orders-table__actions">
										<a class="btn-link" href="<?php echo esc_url( $order_row['view_url'] ); ?>"><?php esc_html_e( 'Detail', 'solar-template' ); ?></a>
										<?php if ( $order_row['reorder_url'] ) : ?>
											<a class="btn-link btn-link--gold" href="<?php echo esc_url( $order_row['reorder_url'] ); ?>"><?php esc_html_e( 'Reorder', 'solar-template' ); ?></a>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>

	<div class="account-quick-links">
		<a class="account-quick-link" href="<?php echo esc_url( $data['addresses_url'] ); ?>">
			<span class="account-quick-link__icon" aria-hidden="true">📍</span>
			<span class="account-quick-link__title"><?php esc_html_e( 'Addresses', 'solar-template' ); ?></span>
		</a>
		<a class="account-quick-link" href="<?php echo esc_url( $data['downloads_url'] ); ?>">
			<span class="account-quick-link__icon" aria-hidden="true">⬇️</span>
			<span class="account-quick-link__title"><?php esc_html_e( 'Downloads', 'solar-template' ); ?></span>
		</a>
		<a class="account-quick-link" href="<?php echo esc_url( $data['settings_url'] ); ?>">
			<span class="account-quick-link__icon" aria-hidden="true">⚙️</span>
			<span class="account-quick-link__title"><?php esc_html_e( 'Settings', 'solar-template' ); ?></span>
		</a>
	</div>
</div>
