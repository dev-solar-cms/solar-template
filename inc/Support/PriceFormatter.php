<?php
/**
 * Created: 2026-09-25 15:01 CEST
 * Role: Placeholder price formatting helper (Solar_Template\Support).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Format an editorial placeholder price the same way the design handoff's mockup data
 *          does ("89,00 €"). Shared by template-parts/product-card.php and
 *          template-parts/front-page/hero.php, which previously each defined their own identical
 *          local copy of this helper. Superseded by WooCommerce's own `wc_price()` wherever a card
 *          is wired to a real `WC_Product`.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats a placeholder price for editorial/fake-data display.
 */
final class PriceFormatter {

	/**
	 * @param float  $amount          Amount to format.
	 * @param string $currency_symbol Currency symbol appended after the amount.
	 * @return string Formatted, escaped price.
	 */
	public static function format( float $amount, string $currency_symbol ): string {
		return number_format( $amount, 2, ',', ' ' ) . '&nbsp;' . $currency_symbol;
	}
}
