<?php
/**
 * Created: 2026-09-25 15:25 CEST
 * Role: wp-admin notices (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Warn the site administrator when WooCommerce is missing or inactive. The theme still
 *          activates and renders normally without WooCommerce (see Solar_Template\Theme::setup(),
 *          which only declares `woocommerce` theme support when the plugin is actually active);
 *          this only makes the situation visible in wp-admin instead of silently skipping
 *          storefront features. The "vendor/ missing" notice stays inline in functions.php, since
 *          nothing in `inc/` can be autoloaded before that check itself runs.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Solar_Template\Support\WooCommerceStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-admin notices shown by the theme.
 */
final class Notices {

	/**
	 * Warns the site administrator when WooCommerce is missing or inactive.
	 *
	 * @return void
	 */
	public static function woocommerce_missing(): void {
		if ( WooCommerceStatus::is_active() ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses(
				sprintf(
					/* translators: %s: link to the "Add plugins" screen. */
					__( 'Solar Template is designed for WooCommerce. Please <a href="%s">install and activate WooCommerce</a> to enable the storefront features.', 'solar-template' ),
					esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) )
				),
				array( 'a' => array( 'href' => true ) )
			)
		);
	}
}
