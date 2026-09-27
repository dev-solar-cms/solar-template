<?php
/**
 * Created: 2026-09-27 10:05 CEST
 * Role: Pure catalog-listing logic for the translation editor (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Turn the theme's known string list (Solar_Template\I18n\DefaultStrings — the single
 *          source of truth for which keys exist at all) and a locale's current translations
 *          (Solar_Template\I18n\Translator::get_strings()) into the paginated, searchable,
 *          placeholder-annotated rows Solar_Template\Admin\TranslationsEditorController renders and
 *          saves. No WordPress dependency, so every rule here (search matching, pagination,
 *          placeholder/plural detection) is unit tested directly.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Solar_Template\I18n\DefaultStrings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the translation editor's rows from the theme's known keys and a locale's current values.
 */
final class TranslationCatalog {

	/**
	 * Every key the theme's code currently calls a translation function on, in the order
	 * DefaultStrings::all() defines them.
	 *
	 * @return array<int, string>
	 */
	public static function all_keys(): array {
		return array_keys( DefaultStrings::all() );
	}

	/**
	 * Whether $key was originally registered with a plural form (i.e. the theme's code calls
	 * `_n()` on it, not just `__()`/`_e()`).
	 *
	 * @param string $key String key (the original/English text used as `msgid`).
	 * @return bool
	 */
	public static function has_plural( string $key ): bool {
		$entry = DefaultStrings::all()[ $key ] ?? null;

		return null !== $entry && isset( $entry['plural'] );
	}

	/**
	 * Every printf-style placeholder (`%s`, `%d`, `%1$s`, `%2$d`, ...) used in $key, in first-seen
	 * order, so the editor can show a translator explicitly which ones a translation must keep.
	 *
	 * @param string $key String key (the original/English text used as `msgid`).
	 * @return array<int, string>
	 */
	public static function placeholders( string $key ): array {
		preg_match_all( '/%(?:\d+\$)?[sd]/', $key, $matches );

		return array_values( array_unique( $matches[0] ) );
	}

	/**
	 * Builds one page of editable rows: filters $keys by $search (case-insensitive substring match
	 * against either the key itself or its current translation), then paginates the result.
	 *
	 * @param array<int, string>                                                     $keys            Every
	 *        known key (self::all_keys()).
	 * @param array<string, array{singular: string, plural: ?string, context: string}> $current_strings
	 *        The target locale's current translations (Translator::get_strings()).
	 * @param string                                                                  $search          Free-text
	 *        search, '' for none.
	 * @param int                                                                     $page            Requested
	 *        page number (1-indexed), clamped to the valid range.
	 * @param int                                                                     $per_page        Rows per page.
	 * @return array{rows: array<int, array{key: string, placeholders: array<int, string>, has_plural: bool, singular: string, plural: string}>, total: int, total_pages: int, page: int}
	 */
	public static function build_rows( array $keys, array $current_strings, string $search, int $page, int $per_page ): array {
		$needle = trim( mb_strtolower( $search ) );

		$filtered = array_values(
			array_filter(
				$keys,
				static function ( string $key ) use ( $needle, $current_strings ): bool {
					if ( '' === $needle ) {
						return true;
					}

					$current = $current_strings[ $key ]['singular'] ?? '';

					return false !== mb_stripos( $key, $needle ) || false !== mb_stripos( $current, $needle );
				}
			)
		);

		$total       = count( $filtered );
		$total_pages = max( 1, (int) ceil( $total / max( 1, $per_page ) ) );
		$page        = max( 1, min( $page, $total_pages ) );
		$slice       = array_slice( $filtered, ( $page - 1 ) * $per_page, $per_page );

		$rows = array();

		foreach ( $slice as $key ) {
			$rows[] = array(
				'key'          => $key,
				'placeholders' => self::placeholders( $key ),
				'has_plural'   => self::has_plural( $key ),
				'singular'     => $current_strings[ $key ]['singular'] ?? '',
				'plural'       => $current_strings[ $key ]['plural'] ?? '',
			);
		}

		return array(
			'rows'        => $rows,
			'total'       => $total,
			'total_pages' => $total_pages,
			'page'        => $page,
		);
	}
}
