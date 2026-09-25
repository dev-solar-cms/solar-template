<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Reusable blog post card template-part (template-parts/blog-card.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a single blog post card (16:10 image, category badge, meta line, title,
 *          excerpt, author) from a plain `$args` array, so it can be reused by the blog archive
 *          grid and the "related articles" section without duplicating markup. This step wires it
 *          with fake data only: `$args` is shaped like WordPress post data on purpose
 *          (title/excerpt/permalink/author) but is not read from a `WP_Post` yet — that mapping is
 *          added once the blog archive is wired to real posts.
 *
 * The meta line (date + reading time, e.g. "Jan 12, 2026 · 5 min read") is accepted as a single
 * pre-formatted string rather than separate fields: the exact wording/order of that combination is
 * a content decision for whichever template composes it (and, once wired to real posts, may need
 * its own translatable format string with a plural reading-time value) — out of scope for a step
 * that only renders this card with fake data.
 *
 * Expected `$args` keys (all optional, see $defaults below):
 * - image_url (string|null), image_alt (string)
 * - permalink (string) post URL
 * - badge (array{type: string, label: string}|null) type is one of new|exclusive|sale|outline-gold
 * - meta (string) pre-formatted date/reading-time line
 * - title (string), excerpt (string)
 * - author_name (string), author_avatar_url (string|null)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'image_url'         => null,
	'image_alt'         => '',
	'permalink'         => '#',
	'badge'             => null,
	'meta'              => '',
	'title'             => '',
	'excerpt'           => '',
	'author_name'       => '',
	'author_avatar_url' => null,
);

$card = wp_parse_args( $args ?? array(), $defaults );
?>
<article class="blog-card">
	<a class="blog-card__media" href="<?php echo esc_url( $card['permalink'] ); ?>">
		<?php if ( $card['image_url'] ) : ?>
			<img
				class="blog-card__image"
				src="<?php echo esc_url( $card['image_url'] ); ?>"
				alt="<?php echo esc_attr( $card['image_alt'] ); ?>"
				loading="lazy"
			/>
		<?php endif; ?>

		<?php if ( ! empty( $card['badge']['type'] ) && ! empty( $card['badge']['label'] ) ) : ?>
			<span class="blog-card__badge badge badge--<?php echo esc_attr( $card['badge']['type'] ); ?>">
				<?php echo esc_html( $card['badge']['label'] ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="blog-card__body">
		<?php if ( '' !== $card['meta'] ) : ?>
			<p class="blog-card__meta"><?php echo esc_html( $card['meta'] ); ?></p>
		<?php endif; ?>

		<h3 class="blog-card__title">
			<a href="<?php echo esc_url( $card['permalink'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a>
		</h3>

		<?php if ( '' !== $card['excerpt'] ) : ?>
			<p class="blog-card__excerpt"><?php echo esc_html( $card['excerpt'] ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $card['author_name'] ) : ?>
			<p class="blog-card__author">
				<?php if ( $card['author_avatar_url'] ) : ?>
					<img
						class="blog-card__author-avatar"
						src="<?php echo esc_url( $card['author_avatar_url'] ); ?>"
						alt=""
					/>
				<?php endif; ?>
				<?php echo esc_html( $card['author_name'] ); ?>
			</p>
		<?php endif; ?>
	</div>
</article>
