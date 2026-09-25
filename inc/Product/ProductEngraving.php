<?php
/**
 * Created: 2026-09-25 17:10 CEST
 * Role: Custom engraving product data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Read a product's custom-engraving configuration (enabled, surcharge price, max text
 *          length) from its own native WooCommerce product meta — set from the admin product edit
 *          screen by Solar_Template\Product\EngravingAdminFields, no ACF/WooCommerce Product
 *          Add-ons dependency (DECISIONS.md §2).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads a product's custom-engraving configuration.
 */
final class ProductEngraving {

	public const META_ENABLED    = '_solar_template_engraving_enabled';
	public const META_PRICE      = '_solar_template_engraving_price';
	public const META_MAX_LENGTH = '_solar_template_engraving_max_length';

	public const DEFAULT_PRICE      = 25.0;
	public const DEFAULT_MAX_LENGTH = 20;

	/**
	 * @param \WC_Product $product Product to check.
	 * @return bool True when custom engraving is enabled for this product.
	 */
	public static function is_enabled( \WC_Product $product ): bool {
		return 'yes' === $product->get_meta( self::META_ENABLED, true );
	}

	/**
	 * @param \WC_Product $product Product to read the engraving surcharge from.
	 * @return float Surcharge added to the product's price when engraving is selected.
	 */
	public static function price( \WC_Product $product ): float {
		$price = $product->get_meta( self::META_PRICE, true );

		return '' !== $price ? (float) $price : self::DEFAULT_PRICE;
	}

	/**
	 * @param \WC_Product $product Product to read the max engraving text length from.
	 * @return int Maximum number of characters accepted, at least 1.
	 */
	public static function max_length( \WC_Product $product ): int {
		$max_length = $product->get_meta( self::META_MAX_LENGTH, true );

		return '' !== $max_length ? max( 1, (int) $max_length ) : self::DEFAULT_MAX_LENGTH;
	}

	/**
	 * @param \WC_Product $product Product to build the engraving config for.
	 * @return array{price: float, max_length: int}|null Null when engraving is not enabled for
	 *                                                      this product, so the caller can skip
	 *                                                      rendering the section entirely.
	 */
	public static function config_for_product( \WC_Product $product ): ?array {
		if ( ! self::is_enabled( $product ) ) {
			return null;
		}

		return array(
			'price'      => self::price( $product ),
			'max_length' => self::max_length( $product ),
		);
	}
}
