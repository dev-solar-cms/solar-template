<?php
/**
 * Created: 2026-09-26 21:45 CEST
 * Role: Blog featured article template-part (template-parts/blog/featured-article.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the 50/50 featured article block from a plain `$args` array (see
 *          Solar_Template\Blog\PostCardMapper::map_featured()). Only ever included by home.php when
 *          Solar_Template\Blog\FeaturedPost::current() returned a real post.
 *
 * Expected `$args` keys (see PostCardMapper::map_featured()):
 * - image_url (string|null), image_alt (string), permalink (string)
 * - category (string), date (string), reading_time_label (string)
 * - title (string), excerpt (string), author_name (string)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'image_url'          => null,
	'image_alt'          => '',
	'permalink'          => '#',
	'category'           => '',
	'date'               => '',
	'reading_time_label' => '',
	'title'              => '',
	'excerpt'            => '',
	'author_name'        => '',
);

$featured = wp_parse_args( $args ?? array(), $defaults );
?>
<div class="blog-featured">
	<a class="blog-featured__link" href="<?php echo esc_url( $featured['permalink'] ); ?>">
		<div class="blog-featured__media">
			<?php if ( $featured['image_url'] ) : ?>
				<img
					class="blog-featured__image"
					src="<?php echo esc_url( $featured['image_url'] ); ?>"
					alt="<?php echo esc_attr( $featured['image_alt'] ); ?>"
					loading="lazy"
				/>
			<?php endif; ?>
			<span class="blog-featured__badge badge badge--exclusive">
				<?php esc_html_e( 'Featured article', 'solar-template' ); ?>
			</span>
		</div>

		<div class="blog-featured__body">
			<div class="blog-featured__meta">
				<?php if ( '' !== $featured['category'] ) : ?>
					<span class="blog-featured__category"><?php echo esc_html( $featured['category'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $featured['date'] ) : ?>
					<span class="blog-featured__meta-item">· <?php echo esc_html( $featured['date'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $featured['reading_time_label'] ) : ?>
					<span class="blog-featured__meta-item">· <?php echo esc_html( $featured['reading_time_label'] ); ?></span>
				<?php endif; ?>
			</div>

			<h2 class="blog-featured__title"><?php echo esc_html( $featured['title'] ); ?></h2>

			<?php if ( '' !== $featured['excerpt'] ) : ?>
				<p class="blog-featured__excerpt"><?php echo esc_html( $featured['excerpt'] ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $featured['author_name'] ) : ?>
				<p class="blog-featured__author"><?php echo esc_html( $featured['author_name'] ); ?></p>
			<?php endif; ?>

			<span class="blog-featured__cta">
				<?php esc_html_e( 'Read the article', 'solar-template' ); ?> <span aria-hidden="true">→</span>
			</span>
		</div>
	</a>
</div>
