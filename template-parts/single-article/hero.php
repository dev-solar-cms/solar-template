<?php
/**
 * Created: 2026-09-26 22:10 CEST
 * Role: Single article hero image template-part (template-parts/single-article/hero.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the article's full-width featured image (21:8, dark gradient overlay). Renders
 *          nothing when the article has no featured image.
 *
 * @package Solar_Template
 * @var array $args {image_url: string|null, image_alt: string}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'image_url' => null,
	'image_alt' => '',
);

$hero = wp_parse_args( $args ?? array(), $defaults );

if ( ! $hero['image_url'] ) {
	return;
}
?>
<div class="article-hero">
	<img
		class="article-hero__image"
		src="<?php echo esc_url( $hero['image_url'] ); ?>"
		alt="<?php echo esc_attr( $hero['image_alt'] ); ?>"
	/>
	<div class="article-hero__overlay" aria-hidden="true"></div>
</div>
