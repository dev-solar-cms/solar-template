<?php
/**
 * Created: 2026-09-26 17:00 CEST
 * Role: Wishlist storage (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Store each customer's saved products in `wp_solar_template_wishlist` (see
 *          Solar_Template\Database\Installer::create_wishlist_table()). Direct `$wpdb` usage
 *          rather than a Contracts interface, same convention as
 *          Solar_Template\Newsletter\SubscriberRepository: this wraps no third-party library, only
 *          WordPress' own database API.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores and looks up a customer's wishlisted products.
 */
final class WishlistRepository {

	/**
	 * @param object $wpdb WordPress' `$wpdb` global (untyped, same convention as
	 *                     Solar_Template\Newsletter\SubscriberRepository).
	 */
	public function __construct( private object $wpdb ) {}

	/**
	 * @param int $user_id    Customer's user ID.
	 * @param int $product_id Product ID to look up.
	 * @return bool
	 */
	public function is_wishlisted( int $user_id, int $product_id ): bool {
		$table = $this->table_name();
		$sql   = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND product_id = %d", $user_id, $product_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (bool) $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Adds or removes $product_id from $user_id's wishlist, whichever applies.
	 *
	 * @param int $user_id    Customer's user ID.
	 * @param int $product_id Product ID to toggle.
	 * @return bool The new state: true when the product is now wishlisted, false when it was just
	 *              removed.
	 */
	public function toggle( int $user_id, int $product_id ): bool {
		if ( $this->is_wishlisted( $user_id, $product_id ) ) {
			$this->remove( $user_id, $product_id );

			return false;
		}

		$this->add( $user_id, $product_id );

		return true;
	}

	/**
	 * @param int $user_id    Customer's user ID.
	 * @param int $product_id Product ID to add.
	 * @return void
	 */
	public function add( int $user_id, int $product_id ): void {
		$this->wpdb->insert(
			$this->table_name(),
			array(
				'user_id'    => $user_id,
				'product_id' => $product_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * @param int $user_id    Customer's user ID.
	 * @param int $product_id Product ID to remove.
	 * @return void
	 */
	public function remove( int $user_id, int $product_id ): void {
		$this->wpdb->delete(
			$this->table_name(),
			array(
				'user_id'    => $user_id,
				'product_id' => $product_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * @param int $user_id Customer's user ID.
	 * @return int Number of products this customer has wishlisted.
	 */
	public function count_for( int $user_id ): int {
		$table = $this->table_name();
		$sql   = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @param int $user_id Customer's user ID.
	 * @return int[] Product IDs this customer has wishlisted, newest first.
	 */
	public function product_ids_for( int $user_id ): array {
		$table = $this->table_name();
		$sql   = $this->wpdb->prepare( "SELECT product_id FROM {$table} WHERE user_id = %d ORDER BY id DESC", $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return array_map( 'intval', (array) $this->wpdb->get_col( $sql ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * @return string The wishlist table name, with the site's table prefix.
	 */
	private function table_name(): string {
		return $this->wpdb->prefix . 'solar_template_wishlist';
	}
}
