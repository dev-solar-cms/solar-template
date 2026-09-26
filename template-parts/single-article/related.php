<?php
/**
 * Created: 2026-09-26 22:20 CEST
 * Role: Single article "related articles" template-part (template-parts/single-article/related.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the related-articles grid, reusing the existing blog card component (same
 *          convention as single-product/related-products.php reusing the product card). Renders
 *          nothing when Solar_Template\Blog\RelatedArticles::posts() found no related article.
 *
 * @package Solar_Template
 * @var array $args {heading: array{eyebrow: string, heading: string, view_all: array{label: string,
 *                    url: string}}, posts: array<int, array>}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'heading' => array(
		'eyebrow'  => '',
		'heading'  => '',
		'view_all' => array(
			'label' => '',
			'url'   => '',
		),
	),
	'posts'   => array(),
);

$data = wp_parse_args( $args ?? array(), $defaults );

if ( empty( $data['posts'] ) ) {
	return;
}
?>
<section class="article-related">
	<div class="article-related__inner">
		<div class="article-related__header">
			<div>
				<?php if ( '' !== $data['heading']['eyebrow'] ) : ?>
					<p class="article-related__eyebrow"><?php echo esc_html( $data['heading']['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h2 class="article-related__heading"><?php echo esc_html( $data['heading']['heading'] ); ?></h2>
			</div>

			<?php if ( ! empty( $data['heading']['view_all']['label'] ) ) : ?>
				<a class="article-related__view-all" href="<?php echo esc_url( $data['heading']['view_all']['url'] ); ?>">
					<?php echo esc_html( $data['heading']['view_all']['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="blog-grid">
			<?php foreach ( $data['posts'] as $blog_card_args ) : ?>
				<?php get_template_part( 'template-parts/blog-card', null, $blog_card_args ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
