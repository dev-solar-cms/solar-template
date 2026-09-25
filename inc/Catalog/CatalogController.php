<?php
/**
 * Created: 2026-09-25 14:42 CEST
 * Role: Catalog request handlers (Solar_Template\Catalog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The catalog's WordPress-hook "controllers" in the DECISIONS.md MVC sense — the
 *          `pre_get_posts` filter application, the AJAX filter/sort/pagination handler, and the
 *          filter bar's script enqueue — delegating all business logic to CatalogOptions/
 *          CatalogFilters/CatalogPagination and rendering the existing, unchanged
 *          template-parts/catalog-*.php views.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the catalog's filter/sort/pagination behavior into WordPress/WooCommerce.
 */
final class CatalogController {

	/**
	 * Merges the currently active catalog filters and sort selection into the shop/product-taxonomy
	 * page's main query, so a plain page load (no JavaScript, a shared/bookmarked filtered URL)
	 * already returns filtered, correctly-ordered results — the AJAX handler only has to re-render
	 * the same query for an in-page update.
	 *
	 * When a "Category" filter is active, it fully replaces the archive's own taxonomy scope (rather
	 * than narrowing it further): picking a category from the filter bar can move a visitor already
	 * on one category's archive to a different category entirely, exactly like the design handoff's
	 * quick category links would have (see archive-product.php).
	 *
	 * @param \WP_Query $query The query being modified.
	 * @return void
	 */
	public static function apply_filters_to_main_query( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
			return;
		}

		$filters = CatalogFilters::active();
		$args    = CatalogFilters::query_args( $filters );

		if ( isset( $args['tax_query'] ) ) {
			if ( ! empty( $filters['category'] ) ) {
				$query->set( 'product_cat', '' );
				$query->set( 'product_tag', '' );
			}

			// `get( 'tax_query', array() )` explicitly defaults to an array: `WP_Query::get()` itself
			// defaults to '' for an unset query var, and `(array) ''` produces `array( '' )` rather
			// than `array()`, which corrupts `parse_tax_query()`'s expected clause structure.
			$query->set( 'tax_query', array_merge( (array) $query->get( 'tax_query', array() ), $args['tax_query'] ) );
		}

		if ( isset( $args['meta_query'] ) ) {
			$query->set( 'meta_query', array_merge( (array) $query->get( 'meta_query', array() ), $args['meta_query'] ) );
		}

		$query->set( 'orderby', $args['orderby'] );
		$query->set( 'order', $args['order'] );
		$query->set( 'meta_key', $args['meta_key'] ?? '' );
		$query->set( 'posts_per_page', CatalogOptions::products_per_page() );
	}

	/**
	 * Handles the catalog filter bar's AJAX request (`solar_template_catalog_filter` action):
	 * sanitizes the submitted filters, runs them as the page's main product query (so WooCommerce's
	 * own visibility/stock/ordering logic — only ever applied to the main query — still applies,
	 * exactly as on a plain page load), and responds with the re-rendered results markup
	 * (template-parts/catalog-results.php) the front-end (assets/js/catalog.js) swaps into the page.
	 *
	 * Nonce verification happens in the calling wrapper (functions.php), before this method is ever
	 * invoked.
	 *
	 * @return void
	 */
	public static function handle_filter_request(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by the calling wrapper (functions.php) before this method is invoked.
		$filters   = CatalogFilters::sanitize( wp_unslash( $_POST ) );
		$paged     = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.
		$page_url  = isset( $_POST['pageUrl'] ) ? esc_url_raw( wp_unslash( $_POST['pageUrl'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.
		$base_url  = '' !== $page_url ? $page_url : null;
		$is_append = isset( $_POST['mode'] ) && 'append' === $_POST['mode']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.

		$args = array_merge(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'paged'               => $paged,
				'posts_per_page'      => CatalogOptions::products_per_page(),
				'ignore_sticky_posts' => true,
			),
			CatalogFilters::query_args( $filters )
		);

		global $wp_query, $wp_the_query;

		$previous_query      = $wp_query;
		$previous_main_query = $wp_the_query;

		$wp_the_query = new \WP_Query(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- temporarily swapped so WooCommerce's own main-query-only filtering (visibility/stock) applies, restored right below.
		$wp_query     = $wp_the_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_the_query->query( $args );

		if ( $is_append ) {
			// "Load more": only the requested page's own cards, plus a freshly computed "load more"
			// block (updated shown/total/next page) — appended to/replacing a slice of the existing
			// grid client-side, rather than re-rendering the whole results block from scratch.
			ob_start();
			get_template_part( 'template-parts/catalog-cards' );
			$cards_html = ob_get_clean();

			ob_start();
			get_template_part( 'template-parts/catalog-load-more', null, array( 'base_url' => $base_url ) );
			$load_more_html = ob_get_clean();

			$wp_query     = $previous_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_the_query = $previous_main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			wp_send_json_success(
				array(
					'cardsHtml'    => $cards_html,
					'loadMoreHtml' => $load_more_html,
				)
			);
		}

		ob_start();
		get_template_part( 'template-parts/catalog-results', null, array( 'base_url' => $base_url ) );
		$results_html = ob_get_clean();

		ob_start();
		get_template_part(
			'template-parts/catalog-active-filters',
			null,
			array(
				'filters'  => $filters,
				'base_url' => $base_url,
			)
		);
		$active_filters_html = ob_get_clean();

		$wp_query     = $previous_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_the_query = $previous_main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		wp_send_json_success(
			array(
				'html'              => $results_html,
				'activeFiltersHtml' => $active_filters_html,
			)
		);
	}

	/**
	 * Enqueues the catalog filter bar's own script and localizes the AJAX endpoint/nonce it needs, on
	 * the shop page and product category/tag archives only.
	 *
	 * @return void
	 */
	public static function enqueue_script(): void {
		if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
			return;
		}

		wp_localize_script(
			'solar-template',
			'solarTemplateCatalog',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'solar_template_catalog_filter' ),
			)
		);
	}
}
