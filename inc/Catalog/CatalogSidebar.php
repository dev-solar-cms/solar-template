<?php
/**
 * Created: 2026-09-26 12:40 CEST
 * Role: Catalog sidebar widgets data (Solar_Template\Catalog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the data behind the catalog's optional sidebar column
 *          (template-parts/catalog-sidebar-widgets.php) — Category/Color/Brand/Rating as plain
 *          toggle links (reusing CatalogFilters::url()/remove_url(), same convention as the "Active
 *          filters" chip row) and Price as a handful of fixed brackets, rather than a second,
 *          independent `<form>` that would silently drop whichever filters are only present in the
 *          top bar's own form when submitted (two separate `<form method="get">`s can't both
 *          contribute to one URL).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the catalog sidebar's widget data.
 */
final class CatalogSidebar {

	/**
	 * Splits a price range into a fixed number of brackets, each with a real min/max the site's
	 * products actually span.
	 *
	 * Pure function — no WordPress dependency — so it's unit tested directly.
	 *
	 * @param float $min   Lowest price across the catalog.
	 * @param float $max   Highest price across the catalog.
	 * @param int   $count Number of brackets to split the range into.
	 * @return array<int, array{min: float|null, max: float|null}> Empty when $max <= $min (nothing to
	 *                                                                bracket). The first bracket's
	 *                                                                `min` and the last one's `max`
	 *                                                                are always null (open-ended).
	 */
	public static function price_brackets( float $min, float $max, int $count = 4 ): array {
		if ( $max <= $min || $count < 1 ) {
			return array();
		}

		$step      = ( $max - $min ) / $count;
		$brackets  = array();
		$threshold = $min;

		for ( $i = 0; $i < $count; $i++ ) {
			$next_threshold = ( $i === $count - 1 ) ? $max : $threshold + $step;

			$brackets[] = array(
				'min' => 0 === $i ? null : round( $threshold, 2 ),
				'max' => ( $count - 1 === $i ) ? null : round( $next_threshold, 2 ),
			);

			$threshold = $next_threshold;
		}

		return $brackets;
	}

	/**
	 * Returns which sidebar widgets are enabled, in display order, with a `has_options` flag so
	 * template-parts/catalog-sidebar-widgets.php can skip one with nothing real to filter by (same
	 * graceful-degradation convention as the top filter bar).
	 *
	 * @return array<int, array{key: string, has_options: bool}>
	 */
	public static function widgets(): array {
		$availability = array(
			'category' => ! empty( CatalogOptions::category_options() ),
			'price'    => CatalogOptions::price_bounds()['max'] > 0,
			'color'    => ! empty( CatalogOptions::attribute_options( CatalogOptions::color_attribute_slug() ) ),
			'brand'    => ! empty( CatalogOptions::attribute_options( CatalogOptions::brand_attribute_slug() ) ),
			'rating'   => true,
		);

		$widgets = array();

		foreach ( CatalogOptions::sidebar_widgets() as $key ) {
			$widgets[] = array(
				'key'         => $key,
				'has_options' => $availability[ $key ] ?? false,
			);
		}

		return $widgets;
	}
}
