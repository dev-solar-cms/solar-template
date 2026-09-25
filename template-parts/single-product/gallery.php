<?php
/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Product page gallery template-part (template-parts/single-product/gallery.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the main product image (4:5) plus a strip of up to 4 thumbnails; clicking a
 *          thumbnail swaps the main image (assets/js/product.js's initProductGallery()), no page
 *          reload. Deliberately does not build the mockup's "Enlarge" lightbox button — no
 *          behaviour is specified for it beyond the mockup itself, same reasoning as
 *          archive-product.php skipping its grid/list view toggle. Fed a plain, already-computed
 *          image list (single-product.php calls Solar_Template\Product\ProductGallery::images()),
 *          same convention as template-parts/product-card.php.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type array<int, array{id: int, full: string, alt: string}> $images Images to render, main
 *                                                                            image first.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gallery_args   = wp_parse_args( $args ?? array(), array( 'images' => array() ) );
$gallery_images = $gallery_args['images'];

if ( empty( $gallery_images ) ) {
	return;
}

$main_image = $gallery_images[0];
?>
<div class="product-gallery">
	<img
		class="product-gallery__main-image"
		src="<?php echo esc_url( $main_image['full'] ); ?>"
		alt="<?php echo esc_attr( $main_image['alt'] ); ?>"
	/>

	<?php if ( count( $gallery_images ) > 1 ) : ?>
		<div class="product-gallery__thumbnails">
			<?php foreach ( $gallery_images as $gallery_index => $gallery_image ) : ?>
				<button
					type="button"
					class="product-gallery__thumbnail<?php echo 0 === $gallery_index ? ' is-active' : ''; ?>"
					data-full="<?php echo esc_url( $gallery_image['full'] ); ?>"
					data-alt="<?php echo esc_attr( $gallery_image['alt'] ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: 1: image position, 2: total number of images. */ __( 'View image %1$d of %2$d', 'solar-template' ), $gallery_index + 1, count( $gallery_images ) ) ); ?>"
				>
					<img src="<?php echo esc_url( $gallery_image['full'] ); ?>" alt="" loading="lazy" />
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
