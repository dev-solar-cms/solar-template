<?php
/**
 * Created: 2026-09-26 13:00 CEST
 * Role: "Products" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Catalog grid columns/per-page count, filter bar position, optional sidebar widgets,
 *          related products, and the custom engraving section's site-wide defaults — every field
 *          wired for real onto Group 04 (catalog, via Solar_Template\Catalog\CatalogOptions'
 *          existing filters) and Group 05 (product page, via Solar_Template\Product\ProductRelated/
 *          Solar_Template\Product\ProductEngraving).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Products" settings tab.
 */
final class ProductsSettings implements SettingsTabInterface {

	private const FILTERS_POSITIONS = array( 'top', 'sidebar-left', 'sidebar-right' );

	private const SIDEBAR_WIDGETS = array(
		'category' => 'Category',
		'price'    => 'Price',
		'color'    => 'Color',
		'brand'    => 'Brand',
		'rating'   => 'Rating',
	);

	private const ENGRAVING_FIELD_TYPES = array( 'text', 'textarea' );

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'products';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Products', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-products';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		return array(
			'columns'                 => '4',
			'products_per_page'       => '16',
			'filters_position'        => 'top',
			'sidebar_enabled'         => '0',
			'sidebar_widgets'         => implode( ',', array_keys( self::SIDEBAR_WIDGETS ) ),
			'related_enabled'         => '1',
			'related_count'           => '4',
			'engraving_enabled'       => '1',
			'engraving_label'         => '',
			'engraving_field_type'    => 'text',
			'engraving_default_price' => (string) 25.0,
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$columns       = isset( $raw['columns'] ) ? absint( $raw['columns'] ) : 4;
		$per_page      = isset( $raw['products_per_page'] ) ? absint( $raw['products_per_page'] ) : 16;
		$position      = isset( $raw['filters_position'] ) ? sanitize_key( (string) $raw['filters_position'] ) : 'top';
		$related_count = isset( $raw['related_count'] ) ? absint( $raw['related_count'] ) : 4;
		$field_type    = isset( $raw['engraving_field_type'] ) ? sanitize_key( (string) $raw['engraving_field_type'] ) : 'text';
		$default_price = isset( $raw['engraving_default_price'] ) ? (float) $raw['engraving_default_price'] : 25.0;

		$widgets = isset( $raw['sidebar_widgets'] ) && is_array( $raw['sidebar_widgets'] )
			? array_values( array_intersect( array_map( 'sanitize_key', $raw['sidebar_widgets'] ), array_keys( self::SIDEBAR_WIDGETS ) ) )
			: array();

		return array(
			'columns'                 => (string) max( 2, min( 6, $columns ) ),
			'products_per_page'       => (string) max( 4, $per_page ),
			'filters_position'        => in_array( $position, self::FILTERS_POSITIONS, true ) ? $position : 'top',
			'sidebar_enabled'         => ! empty( $raw['sidebar_enabled'] ) ? '1' : '0',
			'sidebar_widgets'         => implode( ',', $widgets ),
			'related_enabled'         => ! empty( $raw['related_enabled'] ) ? '1' : '0',
			'related_count'           => (string) max( 1, min( 12, $related_count ) ),
			'engraving_enabled'       => ! empty( $raw['engraving_enabled'] ) ? '1' : '0',
			'engraving_label'         => isset( $raw['engraving_label'] ) ? sanitize_text_field( (string) $raw['engraving_label'] ) : '',
			'engraving_field_type'    => in_array( $field_type, self::ENGRAVING_FIELD_TYPES, true ) ? $field_type : 'text',
			'engraving_default_price' => (string) max( 0.0, $default_price ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		$enabled_widgets = array_filter( explode( ',', $values['products.sidebar_widgets'] ) );
		?>
		<tr>
			<th><?php esc_html_e( 'Columns', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__radio-group">
					<?php foreach ( array( '2', '3', '4', '5', '6' ) as $count ) : ?>
						<label><input type="radio" name="columns" value="<?php echo esc_attr( $count ); ?>" <?php checked( $values['products.columns'], $count ); ?> /> <?php echo esc_html( $count ); ?></label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="st-products-per-page"><?php esc_html_e( 'Products per page', 'solar-template' ); ?></label></th>
			<td>
				<input type="number" id="st-products-per-page" name="products_per_page" value="<?php echo esc_attr( $values['products.products_per_page'] ); ?>" min="4" step="4" class="small-text" />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Filter bar position', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__radio-group">
					<label><input type="radio" name="filters_position" value="top" <?php checked( $values['products.filters_position'], 'top' ); ?> /> <?php esc_html_e( 'Top', 'solar-template' ); ?></label>
					<label><input type="radio" name="filters_position" value="sidebar-left" <?php checked( $values['products.filters_position'], 'sidebar-left' ); ?> /> <?php esc_html_e( 'Sidebar left', 'solar-template' ); ?></label>
					<label><input type="radio" name="filters_position" value="sidebar-right" <?php checked( $values['products.filters_position'], 'sidebar-right' ); ?> /> <?php esc_html_e( 'Sidebar right', 'solar-template' ); ?></label>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Sidebar', 'solar-template' ); ?></th>
			<td>
				<label style="display:block;margin-bottom:10px;">
					<input type="checkbox" class="solar-template-settings__toggle" name="sidebar_enabled" value="1" <?php checked( $values['products.sidebar_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
				<div class="solar-template-settings__checkbox-group">
					<?php foreach ( self::SIDEBAR_WIDGETS as $widget_key => $widget_label ) : ?>
						<label>
							<input type="checkbox" name="sidebar_widgets[]" value="<?php echo esc_attr( $widget_key ); ?>" <?php checked( in_array( $widget_key, $enabled_widgets, true ) ); ?> />
							<?php echo esc_html( $widget_label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description"><?php esc_html_e( 'A sidebar column also appears whenever the filter bar position above is set to a sidebar, even with this disabled.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Related products', 'solar-template' ); ?></th>
			<td>
				<label style="display:block;margin-bottom:10px;">
					<input type="checkbox" class="solar-template-settings__toggle" name="related_enabled" value="1" <?php checked( $values['products.related_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
				<label>
					<?php esc_html_e( 'Number shown', 'solar-template' ); ?>
					<input type="number" name="related_count" value="<?php echo esc_attr( $values['products.related_count'] ); ?>" min="1" max="12" class="small-text" />
				</label>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Custom engraving', 'solar-template' ); ?></th>
			<td>
				<label style="display:block;margin-bottom:10px;">
					<input type="checkbox" class="solar-template-settings__toggle" name="engraving_enabled" value="1" <?php checked( $values['products.engraving_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled site-wide', 'solar-template' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'A product also needs its own "Custom engraving" toggle (Product data > General) to actually offer it.', 'solar-template' ); ?></p>
				<p>
					<label for="st-engraving-label"><?php esc_html_e( 'Section label', 'solar-template' ); ?></label><br />
					<input type="text" id="st-engraving-label" name="engraving_label" value="<?php echo esc_attr( $values['products.engraving_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Custom engraving', 'solar-template' ); ?>" />
				</p>
				<p>
					<?php esc_html_e( 'Field type', 'solar-template' ); ?><br />
					<label><input type="radio" name="engraving_field_type" value="text" <?php checked( $values['products.engraving_field_type'], 'text' ); ?> /> <?php esc_html_e( 'Single line', 'solar-template' ); ?></label>
					<label><input type="radio" name="engraving_field_type" value="textarea" <?php checked( $values['products.engraving_field_type'], 'textarea' ); ?> /> <?php esc_html_e( 'Multiple lines', 'solar-template' ); ?></label>
				</p>
				<p>
					<label for="st-engraving-price"><?php printf( /* translators: %s: currency symbol. */ esc_html__( 'Default surcharge (%s)', 'solar-template' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></label><br />
					<input type="number" id="st-engraving-price" name="engraving_default_price" value="<?php echo esc_attr( $values['products.engraving_default_price'] ); ?>" min="0" step="0.01" class="small-text" />
				</p>
				<p class="description"><?php esc_html_e( 'Applies to any product with no surcharge of its own set.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Filter callback for `solar_template_catalog_columns`.
	 *
	 * @param int $default_columns Value the filter was called with.
	 * @return int
	 */
	public static function apply_columns( int $default_columns ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_columns is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return (int) SettingsRepository::get( 'products.columns', '4' );
	}

	/**
	 * Filter callback for `loop_shop_per_page`.
	 *
	 * @param int $default_per_page Value the filter was called with.
	 * @return int
	 */
	public static function apply_products_per_page( int $default_per_page ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_per_page is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return (int) SettingsRepository::get( 'products.products_per_page', '16' );
	}

	/**
	 * Filter callback for `solar_template_catalog_filters_position`.
	 *
	 * @param string $default_position Value the filter was called with.
	 * @return string
	 */
	public static function apply_filters_position( string $default_position ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_position is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return SettingsRepository::get( 'products.filters_position', 'top' );
	}

	/**
	 * Filter callback for `solar_template_catalog_sidebar_enabled`.
	 *
	 * @param bool $default_enabled Value the filter was called with.
	 * @return bool
	 */
	public static function apply_sidebar_enabled( bool $default_enabled ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_enabled is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return '1' === SettingsRepository::get( 'products.sidebar_enabled', '0' );
	}

	/**
	 * Filter callback for `solar_template_catalog_sidebar_widgets`.
	 *
	 * @param array<int, string> $default_widgets Value the filter was called with.
	 * @return array<int, string>
	 */
	public static function apply_sidebar_widgets( array $default_widgets ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_widgets is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		$stored = SettingsRepository::get( 'products.sidebar_widgets', implode( ',', array_keys( self::SIDEBAR_WIDGETS ) ) );

		return array_filter( explode( ',', $stored ) );
	}

	/**
	 * @return bool Whether the product page's "Related products" section is enabled.
	 */
	public static function is_related_enabled(): bool {
		return '1' === SettingsRepository::get( 'products.related_enabled', '1' );
	}

	/**
	 * @return int Number of related products to show.
	 */
	public static function related_count(): int {
		return (int) SettingsRepository::get( 'products.related_count', '4' );
	}

	/**
	 * Filter callback for `solar_template_engraving_enabled`.
	 *
	 * @param bool $default_enabled Value the filter was called with.
	 * @return bool
	 */
	public static function apply_engraving_enabled( bool $default_enabled ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_enabled is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return '1' === SettingsRepository::get( 'products.engraving_enabled', '1' );
	}

	/**
	 * Filter callback for `solar_template_engraving_label`.
	 *
	 * @param string $default_label Value the filter was called with.
	 * @return string
	 */
	public static function apply_engraving_label( string $default_label ): string {
		$configured = SettingsRepository::get( 'products.engraving_label', '' );

		return '' !== $configured ? $configured : $default_label;
	}

	/**
	 * Filter callback for `solar_template_engraving_field_type`.
	 *
	 * @param string $default_type Value the filter was called with.
	 * @return string
	 */
	public static function apply_engraving_field_type( string $default_type ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_type is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return SettingsRepository::get( 'products.engraving_field_type', 'text' );
	}

	/**
	 * Filter callback for `solar_template_engraving_default_price`.
	 *
	 * @param float $default_price Value the filter was called with.
	 * @return float
	 */
	public static function apply_engraving_default_price( float $default_price ): float { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_price is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return (float) SettingsRepository::get( 'products.engraving_default_price', '25' );
	}
}
