<?php
/**
 * Created: 2026-09-26 16:15 CEST
 * Role: My Account orders list template-part (template-parts/account/orders-list.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the status tabs, order cards and pagination for the orders list page, from the
 *          plain view-model built by Solar_Template\Account\OrdersView. Used by
 *          woocommerce/myaccount/orders.php.
 *
 * Expected `$args` keys:
 * - tabs (array<int, array{key: string, label: string, count: int, url: string, is_active: bool}>)
 * - orders (array<int, array>) each shaped like Solar_Template\Account\OrdersView::map_order()'s return
 * - current_page, max_pages (int)
 * - base_url (string) orders page URL, pagination links are built against it
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'tabs'         => array(),
	'orders'       => array(),
	'current_page' => 1,
	'max_pages'    => 1,
	'base_url'     => '#',
);

$data = wp_parse_args( $args ?? array(), $defaults );
?>
<h1><?php esc_html_e( 'Orders', 'solar-template' ); ?></h1>

<div class="account-orders__tabs">
	<?php foreach ( $data['tabs'] as $tab_item ) : ?>
		<a
			class="account-orders__tab<?php echo $tab_item['is_active'] ? ' is-active' : ''; ?>"
			href="<?php echo esc_url( $tab_item['url'] ); ?>"
		>
			<?php echo esc_html( $tab_item['label'] ); ?> (<?php echo esc_html( (string) $tab_item['count'] ); ?>)
		</a>
	<?php endforeach; ?>
</div>

<?php if ( empty( $data['orders'] ) ) : ?>
	<p class="account-panel__empty"><?php esc_html_e( 'No order matches this selection.', 'solar-template' ); ?></p>
<?php else : ?>
	<div class="account-orders__list">
		<?php foreach ( $data['orders'] as $order_row ) : ?>
			<?php get_template_part( 'template-parts/account/order-card', null, $order_row ); ?>
		<?php endforeach; ?>
	</div>

	<?php if ( $data['max_pages'] > 1 ) : ?>
		<nav class="account-orders__pagination" aria-label="<?php esc_attr_e( 'Orders pagination', 'solar-template' ); ?>">
			<?php if ( $data['current_page'] > 1 ) : ?>
				<a class="btn-link" href="<?php echo esc_url( add_query_arg( 'orders-page', $data['current_page'] - 1, $data['base_url'] ) ); ?>">
					<?php esc_html_e( 'Previous', 'solar-template' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $data['current_page'] < $data['max_pages'] ) : ?>
				<a class="btn-link" href="<?php echo esc_url( add_query_arg( 'orders-page', $data['current_page'] + 1, $data['base_url'] ) ); ?>">
					<?php esc_html_e( 'Next', 'solar-template' ); ?>
				</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
<?php endif; ?>
