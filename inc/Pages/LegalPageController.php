<?php
/**
 * Created: 2026-09-26 21:00 CEST
 * Role: Legal page template registration + content helpers (Solar_Template\Pages).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Register the selectable "Legal Page" page template (Page Attributes dropdown in the
 *          block editor), build the tab strip linking the site's own C.G.V./privacy/legal notice/
 *          cookies pages, and auto-generate a table of contents from the current page's own <h2>
 *          headings — the heading-parsing logic stays a pure string transform (no WordPress
 *          dependency) so it can be unit tested directly.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Pages;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Legal Page" template registration and tab/table-of-contents helpers.
 */
final class LegalPageController {

	public const TEMPLATE = 'page-templates/legal-page.php';

	/**
	 * Adds the theme's "Legal Page" template to the Page Attributes template dropdown.
	 *
	 * @param array<string, string> $templates Existing selectable templates, file path => label.
	 * @return array<string, string>
	 */
	public static function register_template( array $templates ): array {
		$templates[ self::TEMPLATE ] = __( 'Legal Page', 'solar-template' );

		return $templates;
	}

	/**
	 * Returns the fixed list of legal pages the tab strip links to (slug => translated label), in
	 * display order.
	 *
	 * @return array<string, string>
	 */
	public static function tab_definitions(): array {
		return array(
			'terms-and-conditions' => __( 'Terms & Conditions', 'solar-template' ),
			'privacy-policy'       => __( 'Privacy Policy', 'solar-template' ),
			'legal-notice'         => __( 'Legal Notice', 'solar-template' ),
			'cookies'              => __( 'Cookies', 'solar-template' ),
		);
	}

	/**
	 * Resolves the tab strip for the given current page slug: each tab's real permalink (falling
	 * back to the site's front page when that legal page doesn't exist yet — see
	 * Solar_Template\Support\StoreLinks::page_url_by_slug()) and whether it matches $current_slug.
	 *
	 * @param string $current_slug Slug of the page currently being displayed.
	 * @return array<int, array{slug: string, label: string, url: string, active: bool}>
	 */
	public static function tabs( string $current_slug ): array {
		$tabs = array();

		foreach ( self::tab_definitions() as $slug => $label ) {
			$tabs[] = array(
				'slug'   => $slug,
				'label'  => $label,
				'url'    => StoreLinks::page_url_by_slug( $slug ),
				'active' => $slug === $current_slug,
			);
		}

		return $tabs;
	}

	/**
	 * Adds a stable `id` attribute to every <h2> heading in $content that doesn't already have one
	 * (an author-set id is always kept as-is), and returns both the resulting HTML and the ordered
	 * list of headings this produces the table of contents from.
	 *
	 * Deliberately a pure string transform with no WordPress function calls, so it can be unit
	 * tested without a WordPress install (see tests/php/Pages/LegalPageControllerTest.php).
	 *
	 * @param string $content Rendered post content (post_content run through the_content filters).
	 * @return array{content: string, headings: array<int, array{id: string, label: string}>}
	 */
	public static function annotate_headings( string $content ): array {
		$headings = array();
		$seen_ids = array();

		$content = (string) preg_replace_callback(
			'/<h2\b([^>]*)>(.*?)<\/h2>/is',
			static function ( array $matches ) use ( &$headings, &$seen_ids ): string {
				$attributes = $matches[1];
				$inner_html = $matches[2];
				$label      = trim( preg_replace( '/\s+/', ' ', html_entity_decode( strip_tags( $inner_html ), ENT_QUOTES ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- deliberately plain strip_tags(), not wp_strip_all_tags(): this method stays a pure, WordPress-independent string transform so it can be unit tested without a WordPress install (see tests/php/Pages/LegalPageControllerTest.php).

				if ( '' === $label ) {
					return $matches[0];
				}

				if ( preg_match( '/\bid=["\']([^"\']+)["\']/i', $attributes, $id_match ) ) {
					$id = $id_match[1];
				} else {
					$id         = self::unique_slug( $label, $seen_ids );
					$attributes = $attributes . ' id="' . $id . '"';
				}

				$seen_ids[ $id ] = true;
				$headings[]      = array(
					'id'    => $id,
					'label' => $label,
				);

				return "<h2{$attributes}>{$inner_html}</h2>";
			},
			$content
		);

		return array(
			'content'  => $content,
			'headings' => $headings,
		);
	}

	/**
	 * Turns $label into a URL-safe slug, appending -2/-3/... when it collides with one already in
	 * $seen_ids (two headings sharing the exact same text still get distinct anchors).
	 *
	 * @param string              $label    Heading text.
	 * @param array<string, bool> $seen_ids Slugs already used so far, keyed by slug.
	 * @return string
	 */
	private static function unique_slug( string $label, array $seen_ids ): string {
		$base = strtolower( trim( (string) preg_replace( '/[^a-z0-9]+/i', '-', self::to_ascii( $label ) ), '-' ) );

		if ( '' === $base ) {
			$base = 'section';
		}

		$slug   = $base;
		$suffix = 2;

		while ( isset( $seen_ids[ $slug ] ) ) {
			$slug = "{$base}-{$suffix}";
			++$suffix;
		}

		return $slug;
	}

	/**
	 * Transliterates common accented Latin characters to their plain ASCII equivalent.
	 *
	 * Deliberately a fixed character map rather than `iconv(...,'ASCII//TRANSLIT',...)`: that
	 * transliteration mode's exact output depends on the underlying C library (glibc vs. macOS'
	 * libiconv), which is not something this theme controls or wants heading anchors to vary with.
	 *
	 * @param string $text Text to transliterate.
	 * @return string
	 */
	private static function to_ascii( string $text ): string {
		static $map = array(
			'à' => 'a',
			'â' => 'a',
			'ä' => 'a',
			'á' => 'a',
			'ã' => 'a',
			'å' => 'a',
			'ç' => 'c',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'í' => 'i',
			'ñ' => 'n',
			'ò' => 'o',
			'ô' => 'o',
			'ö' => 'o',
			'ó' => 'o',
			'õ' => 'o',
			'ù' => 'u',
			'û' => 'u',
			'ü' => 'u',
			'ú' => 'u',
			'ý' => 'y',
			'ÿ' => 'y',
			'œ' => 'oe',
			'æ' => 'ae',
			'ß' => 'ss',
		);

		return strtr( mb_strtolower( $text ), $map );
	}
}
