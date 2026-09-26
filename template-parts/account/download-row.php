<?php
/**
 * Created: 2026-09-26 18:25 CEST
 * Role: Download row template-part (template-parts/account/download-row.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a single downloadable file row (icon, name, order reference, remaining downloads,
 *          download button), from the plain view-model built by
 *          Solar_Template\Account\DownloadsView. Used by woocommerce/myaccount/downloads.php.
 *
 * Expected `$args` keys: see Solar_Template\Account\DownloadsView::map()'s return shape.
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'name'            => '',
	'product_name'    => '',
	'order_number'    => '',
	'extension'       => '',
	'icon'            => '📄',
	'remaining_label' => '',
	'download_url'    => '#',
);

$download_row = wp_parse_args( $args ?? array(), $defaults );
?>
<div class="download-row">
	<div class="download-row__icon" aria-hidden="true"><?php echo esc_html( $download_row['icon'] ); ?></div>
	<div class="download-row__info">
		<div class="download-row__name"><?php echo esc_html( $download_row['name'] ); ?></div>
		<div class="download-row__meta">
			<?php if ( '' !== $download_row['order_number'] ) : ?>
				<?php
				printf(
					/* translators: %s: order number. */
					esc_html__( 'Order #%s', 'solar-template' ),
					esc_html( $download_row['order_number'] )
				);
				?>
				·
			<?php endif; ?>
			<?php echo esc_html( $download_row['extension'] ); ?>
		</div>
	</div>
	<div class="download-row__action">
		<div class="download-row__remaining"><?php echo esc_html( $download_row['remaining_label'] ); ?></div>
		<a class="btn btn--dark" href="<?php echo esc_url( $download_row['download_url'] ); ?>">
			⬇ <?php esc_html_e( 'Download', 'solar-template' ); ?>
		</a>
	</div>
</div>
