<?php
/**
 * Created: 2026-09-25 10:20 CEST
 * Role: Catalog filter bar template-part (template-parts/catalog-filters.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the sticky filter bar (Category/Price/Color/Size/Rating, and a sort dropdown
 *          aligned to the end of the bar) as a plain native `<form method="get">` — it still
 *          works with JavaScript disabled, submitting a normal page load that
 *          solar_template_apply_catalog_filters_to_main_query() already filters/sorts —
 *          progressively enhanced by assets/js/catalog.js into an AJAX request. The Category/
 *          Price/Color/Size groups are each only rendered when they actually have options to
 *          offer (same graceful-degradation convention as the rest of the theme, e.g. no "Color"
 *          filter when the store has no color attribute), so the bar quietly shrinks rather than
 *          showing an empty/broken control. "Rating" and the sort dropdown have fixed options
 *          with no such data dependency, so they are always offered.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_filters = solar_template_get_active_catalog_filters();
$price_bounds   = solar_template_get_catalog_price_bounds();

$filter_groups = array();

$category_options = solar_template_get_catalog_category_options();

if ( ! empty( $category_options ) ) {
	$filter_groups[] = array(
		'key'     => 'category',
		'label'   => __( 'Category', 'solar-template' ),
		'type'    => 'checkbox',
		'name'    => 'filter_category',
		'options' => array_map(
			static function ( array $option ): array {
				return array(
					'value' => $option['slug'],
					/* translators: 1: category name, 2: number of products in that category. */
					'label' => sprintf( __( '%1$s (%2$d)', 'solar-template' ), $option['name'], $option['count'] ),
				);
			},
			$category_options
		),
		'active'  => $active_filters['category'],
	);
}

if ( $price_bounds['max'] > 0 ) {
	$filter_groups[] = array(
		'key'    => 'price',
		'label'  => __( 'Price', 'solar-template' ),
		'type'   => 'range',
		'bounds' => $price_bounds,
		'active' => array(
			'min' => $active_filters['min_price'],
			'max' => $active_filters['max_price'],
		),
	);
}

$color_options = solar_template_get_catalog_attribute_options( solar_template_catalog_color_attribute_slug() );

if ( ! empty( $color_options ) ) {
	$filter_groups[] = array(
		'key'     => 'color',
		'label'   => __( 'Color', 'solar-template' ),
		'type'    => 'checkbox',
		'name'    => 'filter_color',
		'options' => array_map(
			static function ( array $option ): array {
				return array(
					'value' => $option['slug'],
					'label' => $option['name'],
				);
			},
			$color_options
		),
		'active'  => $active_filters['color'],
	);
}

$size_options = solar_template_get_catalog_attribute_options( solar_template_catalog_size_attribute_slug() );

if ( ! empty( $size_options ) ) {
	$filter_groups[] = array(
		'key'     => 'size',
		'label'   => __( 'Size', 'solar-template' ),
		'type'    => 'checkbox',
		'name'    => 'filter_size',
		'options' => array_map(
			static function ( array $option ): array {
				return array(
					'value' => $option['slug'],
					'label' => $option['name'],
				);
			},
			$size_options
		),
		'active'  => $active_filters['size'],
	);
}

// Fixed options, no store data dependency (unlike the groups above) — always offered on this page
// (archive-product.php is only ever reached when WooCommerce is active).
$filter_groups[] = array(
	'key'     => 'rating',
	'label'   => __( 'Rating', 'solar-template' ),
	'type'    => 'checkbox',
	'name'    => 'filter_rating',
	'options' => array_map(
		static function ( array $option ): array {
			return array(
				'value' => (string) $option['value'],
				'label' => $option['label'],
			);
		},
		solar_template_get_catalog_rating_options()
	),
	'active'  => array_map( 'strval', $active_filters['rating'] ),
);

?>
<form id="catalog-filters" class="catalog-filters catalog-filters--<?php echo esc_attr( solar_template_catalog_filters_position() ); ?>" method="get">
	<div class="catalog-filters__inner">
	<div class="catalog-filters__bar">
		<?php foreach ( $filter_groups as $group ) : ?>
			<?php
			$group_is_active   = solar_template_catalog_filter_group_is_active( $group );
			$group_has_counter = 'checkbox' === $group['type'] && $group_is_active;
			?>
			<div class="catalog-filters__group" data-filter-group="<?php echo esc_attr( $group['key'] ); ?>">
				<button
					type="button"
					class="catalog-filters__toggle<?php echo $group_is_active ? ' is-active' : ''; ?>"
					aria-expanded="false"
					aria-controls="catalog-filter-panel-<?php echo esc_attr( $group['key'] ); ?>"
				>
					<?php echo esc_html( $group['label'] ); ?>
					<span class="catalog-filters__toggle-count" <?php echo $group_has_counter ? '' : 'hidden'; ?>>
						<?php echo $group_has_counter ? esc_html( '· ' . count( $group['active'] ) ) : ''; ?>
					</span>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
						<path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>

				<div class="catalog-filters__panel" id="catalog-filter-panel-<?php echo esc_attr( $group['key'] ); ?>" hidden>
					<?php if ( 'checkbox' === $group['type'] ) : ?>
						<?php foreach ( $group['options'] as $option ) : ?>
							<label class="catalog-filters__option">
								<input
									type="checkbox"
									name="<?php echo esc_attr( $group['name'] ); ?>[]"
									value="<?php echo esc_attr( $option['value'] ); ?>"
									<?php echo checked( in_array( (string) $option['value'], (array) $group['active'], true ), true, false ); ?>
								/>
								<span><?php echo esc_html( $option['label'] ); ?></span>
							</label>
						<?php endforeach; ?>
					<?php elseif ( 'range' === $group['type'] ) : ?>
						<div class="catalog-filters__range">
							<label class="catalog-filters__range-field">
								<span><?php esc_html_e( 'Min', 'solar-template' ); ?></span>
								<input
									type="number"
									name="min_price"
									min="<?php echo esc_attr( (string) $group['bounds']['min'] ); ?>"
									max="<?php echo esc_attr( (string) $group['bounds']['max'] ); ?>"
									step="1"
									placeholder="<?php echo esc_attr( (string) $group['bounds']['min'] ); ?>"
									value="<?php echo null !== $group['active']['min'] ? esc_attr( (string) $group['active']['min'] ) : ''; ?>"
								/>
							</label>
							<label class="catalog-filters__range-field">
								<span><?php esc_html_e( 'Max', 'solar-template' ); ?></span>
								<input
									type="number"
									name="max_price"
									min="<?php echo esc_attr( (string) $group['bounds']['min'] ); ?>"
									max="<?php echo esc_attr( (string) $group['bounds']['max'] ); ?>"
									step="1"
									placeholder="<?php echo esc_attr( (string) $group['bounds']['max'] ); ?>"
									value="<?php echo null !== $group['active']['max'] ? esc_attr( (string) $group['active']['max'] ) : ''; ?>"
								/>
							</label>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="catalog-filters__spacer"></div>

		<label class="catalog-filters__sort">
			<span class="screen-reader-text"><?php esc_html_e( 'Sort by', 'solar-template' ); ?></span>
			<select name="catalog_orderby">
				<?php foreach ( solar_template_get_catalog_sort_options() as $sort_option ) : ?>
					<option value="<?php echo esc_attr( $sort_option['value'] ); ?>" <?php echo selected( $active_filters['orderby'], $sort_option['value'], false ); ?>>
						<?php echo esc_html( $sort_option['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
	</div>
	</div>
</form>
