<?php
/**
 * Created: 2026-09-26 13:20 CEST
 * Role: Article "Share" buttons data (Solar_Template\Blog).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the article page's configurable share buttons (Facebook/X/Pinterest as plain,
 *          real share links — no JavaScript needed — and "Copy link" as the existing native Web
 *          Share API/clipboard button), which of them show configured from the administration
 *          "Blog" tab (Solar_Template\Admin\BlogSettings).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the article page's share button list.
 */
final class ArticleShare {

	private const NETWORKS = array( 'facebook', 'x', 'pinterest', 'copy_link' );

	/**
	 * Returns which share networks are enabled, in display order.
	 *
	 * @return array<int, string> Subset of `facebook`, `x`, `pinterest`, `copy_link`.
	 */
	public static function networks(): array {
		/**
		 * Filters the article page's enabled share networks.
		 *
		 * @param array<int, string> $networks Every network by default.
		 */
		$networks = (array) apply_filters( 'solar_template_blog_share_networks', self::NETWORKS );

		return array_values( array_intersect( self::NETWORKS, $networks ) );
	}

	/**
	 * Builds a real share URL for a given network. Pure function — no WordPress dependency — so it's
	 * unit tested directly.
	 *
	 * @param string $network `facebook`, `x`, or `pinterest` (`copy_link` has no URL of its own,
	 *                          handled instead by assets/js/blog.js's existing native button).
	 * @param string $title   Article title.
	 * @param string $url     Article's own permalink.
	 * @return string
	 */
	public static function share_url( string $network, string $title, string $url ): string {
		switch ( $network ) {
			case 'facebook':
				return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url );

			case 'x':
				return 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );

			case 'pinterest':
				return 'https://www.pinterest.com/pin/create/button/?url=' . rawurlencode( $url ) . '&description=' . rawurlencode( $title );

			default:
				return '';
		}
	}

	/**
	 * @param string $network One of self::NETWORKS.
	 * @return string Translated, accessible label for that network's button.
	 */
	public static function label( string $network ): string {
		$labels = array(
			'facebook'  => __( 'Share on Facebook', 'solar-template' ),
			'x'         => __( 'Share on X', 'solar-template' ),
			'pinterest' => __( 'Share on Pinterest', 'solar-template' ),
			'copy_link' => __( 'Share', 'solar-template' ),
		);

		return $labels[ $network ] ?? $network;
	}
}
