<?php
/**
 * Created: 2026-09-25 11:00 CEST
 * Role: Catalog product cards template-part (template-parts/catalog-cards.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render just the loop of product cards for whichever `$wp_query` is currently the main
 *          query — no wrapping grid element. Extracted out of template-parts/catalog-results.php
 *          so solar_template_handle_catalog_filter_request()'s "load more" (append) mode can
 *          render one page's worth of cards on their own, to be appended into the existing grid
 *          client-side, without duplicating this loop.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! have_posts() ) {
	return;
}

while ( have_posts() ) :
	the_post();

	$catalog_product = wc_get_product( get_the_ID() );

	if ( ! $catalog_product ) {
		continue;
	}

	get_template_part( 'template-parts/product-card', null, solar_template_map_product_to_card_args( $catalog_product ) );
endwhile;

wp_reset_postdata();
