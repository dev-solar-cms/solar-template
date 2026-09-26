<?php
/**
 * Created: 2026-09-26 22:15 CEST
 * Role: Single article tags template-part (template-parts/single-article/tags.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the article's real WordPress tags as pills linking to their own tag archive.
 *          Renders nothing when the article has no tag.
 *
 * @package Solar_Template
 * @var array $args {tags: array<int, array{name: string, url: string}>}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'tags' => array(),
);

$data = wp_parse_args( $args ?? array(), $defaults );

if ( empty( $data['tags'] ) ) {
	return;
}
?>
<div class="article-tags">
	<span class="article-tags__label"><?php esc_html_e( 'Tags:', 'solar-template' ); ?></span>
	<?php foreach ( $data['tags'] as $article_tag ) : ?>
		<a class="pill" href="<?php echo esc_url( $article_tag['url'] ); ?>"><?php echo esc_html( $article_tag['name'] ); ?></a>
	<?php endforeach; ?>
</div>
