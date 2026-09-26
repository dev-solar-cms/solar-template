<?php
/**
 * Created: 2026-09-25 15:10 CEST
 * Role: Header/footer social links (Solar_Template\Header).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Return the social network links shown in the header top bar and reused by the footer.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Header;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social network links shown in the header top bar and footer.
 */
final class SocialLinks {

	/**
	 * Returns the social network links shown in the header top bar.
	 *
	 * Defaults to a placeholder `#` URL for each network so the icons match the design handoff
	 * out of the box; a future "Header" administration tab is expected to hook into this filter
	 * with the site owner's actual URLs (see Group 10 of the project roadmap).
	 *
	 * @return array<string, array{url: string, label: string, icon: string}>
	 */
	public static function links(): array {
		$defaults = array(
			'instagram' => array(
				'url'   => '#',
				'label' => __( 'Instagram', 'solar-template' ),
				'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
			),
			'facebook'  => array(
				'url'   => '#',
				'label' => __( 'Facebook', 'solar-template' ),
				'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
			),
			'pinterest' => array(
				'url'   => '#',
				'label' => __( 'Pinterest', 'solar-template' ),
				'icon'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M8 20c1.5-4 2-7 2-9a4 4 0 1 1 8 0c0 2-1 5-2 6"/><circle cx="12" cy="11" r="8"/></svg>',
			),
			'x'         => array(
				'url'   => '#',
				'label' => __( 'X (Twitter)', 'solar-template' ),
				'icon'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
			),
		);

		/**
		 * Filters the social network links shown in the header top bar.
		 *
		 * @param array $links Social network slug => { url, label, icon } map.
		 */
		return apply_filters( 'solar_template_social_links', $defaults );
	}
}
