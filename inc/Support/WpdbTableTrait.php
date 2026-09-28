<?php
/**
 * Created: 2026-09-28 16:10 CEST
 * Role: Shared `$wpdb` table helpers (Solar_Template\Support).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The prefixed-table-name lookup and the `prepare()` + `get_var()` "count" pattern were
 *          independently re-written in 5 classes (Account\WishlistRepository,
 *          Account\SupportRequestRepository, Newsletter\SubscriberRepository, I18n\DatabaseTranslator,
 *          Admin\SettingsRepository). A trait rather than an abstract base class, since one of those
 *          five (SettingsRepository) is a fully static, never-instantiated class while the other four
 *          take `$wpdb` via constructor injection — every method here takes `$wpdb` as an explicit
 *          parameter instead of reading it off `$this`, so the exact same methods work unmodified in
 *          both a static and an instance context, and each class's own public API stays unchanged.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared helpers for a class that reads/writes one of the theme's own `$wpdb` tables.
 */
trait WpdbTableTrait {

	/**
	 * @param object $wpdb  WordPress' `$wpdb` global (untyped, same convention as every class using
	 *                      this trait: no `wpdb` class exists outside a real WordPress install).
	 * @param string $table Unprefixed table name, e.g. `solar_template_wishlist`.
	 * @return string $table, with the site's table prefix.
	 */
	protected static function prefixed_table( object $wpdb, string $table ): string {
		return $wpdb->prefix . $table;
	}

	/**
	 * Runs a prepared `SELECT COUNT(*) FROM {$table} WHERE {$where}` and returns it as an int —
	 * the "how many rows match" pattern independently rewritten by
	 * Account\WishlistRepository::is_wishlisted()/count_for(),
	 * Account\SupportRequestRepository::active_count_for() and
	 * Newsletter\SubscriberRepository::is_subscribed() before this trait existed.
	 *
	 * @param object $wpdb   WordPress' `$wpdb` global.
	 * @param string $table  Prefixed table name (see self::prefixed_table()).
	 * @param string $where  WHERE clause with `%d`/`%s` placeholders, e.g. `user_id = %d`.
	 * @param mixed  ...$args Values to substitute into $where, in order.
	 * @return int
	 */
	protected static function count_where( object $wpdb, string $table, string $where, ...$args ): int {
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where}", ...$args ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $where's own %d/%s placeholders are supplied by the caller, not statically visible here.

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
