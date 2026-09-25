<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Product page corner badges (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compute the product page panel's badges ("New"/"Solar Premium") from real WooCommerce
 *          product data, rather than the design handoff's always-on placeholder badges.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Computes a product's panel badges from its real WooCommerce data.
 */
final class ProductBadges {

	/**
	 * Returns the product panel's badges: "New" when the product was published within the last
	 * self::new_badge_days() days, "Solar Premium" when it is marked "Featured" from the product
	 * edit screen — neither shown unconditionally, unlike the design handoff's always-on mockup
	 * badges.
	 *
	 * @param \WC_Product $product Product to compute badges for.
	 * @return array<int, array{type: string, label: string}>
	 */
	public static function for_product( \WC_Product $product ): array {
		$badges = array();

		if ( self::is_new( $product ) ) {
			$badges[] = array(
				'type'  => 'new',
				'label' => __( 'New', 'solar-template' ),
			);
		}

		if ( $product->is_featured() ) {
			$badges[] = array(
				'type'  => 'premium',
				'label' => __( 'Solar Premium', 'solar-template' ),
			);
		}

		return $badges;
	}

	/**
	 * @param \WC_Product $product Product to check.
	 * @return bool True when the product was published within self::new_badge_days() days.
	 */
	private static function is_new( \WC_Product $product ): bool {
		$date_created = $product->get_date_created();

		if ( ! $date_created ) {
			return false;
		}

		$age_in_days = ( time() - $date_created->getTimestamp() ) / DAY_IN_SECONDS;

		return $age_in_days <= self::new_badge_days();
	}

	/**
	 * Returns the number of days after publication a product is still considered "New". Filterable
	 * for a future "Products" administration tab (Group 10 of the project roadmap).
	 *
	 * @return int
	 */
	public static function new_badge_days(): int {
		/**
		 * Filters the number of days after publication a product still shows the "New" badge.
		 *
		 * @param int $days 14 by default.
		 */
		return (int) apply_filters( 'solar_template_product_new_badge_days', 14 );
	}
}
