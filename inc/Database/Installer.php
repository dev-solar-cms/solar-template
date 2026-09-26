<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Database schema/installation helper for the theme (Solar_Template\Database).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define every `wp_solar_template_*` table and seed their minimal default data, and
 *          hook this installation into the theme's `after_switch_theme` activation event.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Database;

use Solar_Template\I18n\DefaultStrings;
use Solar_Template\I18n\GettextMoCompiler;
use Solar_Template\I18n\LanguageCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and seeds the theme's dedicated database tables.
 */
final class Installer {

	/**
	 * Runs every table creation/seed step, seeds the translation catalog with every string the
	 * theme's PHP code currently uses (see DefaultStrings), then compiles it into `.mo` files, so
	 * the whole pipeline — table, catalog, compiled file, WordPress' own gettext loading — is
	 * exercised from the very first activation.
	 *
	 * Hooked to `after_switch_theme` (see functions.php). Safe to call multiple times.
	 *
	 * @return void
	 */
	public static function install(): void {
		self::create_i18n_tables();
		self::seed_default_languages();
		self::create_settings_table();
		self::seed_default_settings();
		self::create_newsletter_table();
		self::create_wishlist_table();

		// Newly registered My Account endpoints ("wishlist", "sav" — see
		// Solar_Template\Account\AccountEndpoints) only resolve once WordPress' rewrite rules are
		// flushed; running this on every (re-)activation is the same mechanism already used in Docker
		// to pick up any newly added table above.
		flush_rewrite_rules();

		$translator = new \Solar_Template\I18n\DatabaseTranslator(
			$GLOBALS['wpdb'],
			new GettextMoCompiler(),
			get_template_directory() . '/languages'
		);
		DefaultStrings::seed( $translator );
		$translator->compile_all();
	}

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

	/**
	 * Creates (or updates) `wp_solar_template_settings`, a generic key/value store for the
	 * theme's configuration screens (future admin tabs fill it in; this step only needs a
	 * minimal, generically-useful set of defaults to exist from day one).
	 *
	 * @return void
	 */
	public static function create_settings_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::settings_table_sql( $wpdb->prefix, $wpdb->get_charset_collate() ) );
	}

	/**
	 * Inserts the minimal default settings (theme version, base colors, empty logo/favicon) if
	 * they are not registered yet.
	 *
	 * @return void
	 */
	public static function seed_default_settings(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'solar_template_settings';

		$defaults = array(
			'general.theme_version'   => wp_get_theme( get_template() )->get( 'Version' ),
			'general.primary_color'   => '#c9a227',
			'general.secondary_color' => '#0b0b0c',
			'general.logo_id'         => '0',
			'general.favicon_id'      => '0',
		);

		foreach ( $defaults as $key => $value ) {
			$already_exists = (bool) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE setting_key = %s", $key ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);

			if ( $already_exists ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'setting_key'   => $key,
					'setting_value' => (string) $value,
				),
				array( '%s', '%s' )
			);
		}
	}

	/**
	 * Builds the `CREATE TABLE` statement for `wp_solar_template_settings`.
	 *
	 * @param string $prefix          WordPress table prefix (`$wpdb->prefix`).
	 * @param string $charset_collate Charset/collation clause (`$wpdb->get_charset_collate()`).
	 * @return string SQL statement, formatted for `dbDelta()`.
	 */
	public static function settings_table_sql( string $prefix, string $charset_collate ): string {
		$table = $prefix . 'solar_template_settings';

		return "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(191) NOT NULL,
			setting_value longtext NOT NULL,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY setting_key (setting_key)
		) {$charset_collate};";
	}

	/**
	 * Creates (or updates) `wp_solar_template_newsletter_subscribers`, storing the front page
	 * newsletter section's sign-ups (see Solar_Template\Newsletter\SubscriberRepository).
	 *
	 * @return void
	 */
	public static function create_newsletter_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::newsletter_subscribers_table_sql( $wpdb->prefix, $wpdb->get_charset_collate() ) );
	}

	/**
	 * Builds the `CREATE TABLE` statement for `wp_solar_template_newsletter_subscribers`.
	 *
	 * @param string $prefix          WordPress table prefix (`$wpdb->prefix`).
	 * @param string $charset_collate Charset/collation clause (`$wpdb->get_charset_collate()`).
	 * @return string SQL statement, formatted for `dbDelta()`.
	 */
	public static function newsletter_subscribers_table_sql( string $prefix, string $charset_collate ): string {
		$table = $prefix . 'solar_template_newsletter_subscribers';

		return "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(191) NOT NULL,
			subscribed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) {$charset_collate};";
	}

	/**
	 * Creates (or updates) `wp_solar_template_wishlist`, storing each customer's saved products (see
	 * Solar_Template\Account\WishlistRepository).
	 *
	 * @return void
	 */
	public static function create_wishlist_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::wishlist_table_sql( $wpdb->prefix, $wpdb->get_charset_collate() ) );
	}

	/**
	 * Builds the `CREATE TABLE` statement for `wp_solar_template_wishlist`.
	 *
	 * @param string $prefix          WordPress table prefix (`$wpdb->prefix`).
	 * @param string $charset_collate Charset/collation clause (`$wpdb->get_charset_collate()`).
	 * @return string SQL statement, formatted for `dbDelta()`.
	 */
	public static function wishlist_table_sql( string $prefix, string $charset_collate ): string {
		$table = $prefix . 'solar_template_wishlist';

		return "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			product_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY user_product (user_id,product_id)
		) {$charset_collate};";
	}
}
