<?php
/**
 * Created: 2026-09-26 21:32 CEST
 * Role: Generic page template (page.php), WordPress' default fallback for any Page that doesn't use
 *       a more specific template (a slug-matched template such as page-contact.php, or a selectable
 *       one such as page-templates/legal-page.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a plain page's real title and content with the theme's own typography, replacing
 *          index.php as the fallback WordPress previously used for pages.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="static-page">
	<div class="static-page__inner">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<h1 class="static-page__title"><?php the_title(); ?></h1>
			<div class="static-page__content">
				<?php the_content(); ?>
			</div>
			<?php
		endwhile;
		?>
	</div>
</div>

<?php
get_footer();
