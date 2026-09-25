<?php
/**
 * Created: 2026-09-25 10:00 CEST
 * Role: Front page featured products section template-part
 *       (template-parts/front-page/featured-products.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the "Featured products" masonry grid, using real WooCommerce products marked
 *          as "Featured" (see Solar_Template\FrontPage\FeaturedProducts::products()) and the
 *          reusable product card template-part. Renders nothing when there is no featured product
 *          to show (WooCommerce missing/inactive, or none marked as featured yet), rather than an
 *          empty grid.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\FrontPage\FeaturedProducts;
use Solar_Template\Support\StoreLinks;

$featured_products = FeaturedProducts::products();

if ( empty( $featured_products ) ) {
	return;
}

$section_heading = FeaturedProducts::heading();
?>
<section class="featured-products">
	<div class="featured-products__inner">
		<div class="featured-products__header">
			<div>
				<?php if ( '' !== $section_heading['eyebrow'] ) : ?>
					<p class="featured-products__eyebrow"><?php echo esc_html( $section_heading['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h2 class="featured-products__heading"><?php echo esc_html( $section_heading['heading'] ); ?></h2>
			</div>

			<?php if ( ! empty( $section_heading['view_all']['label'] ) ) : ?>
				<a class="featured-products__view-all" href="<?php echo esc_url( $section_heading['view_all']['url'] ); ?>">
					<?php echo esc_html( $section_heading['view_all']['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="featured-products__grid">
			<?php foreach ( $featured_products as $product_card_args ) : ?>
				<div class="featured-products__item">
					<?php get_template_part( 'template-parts/product-card', null, $product_card_args ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="featured-products__footer">
			<a class="btn btn--outline" href="<?php echo esc_url( StoreLinks::shop_url() ); ?>">
				<?php esc_html_e( 'View the full collection', 'solar-template' ); ?>
			</a>
		</div>
	</div>
</section>
