<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: WooCommerce detection helper (Solar_Template\Support).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Centralize the single check the rest of the theme uses to know whether WooCommerce is
 *          active, so every WooCommerce-dependent feature can degrade gracefully instead of
 *          fataling when it is missing or deactivated.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports whether the WooCommerce plugin is installed and active.
 */
final class WooCommerceStatus {

	/**
	 * Whether the `WooCommerce` class is loaded, i.e. the plugin is active.
	 *
	 * @return bool True when WooCommerce is active.
	 */
	public static function is_active(): bool {
		return class_exists( 'WooCommerce' );
	}
}
