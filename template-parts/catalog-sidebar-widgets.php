<?php
/**
 * Created: 2026-09-26 12:45 CEST
 * Role: Catalog sidebar widgets template-part (template-parts/catalog-sidebar-widgets.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the catalog's optional sidebar column (Category/Price/Color/Brand/Rating, whichever
 *          Solar_Template\Admin\ProductsSettings enables) as plain toggle links — works without
 *          JavaScript, reusing the exact same Solar_Template\Catalog\CatalogFilters URL-building/
 *          query mechanism as the top filter bar and "Active filters" chip row, so a link here
 *          genuinely filters the same real catalog query. Renders nothing for a widget with no real
 *          option to offer (same graceful-degradation convention as the rest of the catalog).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Catalog\CatalogFilters;
use Solar_Template\Catalog\CatalogOptions;
use Solar_Template\Catalog\CatalogSidebar;

$widgets = array_filter(
	CatalogSidebar::widgets(),
	static function ( array $widget ): bool {
		return $widget['has_options'];
	}
);

if ( empty( $widgets ) ) {
	return;
}

$active_filters = CatalogFilters::active();
?>
<aside class="catalog-widgets">
	<?php foreach ( $widgets as $widget ) : ?>
		<?php if ( 'category' === $widget['key'] ) : ?>
			<div class="catalog-widgets__widget">
				<h3 class="catalog-widgets__title"><?php esc_html_e( 'Category', 'solar-template' ); ?></h3>
				<ul class="catalog-widgets__list">
					<?php foreach ( CatalogOptions::category_options() as $option ) : ?>
						<?php $is_active = in_array( $option['slug'], $active_filters['category'], true ); ?>
						<li>
							<a
								class="catalog-widgets__link<?php echo $is_active ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( $is_active ? CatalogFilters::remove_url( $active_filters, 'category', $option['slug'] ) : CatalogFilters::url( array_merge( $active_filters, array( 'category' => array_merge( $active_filters['category'], array( $option['slug'] ) ) ) ) ) ); ?>"
							>
								<?php
								printf(
									/* translators: 1: category name, 2: number of products in that category. */
									esc_html__( '%1$s (%2$d)', 'solar-template' ),
									esc_html( $option['name'] ),
									(int) $option['count']
								);
								?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php elseif ( 'price' === $widget['key'] ) : ?>
			<?php $price_bounds = CatalogOptions::price_bounds(); ?>
			<div class="catalog-widgets__widget">
				<h3 class="catalog-widgets__title"><?php esc_html_e( 'Price', 'solar-template' ); ?></h3>
				<ul class="catalog-widgets__list">
					<?php foreach ( CatalogSidebar::price_brackets( $price_bounds['min'], $price_bounds['max'] ) as $bracket ) : ?>
						<?php
						$is_active = $active_filters['min_price'] === $bracket['min'] && $active_filters['max_price'] === $bracket['max'];
						$link_url  = $is_active
							? CatalogFilters::remove_url( $active_filters, 'price', '' )
							: CatalogFilters::url(
								array_merge(
									$active_filters,
									array(
										'min_price' => $bracket['min'],
										'max_price' => $bracket['max'],
									)
								)
							);
						?>
						<li>
							<a class="catalog-widgets__link<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $link_url ); ?>">
								<?php echo esc_html( CatalogFilters::price_chip_label( $bracket['min'], $bracket['max'] ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php elseif ( 'color' === $widget['key'] ) : ?>
			<div class="catalog-widgets__widget">
				<h3 class="catalog-widgets__title"><?php esc_html_e( 'Color', 'solar-template' ); ?></h3>
				<ul class="catalog-widgets__list">
					<?php foreach ( CatalogOptions::attribute_options( CatalogOptions::color_attribute_slug() ) as $option ) : ?>
						<?php $is_active = in_array( $option['slug'], $active_filters['color'], true ); ?>
						<li>
							<a
								class="catalog-widgets__link<?php echo $is_active ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( $is_active ? CatalogFilters::remove_url( $active_filters, 'color', $option['slug'] ) : CatalogFilters::url( array_merge( $active_filters, array( 'color' => array_merge( $active_filters['color'], array( $option['slug'] ) ) ) ) ) ); ?>"
							>
								<?php echo esc_html( $option['name'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php elseif ( 'brand' === $widget['key'] ) : ?>
			<div class="catalog-widgets__widget">
				<h3 class="catalog-widgets__title"><?php esc_html_e( 'Brand', 'solar-template' ); ?></h3>
				<ul class="catalog-widgets__list">
					<?php foreach ( CatalogOptions::attribute_options( CatalogOptions::brand_attribute_slug() ) as $option ) : ?>
						<?php $is_active = in_array( $option['slug'], $active_filters['brand'], true ); ?>
						<li>
							<a
								class="catalog-widgets__link<?php echo $is_active ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( $is_active ? CatalogFilters::remove_url( $active_filters, 'brand', $option['slug'] ) : CatalogFilters::url( array_merge( $active_filters, array( 'brand' => array_merge( $active_filters['brand'], array( $option['slug'] ) ) ) ) ) ); ?>"
							>
								<?php echo esc_html( $option['name'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php elseif ( 'rating' === $widget['key'] ) : ?>
			<div class="catalog-widgets__widget">
				<h3 class="catalog-widgets__title"><?php esc_html_e( 'Rating', 'solar-template' ); ?></h3>
				<ul class="catalog-widgets__list">
					<?php foreach ( CatalogOptions::rating_options() as $option ) : ?>
						<?php $is_active = in_array( $option['value'], $active_filters['rating'], true ); ?>
						<li>
							<a
								class="catalog-widgets__link<?php echo $is_active ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( $is_active ? CatalogFilters::remove_url( $active_filters, 'rating', $option['value'] ) : CatalogFilters::url( array_merge( $active_filters, array( 'rating' => array_merge( $active_filters['rating'], array( $option['value'] ) ) ) ) ) ); ?>"
							>
								<?php echo esc_html( $option['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</aside>
