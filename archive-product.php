<?php
/**
 * Created: 2026-09-25 09:48 CEST
 * Role: Product catalog template (archive-product.php), loaded by WooCommerce for the shop page
 *       and, since no more specific template exists yet, for product category/tag archives too
 *       (WooCommerce's own template loader falls back to this file for
 *       `taxonomy-product_cat.php`/`taxonomy-product_tag.php`).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product catalog: breadcrumb, title, the native AJAX filter bar
 *          (template-parts/catalog-filters.php) and the result count/grid/pagination
 *          (template-parts/catalog-results.php), fed with real WooCommerce data. Deliberately
 *          does not build the grid/list view toggle shown in the design handoff's mockup — it has
 *          no behaviour specified beyond the mockup itself, so it is left out rather than adding
 *          unspecified, un-testable interactivity. The mockup's separate category quick-links row
 *          is subsumed by the filter bar's own category filter instead of being duplicated.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
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
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/catalog-filters' ); ?>

	<div class="catalog__inner">
		<?php get_template_part( 'template-parts/catalog-results' ); ?>
	</div>
</main>
<?php
get_footer();
