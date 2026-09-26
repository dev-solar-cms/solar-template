<?php
/**
 * Created: 2026-09-26 11:25 CEST
 * Role: Product page related products section template-part
 *       (template-parts/single-product/related-products.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the "Related products" grid below the tabs, using real WooCommerce related
 *          products (see Solar_Template\Product\ProductRelated::products()) and the reusable
 *          product card template-part — same convention as
 *          template-parts/front-page/featured-products.php. The shared product card component
 *          already carries the Group 04 card-height alignment fix, so this grid only needs its own
 *          masonry offsets (same convention as .featured-products__grid) to stay visually aligned
 *          with real, variable-length product content. Renders nothing when the product has no
 *          related product at all.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type array $heading  See Solar_Template\Product\ProductRelated::heading().
 *     @type array $products See Solar_Template\Product\ProductRelated::products().
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Support\StoreLinks;

$related_products_args = wp_parse_args(
	$args ?? array(),
	array(
		'heading'  => array(
			'eyebrow' => '',
			'heading' => '',
		),
		'products' => array(),
	)
);

if ( empty( $related_products_args['products'] ) ) {
	return;
}
?>
<section class="related-products">
	<div class="related-products__inner">
		<div class="related-products__header">
			<div>
				<?php if ( '' !== $related_products_args['heading']['eyebrow'] ) : ?>
					<p class="related-products__eyebrow"><?php echo esc_html( $related_products_args['heading']['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h2 class="related-products__heading"><?php echo esc_html( $related_products_args['heading']['heading'] ); ?></h2>
			</div>

			<a class="related-products__view-all" href="<?php echo esc_url( StoreLinks::shop_url() ); ?>">
				<?php esc_html_e( 'View all →', 'solar-template' ); ?>
			</a>
		</div>

		<div class="related-products__grid">
			<?php foreach ( $related_products_args['products'] as $related_product_card_args ) : ?>
				<div class="related-products__item">
					<?php get_template_part( 'template-parts/product-card', null, $related_product_card_args ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
