<?php
/**
 * Created: 2026-09-26 21:55 CEST
 * Role: Main blog index template (home.php), loaded for the site's "Posts page" (Settings >
 *       Reading), since this theme uses a static front page (front-page.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the blog index: dark hero, sticky category filter pills, the featured (sticky)
 *          article on the first, unfiltered page only, then the grid of the theme's own main query
 *          and its pagination. Shares every section but the featured article with archive.php
 *          (category/tag archives).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Blog\BlogHero;
use Solar_Template\Blog\FeaturedPost;

get_header();

$solar_featured_post = ! is_paged() ? FeaturedPost::current() : null;
?>
<main class="blog">
	<?php get_template_part( 'template-parts/blog/hero', null, BlogHero::heading() ); ?>
	<?php get_template_part( 'template-parts/blog/category-filter' ); ?>

	<div class="blog__inner">
		<?php if ( null !== $solar_featured_post ) : ?>
			<?php get_template_part( 'template-parts/blog/featured-article', null, $solar_featured_post ); ?>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/blog/grid' ); ?>
		<?php get_template_part( 'template-parts/blog/pagination' ); ?>
	</div>
</main>
<?php
get_footer();
