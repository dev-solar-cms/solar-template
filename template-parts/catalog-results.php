<?php
/**
 * Created: 2026-09-25 10:10 CEST
 * Role: Catalog results template-part (template-parts/catalog-results.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the part of the product catalog that changes when a filter is applied: the
 *          result count, the product grid (template-parts/catalog-cards.php) and the empty state,
 *          plus the "load more" status/progress bar/button
 *          (template-parts/catalog-load-more.php) for the resulting first page. Shared by
 *          archive-product.php (initial page load) and solar_template_handle_catalog_filter()
 *          (AJAX re-render, "replace" mode), rendered against whichever `$wp_query` is currently
 *          the main query, so both call sites stay pixel-identical without duplicating this
 *          markup. Not used by that same handler's "append" ("load more") mode, which renders
 *          template-parts/catalog-cards.php/catalog-load-more.php directly instead — appending
 *          more cards to an existing grid is a different operation than (re)rendering the whole
 *          results block.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type string|null $base_url See solar_template_catalog_filters_url()'s $base_url parameter,
 *                                   forwarded to template-parts/catalog-load-more.php.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$results_args = wp_parse_args( $args ?? array(), array( 'base_url' => null ) );

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
			<?php get_template_part( 'template-parts/catalog-cards' ); ?>
		</div>

		<?php get_template_part( 'template-parts/catalog-load-more', null, array( 'base_url' => $results_args['base_url'] ) ); ?>
	<?php else : ?>
		<p class="catalog__empty"><?php esc_html_e( 'No products currently match this selection.', 'solar-template' ); ?></p>
	<?php endif; ?>
</div>
