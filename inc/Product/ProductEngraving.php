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
	 * @return bool True when custom engraving is enabled for this product and not disabled
	 *              site-wide from the administration "Products" tab
	 *              (Solar_Template\Admin\ProductsSettings).
	 */
	public static function is_enabled( \WC_Product $product ): bool {
		if ( ! self::is_globally_enabled() ) {
			return false;
		}

		return 'yes' === $product->get_meta( self::META_ENABLED, true );
	}

	/**
	 * Whether the custom engraving feature is enabled site-wide, configured from the administration
	 * "Products" tab. A per-product toggle still governs whether any given product actually offers it
	 * (see self::is_enabled()).
	 *
	 * @return bool
	 */
	public static function is_globally_enabled(): bool {
		/**
		 * Filters whether the custom engraving feature is enabled site-wide.
		 *
		 * @param bool $enabled True by default.
		 */
		return (bool) apply_filters( 'solar_template_engraving_enabled', true );
	}

	/**
	 * @return string The section's label, shown above the toggle on the product page.
	 */
	public static function label(): string {
		/**
		 * Filters the custom engraving section's label.
		 *
		 * @param string $label 'Custom engraving' by default.
		 */
		return (string) apply_filters( 'solar_template_engraving_label', __( 'Custom engraving', 'solar-template' ) );
	}

	/**
	 * @return string `text` (single line) or `textarea` (multiple lines).
	 */
	public static function field_type(): string {
		/**
		 * Filters the custom engraving text field's type.
		 *
		 * @param string $field_type `text` by default.
		 */
		$field_type = (string) apply_filters( 'solar_template_engraving_field_type', 'text' );

		return 'textarea' === $field_type ? 'textarea' : 'text';
	}

	/**
	 * @param \WC_Product $product Product to read the engraving surcharge from.
	 * @return float Surcharge added to the product's price when engraving is selected.
	 */
	public static function price( \WC_Product $product ): float {
		$price = $product->get_meta( self::META_PRICE, true );

		if ( '' !== $price ) {
			return (float) $price;
		}

		/**
		 * Filters the custom engraving surcharge used for a product with no price of its own set.
		 *
		 * @param float $default_price self::DEFAULT_PRICE by default.
		 */
		return (float) apply_filters( 'solar_template_engraving_default_price', self::DEFAULT_PRICE );
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
