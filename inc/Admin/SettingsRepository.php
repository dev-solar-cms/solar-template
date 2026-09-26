<?php
/**
 * Created: 2026-09-26 09:00 CEST
 * Role: Theme settings storage (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Thin key/value wrapper around `wp_solar_template_settings` (already created and seeded
 *          by Solar_Template\Database\Installer since an earlier step of the project, anticipating
 *          this admin settings screen). Every settings tab reads/writes through this class, using
 *          `{tab_slug}.{field}` keys — direct `$wpdb` access, no Contracts interface, same
 *          convention as Solar_Template\Database\Installer/Solar_Template\Newsletter\SubscriberRepository:
 *          this wraps no third-party library, only WordPress' own database API.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes the theme's `{tab}.{field}` settings.
 */
final class SettingsRepository {

	/**
	 * Returns the stored value for $key, or $default_value when no row exists for it yet.
	 *
	 * @param string $key     Dotted setting key, e.g. `general.shop_name`.
	 * @param string $default_value Value returned when $key has never been saved.
	 * @return string
	 */
	public static function get( string $key, string $default_value = '' ): string {
		global $wpdb;

		$table = $wpdb->prefix . 'solar_template_settings';
		$value = $wpdb->get_var(
			$wpdb->prepare( "SELECT setting_value FROM {$table} WHERE setting_key = %s", $key ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return null !== $value ? (string) $value : $default_value;
	}

	/**
	 * Reads every key of $defaults (key => default value), returning the stored value for each one
	 * that has been saved and the given default otherwise.
	 *
	 * @param array<string, string> $defaults Dotted key => default value.
	 * @return array<string, string> Same keys as $defaults, stored or default values.
	 */
	public static function get_many( array $defaults ): array {
		$values = array();

		foreach ( $defaults as $key => $default ) {
			$values[ $key ] = self::get( $key, $default );
		}

		return $values;
	}

	/**
	 * Saves (inserting or updating) the value for $key.
	 *
	 * @param string $key   Dotted setting key, e.g. `general.shop_name`.
	 * @param string $value Value to store.
	 * @return void
	 */
	public static function set( string $key, string $value ): void {
		global $wpdb;

		$wpdb->replace(
			$wpdb->prefix . 'solar_template_settings',
			array(
				'setting_key'   => $key,
				'setting_value' => $value,
			),
			array( '%s', '%s' )
		);
	}

	/**
	 * Saves every entry of $values (unprefixed key => value) under the `{$prefix}.` namespace.
	 *
	 * @param string                $prefix Tab slug the keys belong to, e.g. `general`.
	 * @param array<string, string> $values Unprefixed field key => sanitized value.
	 * @return void
	 */
	public static function set_many( string $prefix, array $values ): void {
		foreach ( $values as $key => $value ) {
			self::set( "{$prefix}.{$key}", (string) $value );
		}
	}
}
