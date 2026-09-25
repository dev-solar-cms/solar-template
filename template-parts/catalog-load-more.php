<?php
/**
 * Created: 2026-09-25 11:00 CEST
 * Role: Catalog "load more" template-part (template-parts/catalog-load-more.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the "Showing N of M products" status, its gold progress bar, and the
 *          "Load more products" button/link, for whichever `$wp_query` is currently the main
 *          query. Always rendered inside its own `#catalog-load-more` wrapper (even when there is
 *          no further page, in which case it renders empty), same convention as
 *          template-parts/catalog-active-filters.php, so assets/js/catalog.js can always replace
 *          it wholesale. The button is a plain link to the next page first (works without
 *          JavaScript, submitting a normal page load — though that shows only that page's
 *          products, not page 1 plus it, since appending without replacing requires JavaScript by
 *          nature), progressively enhanced into an AJAX request that appends that page's cards to
 *          the existing grid instead of navigating.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type string|null $base_url See solar_template_catalog_filters_url()'s $base_url parameter.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$load_more_args = wp_parse_args( $args ?? array(), array( 'base_url' => null ) );
$load_more      = solar_template_get_catalog_load_more_config( $wp_query, $load_more_args['base_url'] );
?>
<div class="catalog__load-more" id="catalog-load-more">
	<?php if ( null !== $load_more ) : ?>
		<p class="catalog__load-more-status">
			<?php
			printf(
				/* translators: 1: number of products currently shown, 2: total number of products. */
				esc_html__( 'Showing %1$d of %2$d products', 'solar-template' ),
				(int) $load_more['shown'],
				(int) $load_more['total']
			);
			?>
		</p>

		<div class="catalog__load-more-track">
			<div class="catalog__load-more-fill" style="width: <?php echo esc_attr( (string) $load_more['percent'] ); ?>%;"></div>
		</div>

		<a
			href="<?php echo esc_url( $load_more['next_page_url'] ); ?>"
			class="btn btn--dark catalog__load-more-button"
			data-load-more
			data-page="<?php echo esc_attr( (string) $load_more['next_page'] ); ?>"
		>
			<?php esc_html_e( 'Load more products', 'solar-template' ); ?>
		</a>
	<?php endif; ?>
</div>
