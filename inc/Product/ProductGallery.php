<?php
/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Product page gallery image list (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Build the ordered list of images (featured image first, then the WooCommerce product
 *          gallery) that template-parts/single-product/gallery.php renders as the main image +
 *          thumbnail strip.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the product page gallery's image list from a WC_Product.
 */
final class ProductGallery {

	/**
	 * Returns the product's images, featured image first, followed by its WooCommerce gallery
	 * images (deduplicated), each sized for the main display and ready to render as a thumbnail.
	 *
	 * @param \WC_Product $product Product to read images from.
	 * @return array<int, array{id: int, full: string, alt: string}> Empty when the product has no
	 *                                                                  image at all.
	 */
	public static function images( \WC_Product $product ): array {
		$attachment_ids    = array();
		$featured_image_id = $product->get_image_id();

		if ( $featured_image_id ) {
			$attachment_ids[] = (int) $featured_image_id;
		}

		foreach ( $product->get_gallery_image_ids() as $gallery_image_id ) {
			$attachment_ids[] = (int) $gallery_image_id;
		}

		$attachment_ids = array_values( array_unique( $attachment_ids ) );

		return array_values(
			array_filter(
				array_map( array( self::class, 'map_attachment' ), $attachment_ids )
			)
		);
	}

	/**
	 * @param int $attachment_id Attachment ID.
	 * @return array{id: int, full: string, alt: string}|null Null when the attachment has no usable
	 *                                                          image URL (e.g. deleted attachment).
	 */
	private static function map_attachment( int $attachment_id ): ?array {
		$url = wp_get_attachment_image_url( $attachment_id, 'large' );

		if ( ! $url ) {
			return null;
		}

		return array(
			'id'   => $attachment_id,
			'full' => $url,
			'alt'  => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		);
	}
}
