<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Database schema/installation helper for the theme (Solar_Template\Database).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define the `wp_solar_template_*` tables and seed their minimal default data. Only the
 *          i18n tables are handled at this stage; the remaining tables and the `after_switch_theme`
 *          activation hook are added once the settings they back exist (see later steps), which
 *          will call the methods defined here as part of a combined install() routine.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Database;

use Solar_Template\I18n\LanguageCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and seeds the theme's dedicated database tables.
 */
final class Installer {

	/**
	 * Creates (or updates, if the schema changed) the two i18n tables:
	 * `wp_solar_template_languages` and `wp_solar_template_translations`.
	 *
	 * Safe to call multiple times: relies on `dbDelta()`, which only applies the differences
	 * between the desired schema and what already exists.
	 *
	 * @return void
	 */
	public static function create_i18n_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		dbDelta( self::languages_table_sql( $wpdb->prefix, $charset_collate ) );
		dbDelta( self::translations_table_sql( $wpdb->prefix, $charset_collate ) );
	}

	/**
	 * Inserts the pre-configured languages (fr_FR, en_US) if they are not registered yet.
	 *
	 * @return void
	 */
	public static function seed_default_languages(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'solar_template_languages';

		foreach ( LanguageCatalog::defaults() as $code => $language ) {
			$already_exists = (bool) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE code = %s", $code ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);

			if ( $already_exists ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'code'       => $code,
					'label'      => $language['label'],
					'flag'       => $language['flag'],
					'is_active'  => 1,
					'is_default' => $language['is_default'] ? 1 : 0,
				),
				array( '%s', '%s', '%s', '%d', '%d' )
			);
		}
	}

	/**
	 * Builds the `CREATE TABLE` statement for `wp_solar_template_languages`.
	 *
	 * @param string $prefix          WordPress table prefix (`$wpdb->prefix`).
	 * @param string $charset_collate Charset/collation clause (`$wpdb->get_charset_collate()`).
	 * @return string SQL statement, formatted for `dbDelta()`.
	 */
	public static function languages_table_sql( string $prefix, string $charset_collate ): string {
		$table = $prefix . 'solar_template_languages';

		return "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(10) NOT NULL,
			label varchar(100) NOT NULL,
			flag varchar(10) NOT NULL DEFAULT '',
			is_active tinyint(1) NOT NULL DEFAULT 1,
			is_default tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code)
		) {$charset_collate};";
	}

	/**
	 * Builds the `CREATE TABLE` statement for `wp_solar_template_translations`.
	 *
	 * @param string $prefix          WordPress table prefix (`$wpdb->prefix`).
	 * @param string $charset_collate Charset/collation clause (`$wpdb->get_charset_collate()`).
	 * @return string SQL statement, formatted for `dbDelta()`.
	 */
	public static function translations_table_sql( string $prefix, string $charset_collate ): string {
		$table = $prefix . 'solar_template_translations';

		return "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			language_code varchar(10) NOT NULL,
			string_key varchar(191) NOT NULL,
			context varchar(191) NOT NULL DEFAULT '',
			singular text NOT NULL,
			plural text NULL,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY language_string_context (language_code,string_key,context)
		) {$charset_collate};";
	}
}
