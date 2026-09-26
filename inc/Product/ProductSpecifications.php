<?php
/**
 * Created: 2026-09-26 10:10 CEST
 * Role: Product page specifications tab data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the product page's "Specifications" tab rows (weight, dimensions, then every
 *          visible product attribute) from real WooCommerce product data, following the same
 *          selection rules as WooCommerce's own `wc_display_product_attributes()` — reimplemented
 *          rather than called directly since that core function echoes a full WooCommerce template
 *          instead of returning plain data, and this theme needs the raw rows to render its own
 *          markup.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the product page's specifications table rows from a WC_Product.
 */
final class ProductSpecifications {

	/**
	 * @param \WC_Product $product Product to read specifications from.
	 * @return array<int, array{label: string, value: string}> Empty when the product has no weight,
	 *                                                            no dimensions and no visible
	 *                                                            attribute at all.
	 */
	public static function rows( \WC_Product $product ): array {
		$rows = array();

		/**
		 * Whether to display the weight/dimensions rows, same core filter
		 * `wc_display_product_attributes()` runs.
		 *
		 * @param bool $enabled Whether the product has a weight or dimensions set.
		 */
		$display_dimensions = apply_filters(
			'wc_product_enable_dimensions_display',
			$product->has_weight() || $product->has_dimensions()
		);

		if ( $display_dimensions && $product->has_weight() ) {
			$rows[] = array(
				'label' => __( 'Weight', 'solar-template' ),
				'value' => wc_format_weight( $product->get_weight() ),
			);
		}

		if ( $display_dimensions && $product->has_dimensions() ) {
			$rows[] = array(
				'label' => __( 'Dimensions', 'solar-template' ),
				'value' => wc_format_dimensions( $product->get_dimensions( false ) ),
			);
		}

		foreach ( array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' ) as $attribute ) {
			$rows[] = array(
				'label' => wc_attribute_label( $attribute->get_name() ),
				'value' => self::attribute_value( $product, $attribute ),
			);
		}

		/**
		 * Filters the product page's specifications table rows.
		 *
		 * @param array<int, array{label: string, value: string}> $rows    See this method's return type.
		 * @param \WC_Product                                      $product Product the rows were built for.
		 */
		return apply_filters( 'solar_template_product_specifications', $rows, $product );
	}

	/**
	 * Reads a single attribute's display value: its assigned term names for a taxonomy attribute,
	 * its plain option list otherwise.
	 *
	 * @param \WC_Product           $product   Product the attribute belongs to.
	 * @param \WC_Product_Attribute $attribute Attribute to read.
	 * @return string
	 */
	private static function attribute_value( \WC_Product $product, \WC_Product_Attribute $attribute ): string {
		if ( $attribute->is_taxonomy() ) {
			$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );

			return implode( ', ', $terms );
		}

		return implode( ', ', $attribute->get_options() );
	}
}
