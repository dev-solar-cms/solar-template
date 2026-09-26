<?php
/**
 * Created: 2026-09-26 21:42 CEST
 * Role: Blog category filter template-part (template-parts/blog/category-filter.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the sticky row of category pills (see Solar_Template\Blog\CategoryFilters::items()).
 *          Every pill is a plain link to a real archive URL — no JavaScript needed.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Blog\CategoryFilters;

$items = CategoryFilters::items();
?>
<nav class="blog-category-filter" aria-label="<?php esc_attr_e( 'Filter articles by category', 'solar-template' ); ?>">
	<div class="blog-category-filter__inner">
		<?php foreach ( $items as $item ) : ?>
			<a
				class="pill<?php echo $item['is_active'] ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $item['url'] ); ?>"
			>
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</nav>
