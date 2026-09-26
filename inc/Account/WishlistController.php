<?php
/**
 * Created: 2026-09-26 17:05 CEST
 * Role: Wishlist request handler (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The wishlist's WordPress-hook "controller" in the DECISIONS.md MVC sense — the AJAX
 *          toggle handler, the "wishlist" My Account endpoint's content, and the product card
 *          script's localized data — delegating storage to WishlistRepository and rendering to
 *          template-parts/account/wishlist.php, mirroring Solar_Template\Newsletter\NewsletterController.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

use Solar_Template\Catalog\ProductCardMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the wishlist's AJAX toggle and My Account page into WordPress.
 */
final class WishlistController {

	/**
	 * Handles the product card wishlist button's AJAX toggle (`solar_template_wishlist_toggle`
	 * action). A logged-out visitor gets a redirect URL to the login page instead of an error, so the
	 * front-end can send them there rather than fail silently.
	 *
	 * @return void
	 */
	public static function handle_toggle(): void {
		check_ajax_referer( 'solar_template_wishlist', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'redirect' => wc_get_page_permalink( 'myaccount' ) ) );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.

		if ( ! $product_id || ! wc_get_product( $product_id ) ) {
			wp_send_json_error( array( 'message' => __( 'This product could not be found.', 'solar-template' ) ) );
		}

		$wishlisted = self::repository()->toggle( get_current_user_id(), $product_id );

		wp_send_json_success( array( 'wishlisted' => $wishlisted ) );
	}

	/**
	 * Renders the "wishlist" My Account endpoint's content: a grid of the customer's saved products
	 * (reusing the existing product card component), or an empty-state message.
	 *
	 * @return void
	 */
	public static function render_wishlist_page(): void {
		$user_id     = get_current_user_id();
		$product_ids = self::repository()->product_ids_for( $user_id );

		$products = array();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product && $product->is_visible() ) {
				$products[] = ProductCardMapper::map( $product );
			}
		}

		get_template_part(
			'template-parts/account/wishlist',
			null,
			array( 'products' => $products )
		);
	}

	/**
	 * Enqueues the wishlist toggle button's localized AJAX endpoint/nonce, on top of the theme's
	 * compiled main script.
	 *
	 * @return void
	 */
	public static function enqueue_script(): void {
		wp_localize_script(
			'solar-template',
			'solarTemplateWishlist',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'solar_template_wishlist' ),
				'isLoggedIn' => is_user_logged_in(),
				'loginUrl'   => wc_get_page_permalink( 'myaccount' ),
			)
		);
	}

	/**
	 * Builds the theme's wishlist repository, wired to the real WordPress database.
	 *
	 * @return WishlistRepository
	 */
	private static function repository(): WishlistRepository {
		global $wpdb;

		static $repository = null;

		if ( null === $repository ) {
			$repository = new WishlistRepository( $wpdb );
		}

		return $repository;
	}
}
