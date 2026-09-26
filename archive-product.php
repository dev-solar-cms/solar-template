<?php
/**
 * Created: 2026-09-25 09:48 CEST
 * Role: Product catalog template (archive-product.php), loaded by WooCommerce for the shop page
 *       and, since no more specific template exists yet, for product category/tag archives too
 *       (WooCommerce's own template loader falls back to this file for
 *       `taxonomy-product_cat.php`/`taxonomy-product_tag.php`).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product catalog: breadcrumb, title, the native AJAX filter bar
 *          (template-parts/catalog-filters.php), its "Active filters" chip row
 *          (template-parts/catalog-active-filters.php) and the result count/grid/"load more"
 *          (template-parts/catalog-results.php), fed with real WooCommerce data. Deliberately
 *          does not build the grid/list view toggle shown in the design handoff's mockup — it has
 *          no behaviour specified beyond the mockup itself, so it is left out rather than adding
 *          unspecified, un-testable interactivity. The mockup's separate category quick-links row
 *          is subsumed by the filter bar's own category filter instead of being duplicated.
 *
 *          The filter bar/results optionally sit alongside a sidebar column
 *          (Solar_Template\Catalog\CatalogOptions::has_sidebar_column()) — real content of that
 *          column depends on the "Products" administration tab: the filter bar itself moves there
 *          when its configured position is a sidebar one, and/or the sidebar quick-link widgets
 *          (template-parts/catalog-sidebar-widgets.php) render there when enabled.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Catalog\CatalogOptions;

$solar_filters_position = CatalogOptions::filters_position();
$solar_has_sidebar      = CatalogOptions::has_sidebar_column();
$solar_sidebar_side     = CatalogOptions::sidebar_side();

get_header();
?>
<main class="catalog<?php echo $solar_has_sidebar ? esc_attr( ' catalog--has-sidebar catalog--sidebar-' . $solar_sidebar_side ) : ''; ?>">
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

	<?php if ( 'top' === $solar_filters_position ) : ?>
		<?php get_template_part( 'template-parts/catalog-filters' ); ?>
	<?php endif; ?>

	<div class="catalog__inner">
		<div class="catalog__body">
			<?php if ( $solar_has_sidebar ) : ?>
				<?php if ( 'top' !== $solar_filters_position ) : ?>
					<?php get_template_part( 'template-parts/catalog-filters' ); ?>
				<?php endif; ?>
				<?php get_template_part( 'template-parts/catalog-sidebar-widgets' ); ?>
			<?php endif; ?>

			<div class="catalog__main">
				<?php get_template_part( 'template-parts/catalog-active-filters' ); ?>
				<?php get_template_part( 'template-parts/catalog-results' ); ?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
