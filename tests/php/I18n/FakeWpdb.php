<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Test double standing in for WordPress' `$wpdb`, used only by I18n tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide a minimal in-memory implementation of the handful of `$wpdb` methods
 *          Solar_Template\I18n\DatabaseTranslator relies on, so that class can be unit tested
 *          without a real WordPress/MySQL install. Not a general-purpose SQL engine: it only
 *          understands the specific queries DatabaseTranslator issues.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\I18n;

/**
 * In-memory stand-in for `$wpdb`, scoped to what DatabaseTranslator needs.
 */
final class FakeWpdb {

	/**
	 * Table prefix, mirroring the real `$wpdb->prefix`.
	 *
	 * @var string
	 */
	public string $prefix = 'wp_';

	/**
	 * Rows of the fake `wp_solar_template_languages` table.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $languages = array();

	/**
	 * Rows of the fake `wp_solar_template_translations` table.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $translations = array();

	/**
	 * Mimics `$wpdb->prepare()`: substitutes `%s` (quoted) and `%d` (cast to int) placeholders.
	 *
	 * @param string $query SQL with placeholders.
	 * @param mixed  ...$args Values to substitute, in order.
	 * @return string The SQL with placeholders replaced.
	 */
	public function prepare( string $query, ...$args ): string {
		$i = 0;

		return preg_replace_callback(
			'/%[sd]/',
			static function ( array $matches ) use ( &$i, $args ): string {
				$value = $args[ $i++ ];

				return '%d' === $matches[0] ? (string) (int) $value : "'" . addslashes( (string) $value ) . "'";
			},
			$query
		);
	}

	/**
	 * Mimics `$wpdb->get_results()` for the two `SELECT` queries DatabaseTranslator issues.
	 *
	 * @param string $query  Already-prepared SQL.
	 * @param string $output Ignored: this fake always returns associative arrays.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_results( string $query, string $output = 'OBJECT' ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $output mirrors $wpdb's own signature; this fake always returns associative arrays.
		if ( str_contains( $query, 'solar_template_languages' ) ) {
			$rows = $this->languages;
			usort( $rows, static fn( $a, $b ) => $b['is_default'] <=> $a['is_default'] );

			return $rows;
		}

		if ( str_contains( $query, 'solar_template_translations' ) ) {
			if ( preg_match( "/language_code = '([^']*)'/", $query, $matches ) ) {
				return array_values(
					array_filter(
						$this->translations,
						static fn( $row ) => $row['language_code'] === $matches[1]
					)
				);
			}

			return $this->translations;
		}

		return array();
	}

	/**
	 * Mimics `$wpdb->query()`, only for the "reset default language" UPDATE statement.
	 *
	 * @param string $query Already-prepared SQL.
	 * @return int Number of affected rows.
	 */
	public function query( string $query ): int {
		if ( str_contains( $query, 'solar_template_languages' ) && str_contains( $query, 'UPDATE' ) ) {
			foreach ( $this->languages as &$row ) {
				$row['is_default'] = 0;
			}

			return count( $this->languages );
		}

		return 0;
	}

	/**
	 * Mimics `$wpdb->replace()`: upserts a row by its unique key (`code` or `string_key`+`language_code`+`context`).
	 *
	 * @param string $table  Table name (with prefix).
	 * @param array  $data   Column => value.
	 * @param array  $format Ignored.
	 * @return int 1 on success.
	 */
	public function replace( string $table, array $data, array $format = array() ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $format mirrors $wpdb's own signature.
		if ( str_contains( $table, 'solar_template_languages' ) ) {
			foreach ( $this->languages as $index => $row ) {
				if ( $row['code'] === $data['code'] ) {
					$this->languages[ $index ] = $data;

					return 1;
				}
			}

			$this->languages[] = $data;

			return 1;
		}

		if ( str_contains( $table, 'solar_template_translations' ) ) {
			foreach ( $this->translations as $index => $row ) {
				if ( $row['language_code'] === $data['language_code']
					&& $row['string_key'] === $data['string_key']
					&& $row['context'] === $data['context']
				) {
					$this->translations[ $index ] = $data;

					return 1;
				}
			}

			$this->translations[] = $data;

			return 1;
		}

		return 0;
	}

	/**
	 * Mimics `$wpdb->delete()`.
	 *
	 * @param string $table  Table name (with prefix).
	 * @param array  $where  Column => value to match.
	 * @param array  $format Ignored.
	 * @return int Number of rows removed.
	 */
	public function delete( string $table, array $where, array $format = array() ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $format mirrors $wpdb's own signature.
		$column = array_key_first( $where );
		$value  = $where[ $column ];

		if ( str_contains( $table, 'solar_template_languages' ) ) {
			$before          = count( $this->languages );
			$this->languages = array_values( array_filter( $this->languages, static fn( $row ) => $row[ $column ] !== $value ) );

			return $before - count( $this->languages );
		}

		if ( str_contains( $table, 'solar_template_translations' ) ) {
			$before             = count( $this->translations );
			$this->translations = array_values( array_filter( $this->translations, static fn( $row ) => $row[ $column ] !== $value ) );

			return $before - count( $this->translations );
		}

		return 0;
	}
}
