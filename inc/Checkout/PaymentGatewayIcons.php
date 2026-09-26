<?php
/**
 * Created: 2026-09-26 14:00 CEST
 * Role: Payment gateway icon lookup (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map a WooCommerce payment gateway id to one of the theme's own small inline icons for
 *          woocommerce/checkout/payment-method.php, so the styled radio card gets a relevant
 *          glyph for the gateways this project actually ships with (WooCommerce's own native
 *          "Direct bank transfer"/"Cheque payment"/"Cash on delivery" — DECISIONS.md §2 rules out
 *          any third-party payment plugin) while still degrading gracefully to a generic card icon
 *          for any other gateway a store owner installs later. The gateway id -> icon key mapping
 *          is filterable (a future Group 10 admin screen's extension point, same convention as
 *          Product\ColorSwatch's own name/slug map); the actual SVG markup per key stays a fixed,
 *          trusted set defined here, never built from filtered/user-controlled HTML.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves a payment gateway id to a small inline SVG icon.
 */
final class PaymentGatewayIcons {

	/**
	 * Returns the inline SVG icon markup for a given gateway id.
	 *
	 * @param string $gateway_id WooCommerce payment gateway id (e.g. `bacs`, `cheque`, `cod`).
	 * @return string
	 */
	public static function for_gateway( string $gateway_id ): string {
		$key = apply_filters( 'solar_template_checkout_gateway_icon_key', self::default_key_for( $gateway_id ), $gateway_id );

		return self::icon_for_key( is_string( $key ) ? $key : 'card' );
	}

	/**
	 * Returns the icon key this theme ships a default icon for, given a native WooCommerce
	 * gateway id.
	 *
	 * @param string $gateway_id WooCommerce payment gateway id.
	 * @return string
	 */
	private static function default_key_for( string $gateway_id ): string {
		$map = array(
			'bacs'   => 'bank',
			'cheque' => 'cheque',
			'cod'    => 'cash',
		);

		return $map[ $gateway_id ] ?? 'card';
	}

	/**
	 * Returns the fixed SVG markup for a known icon key, falling back to the generic card icon for
	 * an unrecognized one (e.g. a key a filter returned that this theme has no icon for).
	 *
	 * @param string $key Icon key.
	 * @return string
	 */
	private static function icon_for_key( string $key ): string {
		$icons = array(
			'bank'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V10"/><path d="M9 21V10"/><path d="M15 21V10"/><path d="M19 21V10"/><path d="M2 10l10-6 10 6"/></svg>',
			'cheque' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 15h4"/><path d="M14 15h4"/><path d="M6 10h12"/></svg>',
			'cash'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 10v.01"/><path d="M18 14v.01"/></svg>',
			'card'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>',
		);

		return $icons[ $key ] ?? $icons['card'];
	}
}
