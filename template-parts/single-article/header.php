<?php
/**
 * Created: 2026-09-26 22:12 CEST
 * Role: Single article header template-part (template-parts/single-article/header.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the article's meta line (category/date/reading time), title, and the author bar
 *          with a native share button (assets/js/blog.js, `initArticleShare()` — the Web Share API
 *          where available, a "copy link" clipboard fallback otherwise). The mockup's own "Save"
 *          button has no defined feature/back end anywhere in the project (article bookmarking is
 *          out of scope) and is deliberately left out, same reasoning as other steps' unspecified-
 *          behaviour trims.
 *
 * @package Solar_Template
 * @var array $args {category: string, date: string, reading_time_label: string, title: string,
 *                    permalink: string, author: array{name: string, avatar_url: string|null}}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'category'           => '',
	'date'               => '',
	'reading_time_label' => '',
	'title'              => '',
	'permalink'          => '',
	'author'             => array(
		'name'       => '',
		'avatar_url' => null,
	),
);

$header = wp_parse_args( $args ?? array(), $defaults );
?>
<div class="article-meta">
	<?php if ( '' !== $header['category'] ) : ?>
		<span class="article-meta__category badge badge--exclusive"><?php echo esc_html( $header['category'] ); ?></span>
	<?php endif; ?>
	<?php if ( '' !== $header['date'] ) : ?>
		<span class="article-meta__item"><?php echo esc_html( $header['date'] ); ?></span>
	<?php endif; ?>
	<?php if ( '' !== $header['reading_time_label'] ) : ?>
		<span class="article-meta__sep" aria-hidden="true">·</span>
		<span class="article-meta__item"><?php echo esc_html( $header['reading_time_label'] ); ?></span>
	<?php endif; ?>
</div>

<h1 class="article-title"><?php echo esc_html( $header['title'] ); ?></h1>

<div class="article-author-bar">
	<div class="article-author-bar__profile">
		<?php if ( $header['author']['avatar_url'] ) : ?>
			<img class="article-author-bar__avatar" src="<?php echo esc_url( $header['author']['avatar_url'] ); ?>" alt="" />
		<?php endif; ?>
		<?php if ( '' !== $header['author']['name'] ) : ?>
			<span class="article-author-bar__name"><?php echo esc_html( $header['author']['name'] ); ?></span>
		<?php endif; ?>
	</div>

	<button
		type="button"
		class="article-share-button btn-link"
		data-share-title="<?php echo esc_attr( $header['title'] ); ?>"
		data-share-url="<?php echo esc_url( $header['permalink'] ); ?>"
		data-copied-label="<?php esc_attr_e( 'Link copied!', 'solar-template' ); ?>"
	>
		<?php esc_html_e( 'Share', 'solar-template' ); ?>
	</button>
</div>
