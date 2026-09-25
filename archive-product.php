<?php
/**
 * Created: 2026-09-25 09:48 CEST
 * Role: Product catalog template (archive-product.php), loaded by WooCommerce for the shop page
 *       and, since no more specific template exists yet, for product category/tag archives too
 *       (WooCommerce's own template loader falls back to this file for
 *       `taxonomy-product_cat.php`/`taxonomy-product_tag.php`).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product catalog: breadcrumb, title/result count, and a masonry-style grid
 *          of the reusable product card component fed with real WooCommerce data. Deliberately
 *          does not build the header's category quick-links or the grid/list view toggle shown in
 *          the design handoff's mockup — the former overlaps with the category filter the next
 *          step introduces and the latter has no behaviour specified beyond the mockup, so both
 *          are left out rather than adding unspecified, un-testable interactivity.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;

$catalog_columns = solar_template_catalog_columns();
$product_count   = (int) $wp_query->found_posts;
?>
<main class="catalog">
	<div class="catalog__inner">
		<?php
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '<span class="catalog__breadcrumb-sep" aria-hidden="true">/</span>',
				'wrap_before' => '<nav class="catalog__breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'solar-template' ) . '">',
				'wrap_after'  => '</nav>',
				'before'      => '',
				'after'       => '',
			)
		);
		?>

		<div class="catalog__header">
			<div>
				<h1 class="catalog__title"><?php echo esc_html( woocommerce_page_title( false ) ); ?></h1>
				<?php if ( $product_count > 0 ) : ?>
					<p class="catalog__count"><?php echo esc_html( solar_template_catalog_result_count_label( $product_count ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>

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
				?>
			</div>

			<div class="catalog__pagination">
				<?php woocommerce_pagination(); ?>
			</div>
		<?php else : ?>
			<p class="catalog__empty"><?php esc_html_e( 'No products currently match this selection.', 'solar-template' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
