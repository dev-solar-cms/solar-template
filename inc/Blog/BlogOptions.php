<?php
/**
 * Created: 2026-09-26 13:30 CEST
 * Role: Read-only blog descriptors (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Centralize the blog's filterable display options (grid column count), same convention as
 *          Solar_Template\Catalog\CatalogOptions::columns().
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only blog option/descriptor lookups.
 */
final class BlogOptions {

	/**
	 * Returns the number of columns the blog grid (template-parts/blog/grid.php) renders, configured
	 * from the administration "Blog" tab (Solar_Template\Admin\BlogSettings).
	 *
	 * @return int Column count, between 2 and 4 inclusive.
	 */
	public static function columns(): int {
		/**
		 * Filters the blog grid's column count.
		 *
		 * @param int $columns Column count, expected between 2 and 4.
		 */
		$columns = (int) apply_filters( 'solar_template_blog_columns', 3 );

		return max( 2, min( 4, $columns ) );
	}
}
