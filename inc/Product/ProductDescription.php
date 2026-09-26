<?php
/**
 * Created: 2026-09-26 10:05 CEST
 * Role: Product page description tab data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Resolve the product page's "Description" tab content from the product's real post
 *          content (the same source WooCommerce's own description tab reads), plus a second,
 *          real gallery image to fill the tab's two-column layout — no editorial placeholder copy,
 *          same "real data only" convention as Catalog\ProductCardMapper.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the product page's "Description" tab content.
 */
final class ProductDescription {

	/**
	 * Returns the product's full description, filtered exactly like the `the_content()` template
	 * tag would (shortcodes, embeds, `wpautop`, etc.), or an empty string for a password-protected
	 * product.
	 *
	 * @param \WC_Product $product Product to read the description of.
	 * @return string Empty when the product has no description at all.
	 */
	public static function content( \WC_Product $product ): string {
		if ( post_password_required( $product->get_id() ) ) {
			return '';
		}

		/**
		 * Filters the product page's description tab content, same core filter `the_content()` runs.
		 *
		 * @param string $content Raw product description.
		 */
		return apply_filters( 'the_content', get_the_content( null, false, $product->get_id() ) );
	}

	/**
	 * Returns the gallery's second image (main image gallery, not the description's own), used to
	 * fill the description tab's second column, or null when the product has at most one image.
	 *
	 * @param array<int, array{id: int, full: string, alt: string}> $gallery_images See
	 *                                                                              Solar_Template\Product\ProductGallery::images().
	 * @return array{id: int, full: string, alt: string}|null
	 */
	public static function secondary_image( array $gallery_images ): ?array {
		return $gallery_images[1] ?? null;
	}
}
