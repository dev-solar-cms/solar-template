<?php
/**
 * Created: 2026-09-25 15:20 CEST
 * Role: Footer content (Solar_Template\Footer).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the footer's column configuration, payment method badges, and copyright line.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Footer;

use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer column configuration, payment icons, and copyright line.
 */
final class Footer {

	/**
	 * Returns the footer's column configuration (brand blurb, Shop/Information/Legal link columns,
	 * newsletter copy).
	 *
	 * Link columns point at the closest existing equivalent already available (shop page, posts
	 * page, a page matching a legal-page slug if one exists) rather than a dedicated view, same
	 * reason as \Solar_Template\Header\Nav::default_items() — real legal pages are built by a later
	 * step of the project roadmap.
	 *
	 * @return array{
	 *     brand: array{name: string, description: string},
	 *     columns: array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>,
	 *     newsletter: array{heading: string, description: string},
	 * }
	 */
	public static function config(): array {
		$shop_url     = StoreLinks::shop_url();
		$blog_page_id = (int) get_option( 'page_for_posts' );
		$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

		$config = array(
			'brand'      => array(
				'name'        => get_bloginfo( 'name' ),
				'description' => __( 'A modern, configurable WooCommerce theme to showcase your products.', 'solar-template' ),
			),
			'columns'    => array(
				array(
					'heading' => __( 'Shop', 'solar-template' ),
					'links'   => array(
						array(
							'label' => __( 'All Products', 'solar-template' ),
							'url'   => $shop_url,
						),
						array(
							'label' => __( 'New In', 'solar-template' ),
							'url'   => $shop_url,
						),
						array(
							'label' => __( 'Sale', 'solar-template' ),
							'url'   => $shop_url,
						),
						array(
							'label' => __( 'Collections', 'solar-template' ),
							'url'   => $shop_url,
						),
					),
				),
				array(
					'heading' => __( 'Information', 'solar-template' ),
					'links'   => array(
						array(
							'label' => __( 'About', 'solar-template' ),
							'url'   => StoreLinks::page_url_by_slug( 'about' ),
						),
						array(
							'label' => __( 'Blog', 'solar-template' ),
							'url'   => $blog_url,
						),
						array(
							'label' => __( 'Contact', 'solar-template' ),
							'url'   => StoreLinks::page_url_by_slug( 'contact' ),
						),
					),
				),
				array(
					'heading' => __( 'Legal', 'solar-template' ),
					'links'   => array(
						array(
							'label' => __( 'Terms & Conditions', 'solar-template' ),
							'url'   => StoreLinks::page_url_by_slug( 'terms-and-conditions' ),
						),
						array(
							'label' => __( 'Privacy Policy', 'solar-template' ),
							'url'   => StoreLinks::page_url_by_slug( 'privacy-policy' ),
						),
						array(
							'label' => __( 'Legal Notice', 'solar-template' ),
							'url'   => StoreLinks::page_url_by_slug( 'legal-notice' ),
						),
						array(
							'label' => __( 'Cookies', 'solar-template' ),
							'url'   => StoreLinks::page_url_by_slug( 'cookies' ),
						),
					),
				),
			),
			'newsletter' => array(
				'heading'     => __( 'Newsletter', 'solar-template' ),
				'description' => __( 'Get exclusive offers first.', 'solar-template' ),
			),
		);

		/**
		 * Filters the footer's column configuration.
		 *
		 * @param array $config See self::config()'s return type.
		 */
		return apply_filters( 'solar_template_footer_config', $config );
	}

	/**
	 * Returns the payment method badges shown in the footer's bottom bar.
	 *
	 * @return string[] Payment method labels, in display order.
	 */
	public static function payment_icons(): array {
		$icons = array( 'VISA', 'MC', 'PAYPAL', 'STRIPE', 'APPLE PAY' );

		/**
		 * Filters the payment method badges shown in the footer.
		 *
		 * @param string[] $icons Payment method labels, in display order.
		 */
		return apply_filters( 'solar_template_footer_payment_icons', $icons );
	}

	/**
	 * Returns the footer's copyright line, with the `{year}` placeholder replaced by the current
	 * year.
	 *
	 * @return string
	 */
	public static function copyright(): string {
		$default = sprintf(
			/* translators: %s: site name. */
			__( '© {year} %s — WordPress WooCommerce theme · All rights reserved', 'solar-template' ),
			get_bloginfo( 'name' )
		);

		/**
		 * Filters the footer's copyright line, before the `{year}` placeholder is replaced.
		 *
		 * @param string $template Copyright line, with a literal `{year}` placeholder.
		 */
		$template = apply_filters( 'solar_template_footer_copyright', $default );

		return str_replace( '{year}', gmdate( 'Y' ), $template );
	}
}
