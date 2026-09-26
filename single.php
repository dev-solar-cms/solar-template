<?php
/**
 * Created: 2026-09-26 22:25 CEST
 * Role: Blog article page template (single.php), loaded for every regular post's own page.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the article page: breadcrumb, full-width hero image, meta/title/author bar with
 *          a share button, the real post content with rich typographic styling (blockquote, figure,
 *          lists), tags, an author card, and related articles sharing a category. Grows section by
 *          section, same convention as archive-product.php/single-product.php.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Blog\ArticleAuthor;
use Solar_Template\Blog\ArticleBreadcrumb;
use Solar_Template\Blog\PostCardMapper;
use Solar_Template\Blog\RelatedArticles;

get_header();

while ( have_posts() ) :
	the_post();

	$solar_article  = get_post();
	$solar_featured = PostCardMapper::map_featured( $solar_article );

	$solar_article_tags = get_the_tags( $solar_article );

	$solar_tags = array_map(
		static function ( \WP_Term $tag ): array {
			return array(
				'name' => $tag->name,
				'url'  => get_tag_link( $tag ),
			);
		},
		is_array( $solar_article_tags ) ? $solar_article_tags : array()
	);
	?>
	<main class="article-page">
		<div class="article-page__breadcrumb-wrap">
			<nav class="article-page__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'solar-template' ); ?>">
				<?php foreach ( ArticleBreadcrumb::items( $solar_article ) as $index => $crumb ) : ?>
					<?php if ( 0 !== $index ) : ?>
						<span class="article-page__breadcrumb-sep" aria-hidden="true">/</span>
					<?php endif; ?>
					<?php if ( null !== $crumb['url'] ) : ?>
						<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
					<?php else : ?>
						<span class="article-page__breadcrumb-current"><?php echo esc_html( $crumb['label'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
		</div>

		<?php
		get_template_part(
			'template-parts/single-article/hero',
			null,
			array(
				'image_url' => get_the_post_thumbnail_url( $solar_article, 'full' ),
				'image_alt' => get_the_title( $solar_article ),
			)
		);
		?>

		<article class="article-page__container">
			<?php
			get_template_part(
				'template-parts/single-article/header',
				null,
				array_merge(
					$solar_featured,
					array(
						'permalink' => get_permalink( $solar_article ),
						'author'    => ArticleAuthor::bar( $solar_article ),
					)
				)
			);
			?>

			<div class="article-body">
				<?php the_content(); ?>
			</div>

			<?php get_template_part( 'template-parts/single-article/tags', null, array( 'tags' => $solar_tags ) ); ?>
			<?php get_template_part( 'template-parts/single-article/author-card', null, ArticleAuthor::card( $solar_article ) ); ?>
		</article>

		<?php
		get_template_part(
			'template-parts/single-article/related',
			null,
			array(
				'heading' => RelatedArticles::heading(),
				'posts'   => RelatedArticles::posts( $solar_article ),
			)
		);
		?>
	</main>
	<?php
endwhile;

get_footer();
