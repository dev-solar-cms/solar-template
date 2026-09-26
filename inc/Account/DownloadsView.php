<?php
/**
 * Created: 2026-09-26 18:20 CEST
 * Role: My Account downloads view-model (Solar_Template\Account).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Map WooCommerce's own real downloadable-product rows (`WC()->customer-
 *          >get_downloadable_products()`) into the plain shape rendered by
 *          template-parts/account/download-row.php.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps WooCommerce's downloadable product rows to the theme's own download row view-model.
 */
final class DownloadsView {

	/**
	 * File extension → emoji icon. Falls back to a generic document icon for any other extension.
	 *
	 * @var array<string, string>
	 */
	private const ICONS = array(
		'pdf'  => '📄',
		'zip'  => '🗂',
		'jpg'  => '🖼',
		'jpeg' => '🖼',
		'png'  => '🖼',
		'gif'  => '🖼',
		'mp4'  => '🎬',
		'mp3'  => '🎵',
	);

	/**
	 * @param array<int, array> $downloads Rows as returned by `WC()->customer->get_downloadable_products()`.
	 * @return array<int, array{name: string, product_name: string, order_number: string, extension: string, icon: string, remaining_label: string, download_url: string}>
	 */
	public static function rows( array $downloads ): array {
		return array_map( array( self::class, 'map' ), $downloads );
	}

	/**
	 * @param array $download A single row as returned by `WC()->customer->get_downloadable_products()`.
	 * @return array{name: string, product_name: string, order_number: string, extension: string, icon: string, remaining_label: string, download_url: string}
	 */
	public static function map( array $download ): array {
		$extension = strtolower( (string) pathinfo( $download['file']['file'] ?? '', PATHINFO_EXTENSION ) );
		$order     = wc_get_order( $download['order_id'] );

		return array(
			'name'            => $download['download_name'],
			'product_name'    => $download['product_name'],
			'order_number'    => $order ? $order->get_order_number() : '',
			'extension'       => strtoupper( $extension ),
			'icon'            => self::ICONS[ $extension ] ?? '📄',
			'remaining_label' => is_numeric( $download['downloads_remaining'] )
				? sprintf(
					/* translators: %d: number of downloads left for this file. */
					_n( '%d download remaining', '%d downloads remaining', (int) $download['downloads_remaining'], 'solar-template' ),
					(int) $download['downloads_remaining']
				)
				: __( 'Unlimited', 'solar-template' ),
			'download_url'    => $download['download_url'],
		);
	}
}
