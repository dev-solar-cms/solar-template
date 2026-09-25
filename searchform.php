<?php
/**
 * Created: 2026-09-25 09:30 CEST
 * Role: Native search form template (searchform.php), loaded by WordPress' own get_search_form().
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Customize the markup get_search_form() outputs, reused inside the header's search
 *          overlay (see header.php, assets/js/header.js). Submits a native WordPress search
 *          (`?s=`) — WordPress already includes every public, searchable post type (including
 *          WooCommerce's "product" once it's active) in the results by default, so no extra
 *          wiring is needed to search products too.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_field_id = wp_unique_id( 'site-search-field-' );
?>
<form role="search" method="get" class="site-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $search_field_id ); ?>">
		<?php esc_html_e( 'Search this site', 'solar-template' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $search_field_id ); ?>"
		class="site-search-form__input"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php echo esc_attr__( 'Search products, articles…', 'solar-template' ); ?>"
	/>
	<button type="submit" class="site-search-form__submit">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'solar-template' ); ?></span>
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
	</button>
</form>
