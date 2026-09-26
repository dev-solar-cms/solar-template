<?php
/**
 * Created: 2026-09-26 22:18 CEST
 * Role: Single article author card template-part (template-parts/single-article/author-card.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the larger author card (avatar or initial, name, bio, "View their articles" link)
 *          from Solar_Template\Blog\ArticleAuthor::card(). Renders nothing when the author has no
 *          bio at all, keeping this card's content real rather than inventing placeholder copy.
 *
 * @package Solar_Template
 * @var array $args {name: string, avatar_url: string|null, initial: string, bio: string,
 *                    archive_url: string}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'name'        => '',
	'avatar_url'  => null,
	'initial'     => '',
	'bio'         => '',
	'archive_url' => '#',
);

$author = wp_parse_args( $args ?? array(), $defaults );

if ( '' === $author['bio'] ) {
	return;
}
?>
<div class="article-author-card">
	<?php if ( $author['avatar_url'] ) : ?>
		<img class="article-author-card__avatar" src="<?php echo esc_url( $author['avatar_url'] ); ?>" alt="" />
	<?php else : ?>
		<span class="article-author-card__avatar article-author-card__avatar--initial"><?php echo esc_html( $author['initial'] ); ?></span>
	<?php endif; ?>

	<div>
		<div class="article-author-card__name"><?php echo esc_html( $author['name'] ); ?></div>
		<p class="article-author-card__bio"><?php echo esc_html( $author['bio'] ); ?></p>
		<a class="article-author-card__link" href="<?php echo esc_url( $author['archive_url'] ); ?>">
			<?php esc_html_e( 'View their articles →', 'solar-template' ); ?>
		</a>
	</div>
</div>
