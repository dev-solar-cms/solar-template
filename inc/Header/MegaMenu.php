<?php
/**
 * Created: 2026-09-25 15:13 CEST
 * Role: "Collections" mega menu logic (Solar_Template\Header).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the mega menu's column content and whether it should render at all.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Header;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Collections" mega menu columns and enabled flag.
 */
final class MegaMenu {

	/**
	 * Returns the columns shown in the header's "Collections" mega menu.
	 *
	 * Defaults to generic placeholder columns rather than real WooCommerce product categories: a
	 * future step, or the Group 10 administration screen, is expected to hook into this filter with
	 * real taxonomy data.
	 *
	 * @return array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>
	 */
	public static function columns(): array {
		$shop_url = StoreLinks::shop_url();

		$columns = array(
			array(
				'heading' => __( 'Shop by category', 'solar-template' ),
				'links'   => array(
					array(
						'label' => __( 'New In', 'solar-template' ),
						'url'   => $shop_url,
					),
					array(
						'label' => __( 'Best Sellers', 'solar-template' ),
						'url'   => $shop_url,
					),
					array(
						'label' => __( 'Limited Edition', 'solar-template' ),
						'url'   => $shop_url,
					),
				),
			),
			array(
				'heading' => __( 'Featured', 'solar-template' ),
				'links'   => array(
					array(
						'label' => __( 'View All Collections', 'solar-template' ),
						'url'   => $shop_url,
					),
				),
			),
		);

		/**
		 * Filters the columns shown in the header's "Collections" mega menu.
		 *
		 * @param array $columns List of { heading, links: [{ label, url }] }, see
		 *                       self::columns()'s return type.
		 */
		return apply_filters( 'solar_template_mega_menu_columns', $columns );
	}

	/**
	 * Reports whether the "Collections" mega menu should be rendered at all.
	 *
	 * Defaults to enabled; a future "Header" administration tab (Group 10 of the project roadmap) is
	 * expected to hook into this filter with the site owner's actual preference.
	 *
	 * @return bool
	 */
	public static function enabled(): bool {
		/**
		 * Filters whether the header's "Collections" mega menu is enabled.
		 *
		 * @param bool $enabled True by default.
		 */
		return (bool) apply_filters( 'solar_template_header_mega_menu_enabled', true );
	}
}
