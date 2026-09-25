<?php
/**
 * Created: 2026-09-25 11:30 CEST
 * Role: Front page testimonials section template-part
 *       (template-parts/front-page/testimonials.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the 3-column grid of customer testimonials (see
 *          Solar_Template\FrontPage\Testimonials::testimonials()), with the middle card rendered
 *          in an inverted (dark) style per the design handoff.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\FrontPage\Testimonials;

$testimonials = Testimonials::testimonials();

if ( empty( $testimonials ) ) {
	return;
}

$section_heading = Testimonials::heading();
?>
<section class="testimonials">
	<div class="testimonials__inner">
		<div class="testimonials__header">
			<?php if ( '' !== $section_heading['eyebrow'] ) : ?>
				<p class="testimonials__eyebrow"><?php echo esc_html( $section_heading['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h2 class="testimonials__heading"><?php echo esc_html( $section_heading['heading'] ); ?></h2>
		</div>

		<div class="testimonials__grid">
			<?php foreach ( $testimonials as $testimonial_index => $testimonial ) : ?>
				<figure class="testimonials__item<?php echo 1 === $testimonial_index ? ' testimonials__item--inverted' : ''; ?>">
					<span class="testimonials__quote-mark" aria-hidden="true">&ldquo;</span>
					<blockquote class="testimonials__quote">
						<?php echo esc_html( $testimonial['quote'] ); ?>
					</blockquote>

					<?php if ( $testimonial['rating'] > 0 ) : ?>
						<div class="testimonials__rating" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating out of 5 stars. */ __( '%d out of 5 stars', 'solar-template' ), $testimonial['rating'] ) ); ?>">
							<?php for ( $star_position = 1; $star_position <= 5; $star_position++ ) : ?>
								<?php echo $star_position <= $testimonial['rating'] ? '&#9733;' : '&#9734;'; ?>
							<?php endfor; ?>
						</div>
					<?php endif; ?>

					<figcaption class="testimonials__author">
						<?php if ( $testimonial['avatar_url'] ) : ?>
							<img
								class="testimonials__author-avatar"
								src="<?php echo esc_url( $testimonial['avatar_url'] ); ?>"
								alt=""
							/>
						<?php else : ?>
							<span class="testimonials__author-avatar testimonials__author-avatar--placeholder" aria-hidden="true"></span>
						<?php endif; ?>
						<span>
							<span class="testimonials__author-name"><?php echo esc_html( $testimonial['author_name'] ); ?></span>
							<span class="testimonials__author-since"><?php echo esc_html( $testimonial['author_since'] ); ?></span>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
