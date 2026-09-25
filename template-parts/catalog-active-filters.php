<?php
/**
 * Created: 2026-09-25 10:40 CEST
 * Role: Catalog active filters template-part (template-parts/catalog-active-filters.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the "Active filters" chip row below the filter bar — one removable chip per
 *          active filter value, plus a "Clear all" link — always inside its own
 *          `#catalog-active-filters` wrapper (even when there is nothing active, in which case it
 *          renders empty) so assets/js/catalog.js can always swap it wholesale after an AJAX
 *          filter update without having to insert/remove the wrapper itself. Each chip/the "Clear
 *          all" link is a plain `<a href>` first (works without JavaScript, since
 *          Solar_Template\Catalog\CatalogController::apply_filters_to_main_query() already filters
 *          that URL), then intercepted by assets/js/catalog.js to sync the filter bar's own
 *          controls and submit via AJAX instead of navigating.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type array|null  $filters  See CatalogFilters::active_chips()'s $filters parameter. Only
 *                                   ever passed by the AJAX handler.
 *     @type string|null $base_url See CatalogFilters::url()'s $base_url parameter.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Catalog\CatalogFilters;

$active_filters_args = wp_parse_args(
	$args ?? array(),
	array(
		'filters'  => null,
		'base_url' => null,
	)
);

$chips = CatalogFilters::active_chips( $active_filters_args['filters'], $active_filters_args['base_url'] );
?>
<div class="catalog-active-filters<?php echo ! empty( $chips ) ? ' catalog-active-filters--has-chips' : ''; ?>" id="catalog-active-filters">
	<?php if ( ! empty( $chips ) ) : ?>
		<span class="catalog-active-filters__label"><?php esc_html_e( 'Active filters:', 'solar-template' ); ?></span>

		<?php foreach ( $chips as $chip ) : ?>
			<span class="catalog-active-filters__chip">
				<?php echo esc_html( $chip['label'] ); ?>
				<a
					href="<?php echo esc_url( $chip['url'] ); ?>"
					class="catalog-active-filters__remove"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: filter label. */ __( 'Remove filter: %s', 'solar-template' ), $chip['label'] ) ); ?>"
				>&times;</a>
			</span>
		<?php endforeach; ?>

		<a
			href="<?php echo esc_url( CatalogFilters::clear_url( $active_filters_args['base_url'] ) ); ?>"
			class="catalog-active-filters__clear"
		>
			<?php esc_html_e( 'Clear all', 'solar-template' ); ?>
		</a>
	<?php endif; ?>
</div>
