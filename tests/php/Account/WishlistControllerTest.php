<?php
/**
 * Created: 2026-09-28 13:45 CEST
 * Role: Unit test for Solar_Template\Account\WishlistController.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert render_wishlist_page() never queries is_wishlisted() — every product it lists is,
 *          by definition, already on this customer's wishlist. Only one test method: `repository()`
 *          memoizes its WishlistRepository in a `static` local, shared for the lifetime of the PHP
 *          process (matching SupportRequestController's/TranslationsSettings' own convention) —
 *          fine in production, but means a second test method in this same file would silently reuse
 *          the first test's fake `$wpdb` instead of its own.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\WishlistController;
use Solar_Template\Account\WishlistRepository;

final class WishlistControllerTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_render_wishlist_page_never_queries_is_wishlisted(): void {
		$fake_wpdb                                        = new FakeWpdb();
		$GLOBALS['wpdb']                                  = $fake_wpdb; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- test-only stand-in for the real `global $wpdb`.
		$GLOBALS['solar_template_test_logged_in_user_id'] = 5;

		$product = new \WC_Product( 42 );
		$product->solar_template_test_set_name( 'Solar Test Product' );
		$GLOBALS['solar_template_test_products'][42] = $product;

		( new WishlistRepository( $fake_wpdb ) )->add( 5, 42 );

		ob_start();
		WishlistController::render_wishlist_page();
		$output = ob_get_clean();

		$this->assertSame( 0, $fake_wpdb->get_var_calls, 'is_wishlisted() (get_var) must never run on the Wishlist page itself.' );
		$this->assertStringContainsString( 'Solar Test Product', $output );
	}
}
