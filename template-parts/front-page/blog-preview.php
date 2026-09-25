<?php
/**
 * Created: 2026-09-25 12:00 CEST
 * Role: Front page blog preview section template-part
 *       (template-parts/front-page/blog-preview.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the 3-column grid of the latest published blog posts, using the reusable blog
 *          card template-part (see Solar_Template\FrontPage\BlogPreview::posts()). Renders
 *          nothing when the site has no published post yet.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\FrontPage\BlogPreview;

$blog_posts = BlogPreview::posts();

if ( empty( $blog_posts ) ) {
	return;
}

$section_heading = BlogPreview::heading();
?>
<section class="blog-preview">
	<div class="blog-preview__inner">
		<div class="blog-preview__header">
			<div>
				<?php if ( '' !== $section_heading['eyebrow'] ) : ?>
					<p class="blog-preview__eyebrow"><?php echo esc_html( $section_heading['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h2 class="blog-preview__heading"><?php echo esc_html( $section_heading['heading'] ); ?></h2>
			</div>

			<?php if ( ! empty( $section_heading['view_all']['label'] ) ) : ?>
				<a class="blog-preview__view-all" href="<?php echo esc_url( $section_heading['view_all']['url'] ); ?>">
					<?php echo esc_html( $section_heading['view_all']['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="blog-preview__grid">
			<?php foreach ( $blog_posts as $blog_card_args ) : ?>
				<?php get_template_part( 'template-parts/blog-card', null, $blog_card_args ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
