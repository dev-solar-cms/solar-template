<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Product page corner badges (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compute a product's real badges ("New"/"Solar Premium"/"On Sale") from real WooCommerce
 *          product data, rather than the design handoff's always-on placeholder badges. Single
 *          source of truth for both the product page panel and Catalog\ProductCardMapper (catalog/
 *          home/related-products cards) — a product previously could show different badges
 *          depending on which of the two independently computed them; both now read the same list,
 *          in the same order, and a product that matches more than one badge shows all of them
 *          together rather than picking a single "winner" (David's explicit product decision).
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
	 * Returns a product's real badges, in display priority order: "New" (published within the last
	 * self::new_badge_days() days), "Solar Premium" (marked "Featured" from the product edit
	 * screen), then "On Sale" (a real active sale price) — none shown unconditionally, unlike the
	 * design handoff's always-on mockup badges, and every one that applies is returned together
	 * (David's explicit product decision: badges coexist rather than one suppressing another).
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

		if ( $product->is_on_sale() ) {
			$badges[] = array(
				'type'  => 'sale',
				'label' => __( 'On Sale', 'solar-template' ),
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
	 * for a future "Products" administration tab.
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
