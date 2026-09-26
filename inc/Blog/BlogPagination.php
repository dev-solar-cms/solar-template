<?php
/**
 * Created: 2026-09-26 21:10 CEST
 * Role: Blog archive pagination (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the blog archive's numbered pagination links from an already-executed post query,
 *          using WordPress' own native `paginate_links()` (restyled circles) rather than a custom
 *          implementation.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the blog archive's pagination link list.
 */
final class BlogPagination {

	/**
	 * @param \WP_Query $query An already-executed blog archive query.
	 * @return array<int, string> A list of ready-to-render HTML anchor/span strings (WordPress' own
	 *                             `page-numbers`/`current`/`dots`/`prev`/`next` classes), or an empty
	 *                             array when the archive fits on a single page.
	 */
	public static function links( \WP_Query $query ): array {
		if ( (int) $query->max_num_pages <= 1 ) {
			return array();
		}

		$current = max( 1, (int) get_query_var( 'paged' ) );

		$links = paginate_links(
			array(
				'base'      => str_replace( (string) PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
				'format'    => '?paged=%#%',
				'current'   => $current,
				'total'     => (int) $query->max_num_pages,
				'prev_text' => '←',
				'next_text' => '→',
				'type'      => 'array',
				'mid_size'  => 1,
				'end_size'  => 1,
			)
		);

		return is_array( $links ) ? $links : array();
	}
}
