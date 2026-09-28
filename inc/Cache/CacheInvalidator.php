<?php
/**
 * Created: 2026-09-27 09:15 CEST
 * Role: Invalidation policy for the theme's own read-through cache (Solar_Template\Cache).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Flush the theme's cache (Solar_Template\Support\TransientCache, behind
 *          Solar_Template\Contracts\CacheInterface) whenever WordPress/WooCommerce content that a
 *          cached read method depends on changes — products, the terms of product categories or the
 *          catalog's configured Color/Size/Brand attribute taxonomies, and blog posts (read by
 *          Solar_Template\FrontPage\BlogPreview/Solar_Template\Blog\RelatedArticles) — so a cached
 *          page never keeps serving stale data past the next relevant save.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Cache;

use Solar_Template\Catalog\CatalogOptions;
use Solar_Template\Contracts\CacheInterface;
use Solar_Template\Support\TransientCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flushes the theme's cache when the product/category data it depends on changes.
 */
final class CacheInvalidator {

	/**
	 * Flushes the theme's cache when a permanently deleted post was a product or a blog post (a
	 * trashed one already goes through `save_post_product`/`save_post_post`, which self::flush() is
	 * also hooked on directly).
	 *
	 * @param int $post_id Deleted post ID.
	 * @return void
	 */
	public static function flush_if_deleted_relevant_post( int $post_id ): void {
		if ( self::is_relevant_post_type( (string) get_post_type( $post_id ) ) ) {
			self::flush();
		}
	}

	/**
	 * Reports whether a post type is read by one of the theme's cached queries.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool
	 */
	public static function is_relevant_post_type( string $post_type ): bool {
		return in_array( $post_type, array( 'product', 'post' ), true );
	}

	/**
	 * Flushes the theme's cache when a created/edited/deleted term belongs to a taxonomy one of the
	 * theme's cached queries reads from (product categories, or the catalog's configured Color/Size/
	 * Brand attribute taxonomies).
	 *
	 * @param int    $term_id          Term ID (unused — a relevant change always triggers a full
	 *                                 flush, see self::flush()'s own docblock for why).
	 * @param int    $term_taxonomy_id Term taxonomy ID (unused, same reason as $term_id).
	 * @param string $taxonomy         Taxonomy the changed term belongs to.
	 * @return void
	 */
	public static function flush_if_relevant_term( int $term_id, int $term_taxonomy_id, string $taxonomy ): void {
		if ( self::is_relevant_taxonomy( $taxonomy ) ) {
			self::flush();
		}
	}

	/**
	 * Reports whether a taxonomy's terms are read by one of the theme's cached queries.
	 *
	 * A pure predicate against the catalog's own (filterable) attribute slugs, kept independent of
	 * any specific naming convention: a store can rename its Color/Size/Brand attribute taxonomy
	 * away from the `pa_*` default, and this still tracks whichever taxonomy is actually configured.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return bool
	 */
	public static function is_relevant_taxonomy( string $taxonomy ): bool {
		return in_array(
			$taxonomy,
			array(
				'product_cat',
				CatalogOptions::color_attribute_slug(),
				CatalogOptions::size_attribute_slug(),
				CatalogOptions::brand_attribute_slug(),
			),
			true
		);
	}

	/**
	 * Purges every value stored in the theme's cache.
	 *
	 * A full flush rather than a per-key invalidation: the cache only ever stores a handful of
	 * cheap-to-recompute product/category ID listings (see
	 * Solar_Template\FrontPage\FeaturedProducts, Solar_Template\FrontPage\Categories,
	 * Solar_Template\Product\ProductRelated and Solar_Template\Catalog\CatalogOptions), so a blanket
	 * flush on any relevant content change is a simple, unambiguous invalidation policy rather than
	 * tracking per-product/per-category cache key dependencies for a negligible cost saving.
	 *
	 * @return void
	 */
	public static function flush(): void {
		self::cache()->flush();
	}

	/**
	 * @return CacheInterface
	 */
	private static function cache(): CacheInterface {
		return new TransientCache();
	}
}
