<?php
/**
 * Created: 2026-09-25 15:40 CEST
 * Role: Front page hero section content (Solar_Template\FrontPage).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the front page hero section's content (eyebrow label, three-line heading,
 *          subtitle, both CTAs, trust badges, and the floating "highlight" card shown over the
 *          media block).
 *
 * @package Solar_Template
 */

namespace Solar_Template\FrontPage;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front page hero section content.
 */
final class Hero {

	/**
	 * Returns the front page hero section's content.
	 *
	 * All editorial content, exposed via a single filterable array rather than wired to a real
	 * WooCommerce product; a future "Home page" administration tab is expected to hook into this
	 * filter with the site owner's actual copy/image.
	 *
	 * @return array{
	 *     eyebrow: string,
	 *     heading_lines: string[],
	 *     subtitle: string,
	 *     primary_cta: array{label: string, url: string},
	 *     secondary_cta: array{label: string, url: string},
	 *     trust_badges: array<int, array{icon: string, label: string}>,
	 *     image_url: string|null,
	 *     image_alt: string,
	 *     highlight: array{
	 *         eyebrow: string,
	 *         title: string,
	 *         price: float|null,
	 *         regular_price: float|null,
	 *         currency_symbol: string,
	 *         progress_percent: int,
	 *         note: string,
	 *     },
	 * }
	 */
	public static function config(): array {
		$shop_url = StoreLinks::shop_url();

		$defaults = array(
			'eyebrow'       => __( 'Spring · Summer 2025 Collection', 'solar-template' ),
			// These three lines form a single sentence, split for the display treatment of the
			// mockup (each on its own line, the middle one in accent gold italics) — kept as separate
			// strings so each line can wrap/translate independently rather than a single string with
			// hardcoded line breaks.
			'heading_lines' => array(
				__( 'The essentials,', 'solar-template' ),
				__( 'reimagined', 'solar-template' ),
				__( 'for you.', 'solar-template' ),
			),
			'subtitle'      => __( 'Discover our premium selection of products, carefully chosen to combine quality, design and durability.', 'solar-template' ),
			'primary_cta'   => array(
				'label' => __( 'Explore the shop', 'solar-template' ),
				'url'   => $shop_url,
			),
			'secondary_cta' => array(
				'label' => __( 'See what’s new →', 'solar-template' ),
				'url'   => $shop_url,
			),
			'trust_badges'  => array(
				array(
					'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>',
					'label' => __( 'Free shipping from €60', 'solar-template' ),
				),
				array(
					'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>',
					'label' => __( 'Returns within 30 days', 'solar-template' ),
				),
				array(
					'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
					'label' => __( 'Secure payment', 'solar-template' ),
				),
			),
			'image_url'     => null,
			'image_alt'     => '',
			'highlight'     => array(
				'eyebrow'          => __( 'Favorite pick', 'solar-template' ),
				'title'            => __( 'Summer 2025 Collection', 'solar-template' ),
				'price'            => 129.0,
				'regular_price'    => 179.0,
				'currency_symbol'  => '€',
				'progress_percent' => 62,
				'note'             => __( '38% already claimed · Limited stock', 'solar-template' ),
			),
		);

		/**
		 * Filters the front page hero section's content.
		 *
		 * @param array $config See self::config()'s return type.
		 */
		return apply_filters( 'solar_template_hero_config', $defaults );
	}
}
