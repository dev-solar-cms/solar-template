<?php
/**
 * Created: 2026-09-26 18:30 CEST
 * Role: My Account downloads override (woocommerce/myaccount/downloads.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the customer's real downloadable products as styled rows (icon, order reference,
 *          remaining downloads, download button) instead of core's plain bullet list, delegating
 *          each row's markup to template-parts/account/download-row.php with a view-model built by
 *          Solar_Template\Account\DownloadsView.
 *
 * Based on WooCommerce core's own myaccount/downloads.php (template version 7.8.0).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Account\DownloadsView;
use Solar_Template\Support\StoreLinks;

$downloads     = WC()->customer->get_downloadable_products();
$has_downloads = (bool) $downloads;

do_action( 'woocommerce_before_account_downloads', $has_downloads );
?>
<h1><?php esc_html_e( 'Downloads', 'solar-template' ); ?></h1>

<?php if ( $has_downloads ) : ?>
	<?php do_action( 'woocommerce_before_available_downloads' ); ?>

	<div class="account-downloads__list">
		<?php foreach ( DownloadsView::rows( $downloads ) as $download_row_args ) : ?>
			<?php get_template_part( 'template-parts/account/download-row', null, $download_row_args ); ?>
		<?php endforeach; ?>
	</div>

	<?php do_action( 'woocommerce_after_available_downloads' ); ?>
<?php else : ?>
	<p class="account-panel__empty">
		<?php esc_html_e( 'No downloads available yet.', 'solar-template' ); ?>
		<a class="btn-link" href="<?php echo esc_url( StoreLinks::shop_url() ); ?>"><?php esc_html_e( 'Browse products', 'solar-template' ); ?></a>
	</p>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_downloads', $has_downloads ); ?>
