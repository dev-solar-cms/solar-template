<?php
/**
 * Created: 2026-09-25 16:45 CEST
 * Role: Variable product variation data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the product panel's Color/Size selector groups and the JSON-able variation
 *          payload assets/js/product.js resolves a selection against, from a WC_Product_Variable's
 *          real WooCommerce variations — per the design handoff's integration note #2, price/
 *          availability recalculation is WooCommerce's own native variation mechanism, this class
 *          only shapes that data for display. Reuses
 *          Solar_Template\Catalog\CatalogOptions::color_attribute_slug()/size_attribute_slug(): the
 *          same "which attribute taxonomy is Color/Size" question the catalog filter bar already
 *          answers, so a store only configures it once.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

use Solar_Template\Catalog\CatalogOptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds a variable product's selector groups and JS variation payload.
 */
final class ProductVariations {

	/**
	 * @param \WC_Product $product Product to check.
	 * @return bool True when the product is a WooCommerce variable product.
	 */
	public static function is_variable( \WC_Product $product ): bool {
		return $product instanceof \WC_Product_Variable;
	}

	/**
	 * Returns the Color/Size selector groups actually used by the product's variations (a product
	 * varying only by size has no Color group, and vice versa); a variation attribute taxonomy
	 * other than Color/Size is not built into the UI — no selector is specified for it beyond the
	 * mockup's own two dimensions.
	 *
	 * @param \WC_Product_Variable $product Product to build selector groups for.
	 * @return array<int, array{taxonomy: string, type: string, label: string, options: array<int, array{slug: string, name: string, color: string|null, available: bool}>}>
	 */
	public static function attribute_groups( \WC_Product_Variable $product ): array {
		$variation_attributes = $product->get_variation_attributes();
		$variations           = self::variations_payload( $product );
		$groups               = array();

		foreach (
			array(
				CatalogOptions::color_attribute_slug() => 'color',
				CatalogOptions::size_attribute_slug()  => 'size',
			) as $taxonomy => $type
		) {
			if ( empty( $variation_attributes[ $taxonomy ] ) ) {
				continue;
			}

			$options = array();

			foreach ( $variation_attributes[ $taxonomy ] as $slug ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );

				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}

				$options[] = array(
					'slug'      => $term->slug,
					'name'      => $term->name,
					'color'     => 'color' === $type ? ColorSwatch::hex_for_term( $term ) : null,
					'available' => self::option_is_available( $variations, $taxonomy, $term->slug ),
				);
			}

			if ( empty( $options ) ) {
				continue;
			}

			$groups[] = array(
				'taxonomy' => $taxonomy,
				'type'     => $type,
				'label'    => 'color' === $type ? __( 'Color', 'solar-template' ) : __( 'Size', 'solar-template' ),
				'options'  => $options,
			);
		}

		return $groups;
	}

	/**
	 * Reports whether at least one in-stock variation exists for the given attribute value,
	 * regardless of any other dimension (a variation with a blank/"Any" value for this taxonomy
	 * counts as matching every option) — matching the design handoff's own static "this size is out
	 * of stock" treatment (independent of the selected color).
	 *
	 * @param array<int, array{attributes: array<string, string>, is_in_stock: bool}> $variations Payload from self::variations_payload().
	 * @param string $taxonomy Attribute taxonomy.
	 * @param string $slug     Term slug to check.
	 * @return bool
	 */
	private static function option_is_available( array $variations, string $taxonomy, string $slug ): bool {
		foreach ( $variations as $variation ) {
			$value = $variation['attributes'][ $taxonomy ] ?? '';

			if ( ( '' === $value || $value === $slug ) && $variation['is_in_stock'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns a plain, JSON-able list of the product's real WooCommerce variations, for
	 * assets/js/product.js to resolve a Color/Size selection against.
	 *
	 * @param \WC_Product_Variable $product Product to read variations from.
	 * @return array<int, array{variation_id: int, attributes: array<string, string>, is_in_stock: bool, display_price: float}>
	 */
	public static function variations_payload( \WC_Product_Variable $product ): array {
		$payload = array();

		foreach ( $product->get_available_variations() as $variation_data ) {
			$variation = wc_get_product( $variation_data['variation_id'] );

			if ( ! $variation ) {
				continue;
			}

			$attributes = array();

			foreach ( $variation_data['attributes'] as $key => $value ) {
				$attributes[ str_replace( 'attribute_', '', $key ) ] = $value;
			}

			$payload[] = array(
				'variation_id'  => (int) $variation_data['variation_id'],
				'attributes'    => $attributes,
				'is_in_stock'   => $variation->is_in_stock(),
				'display_price' => (float) $variation->get_price(),
			);
		}

		return $payload;
	}
}
