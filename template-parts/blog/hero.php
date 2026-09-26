<?php
/**
 * Created: 2026-09-26 21:40 CEST
 * Role: Blog page hero template-part (template-parts/blog/hero.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the dark hero shared by the main blog index and every category/tag archive, from
 *          a plain `$args` array (see Solar_Template\Blog\BlogHero::heading()/archive_heading()).
 *
 * Expected `$args` keys:
 * - eyebrow (string), heading (string), subtitle (string)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'eyebrow'  => '',
	'heading'  => '',
	'subtitle' => '',
);

$hero = wp_parse_args( $args ?? array(), $defaults );
?>
<section class="blog-hero">
	<div class="blog-hero__inner">
		<?php if ( '' !== $hero['eyebrow'] ) : ?>
			<p class="blog-hero__eyebrow"><?php echo esc_html( $hero['eyebrow'] ); ?></p>
		<?php endif; ?>

		<h1 class="blog-hero__heading"><?php echo esc_html( $hero['heading'] ); ?></h1>

		<?php if ( '' !== $hero['subtitle'] ) : ?>
			<p class="blog-hero__subtitle"><?php echo esc_html( $hero['subtitle'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
