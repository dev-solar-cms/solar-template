<?php
/**
 * Created: 2026-09-25 11:00 CEST
 * Role: Front page brand story section template-part (template-parts/front-page/brand-story.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the "Our story" section (image with a floating stat card, heading, paragraphs, a
 *          row of three stats, a CTA), from Solar_Template\FrontPage\BrandStory::config().
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\FrontPage\BrandStory;

$brand_story = BrandStory::config();
?>
<section class="brand-story">
	<div class="brand-story__inner">
		<div class="brand-story__media">
			<div class="brand-story__image">
				<?php if ( $brand_story['image_url'] ) : ?>
					<img
						class="brand-story__image-tag"
						src="<?php echo esc_url( $brand_story['image_url'] ); ?>"
						alt="<?php echo esc_attr( $brand_story['image_alt'] ); ?>"
					/>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $brand_story['highlight']['value'] ) : ?>
				<div class="brand-story__highlight">
					<span class="brand-story__highlight-value"><?php echo esc_html( $brand_story['highlight']['value'] ); ?></span>
					<span class="brand-story__highlight-label"><?php echo esc_html( $brand_story['highlight']['label'] ); ?></span>
				</div>
			<?php endif; ?>
		</div>

		<div class="brand-story__content">
			<?php if ( '' !== $brand_story['eyebrow'] ) : ?>
				<p class="brand-story__eyebrow"><?php echo esc_html( $brand_story['eyebrow'] ); ?></p>
			<?php endif; ?>

			<h2 class="brand-story__heading"><?php echo esc_html( $brand_story['heading'] ); ?></h2>

			<?php foreach ( $brand_story['paragraphs'] as $paragraph ) : ?>
				<p class="brand-story__paragraph"><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>

			<?php if ( ! empty( $brand_story['stats'] ) ) : ?>
				<div class="brand-story__stats">
					<?php foreach ( $brand_story['stats'] as $stat ) : ?>
						<div class="brand-story__stat">
							<span class="brand-story__stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
							<span class="brand-story__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $brand_story['cta']['label'] ) ) : ?>
				<a class="btn btn--dark" href="<?php echo esc_url( $brand_story['cta']['url'] ); ?>">
					<?php echo esc_html( $brand_story['cta']['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
