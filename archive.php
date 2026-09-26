<?php
/**
 * Created: 2026-09-26 21:58 CEST
 * Role: Blog category/tag archive template (archive.php), loaded by WordPress' own template
 *       hierarchy for category.php/tag.php too since neither exists — the content is identical
 *       (same hero/filter/grid/pagination sections, just fed a different, real query), same
 *       reasoning as archive-product.php serving WooCommerce's own category/tag archives.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a category/tag archive: dark hero (the real term's own name/description, no
 *          editorial placeholder), the same sticky category filter pills as the main blog index
 *          (with the current one flagged active), and the grid/pagination of the theme's own main
 *          query. No featured article block here — that is specific to the unfiltered blog index
 *          (see home.php).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Blog\BlogHero;

get_header();
?>
<main class="blog">
	<?php get_template_part( 'template-parts/blog/hero', null, BlogHero::archive_heading() ); ?>
	<?php get_template_part( 'template-parts/blog/category-filter' ); ?>

	<div class="blog__inner">
		<?php get_template_part( 'template-parts/blog/grid' ); ?>
		<?php get_template_part( 'template-parts/blog/pagination' ); ?>
	</div>
</main>
<?php
get_footer();
