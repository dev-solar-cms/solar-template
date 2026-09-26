<?php
/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Product page template (single-product.php), loaded by WooCommerce for every product's own
 *       page.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product page: breadcrumb, then a two-column layout with the image gallery
 *          (template-parts/single-product/gallery.php) on the left and the sticky product panel
 *          (template-parts/single-product/panel.php) on the right, followed by the Description/
 *          Reviews/Specifications tabs (template-parts/single-product/tabs.php). Grows section by
 *          section, same convention as front-page.php/archive-product.php.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Product\ProductBadges;
use Solar_Template\Product\ProductCartForm;
use Solar_Template\Product\ProductDescription;
use Solar_Template\Product\ProductEngraving;
use Solar_Template\Product\ProductGallery;
use Solar_Template\Product\ProductPanel;
use Solar_Template\Product\ProductReviews;
use Solar_Template\Product\ProductSpecifications;
use Solar_Template\Product\ProductStock;

get_header();

while ( have_posts() ) :
	the_post();

	$solar_product = wc_get_product( get_the_ID() );

	if ( ! $solar_product ) {
		continue;
	}

	$solar_product_images = ProductGallery::images( $solar_product );

	if ( empty( $solar_product_images ) ) {
		$solar_product_images = array(
			array(
				'id'   => 0,
				'full' => wc_placeholder_img_src( 'large' ),
				'alt'  => '',
			),
		);
	}

	$solar_product_panel_args = array(
		'title'              => get_the_title(),
		'badges'             => ProductBadges::for_product( $solar_product ),
		'rating'             => ProductPanel::rating_summary( $solar_product ),
		'stock'              => ProductStock::for_product( $solar_product ),
		'short_description'  => apply_filters( 'woocommerce_short_description', $solar_product->get_short_description() ),
		'cart_form'          => ProductCartForm::for_product( $solar_product ),
		'engraving'          => ProductEngraving::config_for_product( $solar_product ),
		'trust_badges'       => ProductPanel::trust_badges(),
		'accordion_sections' => ProductPanel::accordion_sections(),
	);

	$solar_product_tabs_args = array(
		'product_id'     => $solar_product->get_id(),
		'description'    => array(
			'content' => ProductDescription::content( $solar_product ),
			'image'   => ProductDescription::secondary_image( $solar_product_images ),
		),
		'reviews'        => array(
			'enabled'           => ProductReviews::enabled( $solar_product ),
			'can_submit'        => ProductReviews::can_submit( $solar_product ),
			'summary'           => ProductReviews::summary( $solar_product ),
			'list'              => ProductReviews::list( $solar_product ),
			'comment_form_args' => ProductReviews::comment_form_args( $solar_product ),
		),
		'specifications' => ProductSpecifications::rows( $solar_product ),
	);
	?>
	<main class="product-page">
		<div class="product-page__inner">
			<?php
			woocommerce_breadcrumb(
				array(
					'delimiter'   => '<span class="product-page__breadcrumb-sep" aria-hidden="true">/</span>',
					'wrap_before' => '<nav class="product-page__breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'solar-template' ) . '">',
					'wrap_after'  => '</nav>',
					'before'      => '',
					'after'       => '',
				)
			);
			?>

			<div class="product-page__layout">
				<?php get_template_part( 'template-parts/single-product/gallery', null, array( 'images' => $solar_product_images ) ); ?>
				<?php get_template_part( 'template-parts/single-product/panel', null, $solar_product_panel_args ); ?>
			</div>
		</div>

		<?php get_template_part( 'template-parts/single-product/tabs', null, $solar_product_tabs_args ); ?>
	</main>
	<?php
endwhile;

get_footer();
