<?php
/**
 * Created: 2026-09-26 17:35 CEST
 * Role: Test double standing in for WordPress' `$wpdb`, used by
 *       Solar_Template\Account\WishlistRepository/SupportRequestRepository's tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide a minimal in-memory implementation of the handful of `$wpdb` methods
 *          WishlistRepository/SupportRequestRepository rely on, so those classes can be unit
 *          tested without a real WordPress/MySQL install. Not a general-purpose SQL engine: it
 *          only understands the specific queries they issue.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

/**
 * In-memory stand-in for `$wpdb`, scoped to what WishlistRepository needs.
 */
final class FakeWpdb {

	/**
	 * Table prefix, mirroring the real `$wpdb->prefix`.
	 *
	 * @var string
	 */
	public string $prefix = 'wp_';

	/**
	 * Wishlisted rows, each `array{user_id: int, product_id: int}`.
	 *
	 * @var array<int, array{user_id: int, product_id: int}>
	 */
	private array $rows = array();

	/**
	 * Support request rows, each a plain associative array of column => value.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $support_requests = array();

	/**
	 * Mimics `$wpdb->insert_id` after a support request insert.
	 *
	 * @var int
	 */
	public int $insert_id = 0;

	/**
	 * Mimics `$wpdb->prepare()`: substitutes `%d` placeholders.
	 *
	 * @param string $query SQL with placeholders.
	 * @param mixed  ...$args Values to substitute, in order.
	 * @return string The SQL with placeholders replaced.
	 */
	public function prepare( string $query, ...$args ): string {
		$i = 0;

		return preg_replace_callback(
			'/%[ds]/',
			static function ( array $matches ) use ( &$i, $args ): string {
				$value = $args[ $i++ ];

				return '%d' === $matches[0] ? (string) (int) $value : "'" . addslashes( (string) $value ) . "'";
			},
			$query
		);
	}

	/**
	 * Mimics `$wpdb->get_var()` for the "count" queries WishlistRepository issues.
	 *
	 * @param string $query Already-prepared SQL.
	 * @return int
	 */
	public function get_var( string $query ): int {
		if ( preg_match( '/user_id = (\d+) AND product_id = (\d+)/', $query, $matches ) ) {
			return $this->matches( (int) $matches[1], (int) $matches[2] ) ? 1 : 0;
		}

		if ( str_contains( $query, 'solar_template_support_requests' ) && preg_match( "/WHERE user_id = (\d+) AND status = '([^']*)'/", $query, $matches ) ) {
			return count(
				array_filter(
					$this->support_requests,
					fn( $row ) => $row['user_id'] === (int) $matches[1] && $row['status'] === $matches[2]
				)
			);
		}

		if ( preg_match( '/WHERE user_id = (\d+)$/', $query, $matches ) ) {
			return count( array_filter( $this->rows, fn( $row ) => $row['user_id'] === (int) $matches[1] ) );
		}

		return 0;
	}

	/**
	 * Mimics `$wpdb->get_results()` for `SupportRequestRepository::for_user()`.
	 *
	 * @param string $query Already-prepared SQL.
	 * @return object[]
	 */
	public function get_results( string $query ): array {
		if ( preg_match( '/WHERE user_id = (\d+)/', $query, $matches ) ) {
			$user_id = (int) $matches[1];

			return array_values(
				array_map(
					static fn( array $row ): object => (object) $row,
					array_reverse( array_filter( $this->support_requests, fn( $row ) => $row['user_id'] === $user_id ) )
				)
			);
		}

		return array();
	}

	/**
	 * Mimics `$wpdb->get_col()` for `product_ids_for()`.
	 *
	 * @param string $query Already-prepared SQL.
	 * @return int[]
	 */
	public function get_col( string $query ): array {
		if ( preg_match( '/WHERE user_id = (\d+)/', $query, $matches ) ) {
			$user_id = (int) $matches[1];

			return array_values(
				array_map(
					fn( $row ) => $row['product_id'],
					array_reverse( array_filter( $this->rows, fn( $row ) => $row['user_id'] === $user_id ) )
				)
			);
		}

		return array();
	}

	/**
	 * Mimics `$wpdb->insert()`.
	 *
	 * @param string $table  Table name (with prefix).
	 * @param array  $data   Column => value.
	 * @param array  $format Ignored.
	 * @return int 1 on success.
	 */
	public function insert( string $table, array $data, array $format = array() ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $format mirrors $wpdb's own signature.
		if ( str_contains( $table, 'solar_template_wishlist' ) ) {
			$this->rows[] = array(
				'user_id'    => (int) $data['user_id'],
				'product_id' => (int) $data['product_id'],
			);

			return 1;
		}

		if ( str_contains( $table, 'solar_template_support_requests' ) ) {
			$data['status']  = 'open';
			$data['user_id'] = (int) $data['user_id'];

			$this->support_requests[] = $data;
			$this->insert_id          = count( $this->support_requests );

			return 1;
		}

		return 0;
	}

	/**
	 * Mimics `$wpdb->delete()`.
	 *
	 * @param string $table  Table name (with prefix).
	 * @param array  $where  Column => value.
	 * @param array  $format Ignored.
	 * @return int Number of rows removed.
	 */
	public function delete( string $table, array $where, array $format = array() ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $format mirrors $wpdb's own signature.
		if ( ! str_contains( $table, 'solar_template_wishlist' ) ) {
			return 0;
		}

		$before     = count( $this->rows );
		$this->rows = array_values(
			array_filter(
				$this->rows,
				fn( $row ) => ! ( $row['user_id'] === (int) $where['user_id'] && $row['product_id'] === (int) $where['product_id'] )
			)
		);

		return $before - count( $this->rows );
	}

	/**
	 * @param int $user_id    User ID to look up.
	 * @param int $product_id Product ID to look up.
	 * @return bool
	 */
	private function matches( int $user_id, int $product_id ): bool {
		foreach ( $this->rows as $row ) {
			if ( $row['user_id'] === $user_id && $row['product_id'] === $product_id ) {
				return true;
			}
		}

		return false;
	}
}
