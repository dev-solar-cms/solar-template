<?php
/**
 * Created: 2026-09-27 09:40 CEST
 * Role: Unit test for Solar_Template\Cache\CacheInvalidator.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify the pure predicate deciding which taxonomies should invalidate the theme's cache,
 *          against the catalog's default (filterable) attribute slugs — see
 *          Solar_Template\Catalog\CatalogOptions.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Solar_Template\Cache\CacheInvalidator;

/**
 * @covers \Solar_Template\Cache\CacheInvalidator
 */
final class CacheInvalidatorTest extends TestCase {

	/**
	 * Product categories and the default Color/Size/Brand attribute taxonomies are relevant.
	 *
	 * @return void
	 */
	public function test_relevant_taxonomies_are_recognized(): void {
		$this->assertTrue( CacheInvalidator::is_relevant_taxonomy( 'product_cat' ) );
		$this->assertTrue( CacheInvalidator::is_relevant_taxonomy( 'pa_color' ) );
		$this->assertTrue( CacheInvalidator::is_relevant_taxonomy( 'pa_size' ) );
		$this->assertTrue( CacheInvalidator::is_relevant_taxonomy( 'pa_brand' ) );
	}

	/**
	 * An unrelated taxonomy (e.g. post tags/categories) is not relevant.
	 *
	 * @return void
	 */
	public function test_unrelated_taxonomies_are_not_recognized(): void {
		$this->assertFalse( CacheInvalidator::is_relevant_taxonomy( 'category' ) );
		$this->assertFalse( CacheInvalidator::is_relevant_taxonomy( 'post_tag' ) );
	}

	/**
	 * Products and blog posts are relevant (FrontPage\FeaturedProducts, FrontPage\BlogPreview,
	 * Blog\RelatedArticles, Product\ProductRelated, Catalog\CatalogOptions all cache real WP_Post/
	 * WC_Product-derived data).
	 *
	 * @return void
	 */
	public function test_relevant_post_types_are_recognized(): void {
		$this->assertTrue( CacheInvalidator::is_relevant_post_type( 'product' ) );
		$this->assertTrue( CacheInvalidator::is_relevant_post_type( 'post' ) );
	}

	/**
	 * An unrelated post type (e.g. a page, or WooCommerce's own order post type) is not relevant.
	 *
	 * @return void
	 */
	public function test_unrelated_post_types_are_not_recognized(): void {
		$this->assertFalse( CacheInvalidator::is_relevant_post_type( 'page' ) );
		$this->assertFalse( CacheInvalidator::is_relevant_post_type( 'shop_order' ) );
	}
}
