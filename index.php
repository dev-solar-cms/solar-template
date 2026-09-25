<?php
/**
 * Fallback template.
 */

get_header();
?>

<main id="primary">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'solar-template' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
