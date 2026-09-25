<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Product page stock status label (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map a WC_Product's real stock status to the product panel's translated label + display
 *          modifier, independent of WooCommerce's own core-text-domain stock strings (the theme's
 *          translation catalog is the single source of truth for every visible string, see
 *          DECISIONS.md §9).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps a product's real stock status to a translated label + display modifier.
 */
final class ProductStock {

	/**
	 * @param \WC_Product $product Product to read stock status from.
	 * @return array{status: string, label: string, modifier: string} `status` is WooCommerce's own
	 *                                                                  raw value (`instock`/
	 *                                                                  `outofstock`/`onbackorder`);
	 *                                                                  `modifier` is a CSS-friendly
	 *                                                                  slug for styling.
	 */
	public static function for_product( \WC_Product $product ): array {
		$status = $product->get_stock_status();

		switch ( $status ) {
			case 'outofstock':
				return array(
					'status'   => $status,
					'label'    => __( 'Out of stock', 'solar-template' ),
					'modifier' => 'out-of-stock',
				);

			case 'onbackorder':
				return array(
					'status'   => $status,
					'label'    => __( 'Available on backorder', 'solar-template' ),
					'modifier' => 'backorder',
				);

			default:
				return array(
					'status'   => $status,
					'label'    => __( 'In stock', 'solar-template' ),
					'modifier' => 'in-stock',
				);
		}
	}
}
