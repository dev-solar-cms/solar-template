<?php
/**
 * Created: 2026-09-26 21:50 CEST
 * Role: Blog archive grid template-part (template-parts/blog/grid.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the current query's posts (the theme's own main loop — home.php/archive.php) as a
 *          plain 3-column grid of the existing blog card component. Renders nothing when the query
 *          has no post at all.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Blog\PostCardMapper;

if ( ! have_posts() ) {
	echo '<p class="blog-grid__empty">' . esc_html__( 'No article to show yet.', 'solar-template' ) . '</p>';

	return;
}
?>
<div class="blog-grid">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/blog-card', null, PostCardMapper::map( get_post() ) );
	endwhile;
	?>
</div>
