<?php
/**
 * Created: 2026-09-25 16:45 CEST
 * Role: Color-variation swatch hex resolution (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Resolve the swatch color shown for a "Color" attribute term. WooCommerce stores no hex
 *          value for an attribute term on its own, so this reads an optional per-term meta value
 *          first (`_solar_template_color_hex`, ready for a future administration screen to set,
 *          not built now), then a filterable name/slug → hex map covering common color names, then
 *          a neutral fallback.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves a variation "Color" term to a hex swatch color.
 */
final class ColorSwatch {

	/**
	 * @param \WP_Term $term Attribute term to resolve a swatch color for.
	 * @return string Hex color, e.g. `#0D0D0D`.
	 */
	public static function hex_for_term( \WP_Term $term ): string {
		$meta_hex = get_term_meta( $term->term_id, '_solar_template_color_hex', true );

		if ( is_string( $meta_hex ) && '' !== $meta_hex ) {
			return $meta_hex;
		}

		$map = self::default_map();

		foreach ( array( sanitize_title( $term->name ), $term->slug ) as $key ) {
			if ( isset( $map[ $key ] ) ) {
				return $map[ $key ];
			}
		}

		/**
		 * Filters the fallback hex color used for a swatch term with no known mapping.
		 *
		 * @param string   $fallback Neutral gray by default.
		 * @param \WP_Term $term     The unresolved term.
		 */
		return (string) apply_filters( 'solar_template_color_swatch_fallback', '#d4d0ca', $term );
	}

	/**
	 * Returns the default slug/name → hex color map, covering common French and English color
	 * names, filterable so a store can extend it without a per-term meta value.
	 *
	 * @return array<string, string>
	 */
	private static function default_map(): array {
		$map = array(
			'noir'         => '#0d0d0d',
			'noir-profond' => '#0d0d0d',
			'black'        => '#0d0d0d',
			'blanc'        => '#f5f5f0',
			'blanc-neige'  => '#f5f5f0',
			'white'        => '#f5f5f0',
			'or'           => '#c9a96e',
			'or-brosse'    => '#c9a96e',
			'gold'         => '#c9a96e',
			'brushed-gold' => '#c9a96e',
			'argent'       => '#c7ccd1',
			'silver'       => '#c7ccd1',
			'rouge'        => '#b23a2e',
			'red'          => '#b23a2e',
			'bleu'         => '#2e4a6b',
			'blue'         => '#2e4a6b',
			'vert'         => '#3b5f45',
			'green'        => '#3b5f45',
			'beige'        => '#d8cbb0',
			'marron'       => '#5a4632',
			'brown'        => '#5a4632',
			'gris'         => '#8a8680',
			'gray'         => '#8a8680',
			'grey'         => '#8a8680',
		);

		/**
		 * Filters the default color-swatch name/slug → hex map.
		 *
		 * @param array<string, string> $map See this method's return type.
		 */
		return apply_filters( 'solar_template_color_swatch_map', $map );
	}
}
