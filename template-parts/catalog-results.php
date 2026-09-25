<?php
/**
 * Created: 2026-09-25 10:10 CEST
 * Role: Catalog results template-part (template-parts/catalog-results.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the part of the product catalog that changes when a filter is applied: the
 *          result count, the product grid, the empty state and the pagination. Shared by
 *          archive-product.php (initial page load) and solar_template_handle_catalog_filter()
 *          (AJAX re-render), rendered against whichever `$wp_query` is currently the main query,
 *          so both call sites stay pixel-identical without duplicating this markup.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$catalog_columns = solar_template_catalog_columns();
$product_count   = (int) $wp_query->found_posts;
?>
<div id="catalog-results" class="catalog__results">
	<?php if ( $product_count > 0 ) : ?>
		<p class="catalog__count"><?php echo esc_html( solar_template_catalog_result_count_label( $product_count ) ); ?></p>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div
			class="catalog__grid<?php echo 4 === $catalog_columns ? ' catalog__grid--masonry' : ''; ?>"
			style="--catalog-columns: <?php echo esc_attr( (string) $catalog_columns ); ?>;"
		>
			<?php
			while ( have_posts() ) :
				the_post();

				$catalog_product = wc_get_product( get_the_ID() );

				if ( ! $catalog_product ) {
					continue;
				}

				get_template_part( 'template-parts/product-card', null, solar_template_map_product_to_card_args( $catalog_product ) );
			endwhile;

			wp_reset_postdata();
			?>
		</div>

		<div class="catalog__pagination">
			<?php woocommerce_pagination(); ?>
		</div>
	<?php else : ?>
		<p class="catalog__empty"><?php esc_html_e( 'No products currently match this selection.', 'solar-template' ); ?></p>
	<?php endif; ?>
</div>
