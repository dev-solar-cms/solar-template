<?php
/**
 * Created: 2026-09-25 14:42 CEST
 * Role: Catalog "load more" pagination state (Solar_Template\Catalog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compute the progressive-loading state (shown/total/percent/next page) from an
 *          already-executed catalog product query, for template-parts/catalog-load-more.php.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Computes the catalog's "load more" pagination state from an executed product query.
 */
final class CatalogPagination {

	/**
	 * Returns the catalog's "load more" state for the given, already-executed product query: how many
	 * products are shown so far, the real total, the percentage for the progress bar, and the next
	 * page's number/URL — or null when there is no further page (including when the query returned no
	 * results at all), so the calling template-part can render nothing rather than an empty/broken
	 * control.
	 *
	 * @param \WP_Query   $query    An already-executed catalog product query.
	 * @param string|null $base_url See CatalogFilters::url()'s $base_url parameter — the "next page"
	 *                                link is built against it the same way.
	 * @return array{shown: int, total: int, percent: float, next_page: int, next_page_url: string}|null
	 */
	public static function load_more_config( \WP_Query $query, ?string $base_url = null ): ?array {
		$total = (int) $query->found_posts;

		if ( $total <= 0 ) {
			return null;
		}

		$current_page = max( 1, (int) $query->get( 'paged' ) );

		if ( $current_page >= (int) $query->max_num_pages ) {
			return null;
		}

		$per_page  = (int) $query->get( 'posts_per_page' );
		$shown     = min( $total, $current_page * $per_page );
		$next_page = $current_page + 1;

		$next_page_url = null !== $base_url
			? add_query_arg( 'paged', $next_page, $base_url )
			: add_query_arg( 'paged', $next_page );

		return array(
			'shown'         => $shown,
			'total'         => $total,
			'percent'       => round( ( $shown / $total ) * 100, 1 ),
			'next_page'     => $next_page,
			'next_page_url' => $next_page_url,
		);
	}
}
