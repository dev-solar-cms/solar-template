<?php
/**
 * Created: 2026-09-25 09:30 CEST
 * Role: Front page template (front-page.php), used by WordPress for the site's home page
 *       regardless of the "reading" setting (a static page or the latest posts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assemble the home page's sections, each a self-contained template-part under
 *          template-parts/front-page/. Built incrementally, one section at a time, per the
 *          project roadmap; sections are added here in the order they land.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="front-page">
	<?php get_template_part( 'template-parts/front-page/hero' ); ?>
	<?php get_template_part( 'template-parts/front-page/featured-products' ); ?>
	<?php get_template_part( 'template-parts/front-page/categories' ); ?>
	<?php get_template_part( 'template-parts/front-page/brand-story' ); ?>
	<?php get_template_part( 'template-parts/front-page/testimonials' ); ?>
	<?php get_template_part( 'template-parts/front-page/blog-preview' ); ?>
	<?php get_template_part( 'template-parts/front-page/newsletter' ); ?>
</main>

<?php
get_footer();
