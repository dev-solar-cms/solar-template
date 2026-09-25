<?php
/**
 * Created: 2026-09-25 09:30 CEST
 * Role: Front page hero section template-part (template-parts/front-page/hero.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the full-viewport hero (eyebrow label, three-line heading, subtitle, both CTAs,
 *          trust badges, media block with a floating "highlight" card), from
 *          solar_template_hero_config() in functions.php.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero = solar_template_hero_config();

/**
 * Formats a price the same way the design handoff's placeholder data does ("129,00 €").
 *
 * Superseded by WooCommerce's own `wc_price()` once this section is wired to a real featured
 * product; kept intentionally minimal here since this step only renders editorial content (see
 * solar_template_hero_config()). Duplicated (not shared) with product-card.php's identical
 * helper on purpose: both are guarded by function_exists() and this template-part must keep
 * working whether or not the product card partial has already been included on the same page.
 *
 * @param float  $amount          Amount to format.
 * @param string $currency_symbol Currency symbol appended after the amount.
 * @return string Formatted, escaped price.
 */
if ( ! function_exists( 'solar_template_format_placeholder_price' ) ) {
	function solar_template_format_placeholder_price( float $amount, string $currency_symbol ): string {
		return number_format( $amount, 2, ',', ' ' ) . '&nbsp;' . $currency_symbol;
	}
}
?>
<section class="hero">
	<div class="hero__content">
		<?php if ( '' !== $hero['eyebrow'] ) : ?>
			<p class="hero__eyebrow"><?php echo esc_html( $hero['eyebrow'] ); ?></p>
		<?php endif; ?>

		<h1 class="hero__heading">
			<?php foreach ( $hero['heading_lines'] as $line_index => $heading_line ) : ?>
				<span class="hero__heading-line<?php echo 1 === $line_index ? ' hero__heading-line--accent' : ''; ?>">
					<?php echo esc_html( $heading_line ); ?>
				</span>
			<?php endforeach; ?>
		</h1>

		<?php if ( '' !== $hero['subtitle'] ) : ?>
			<p class="hero__subtitle"><?php echo esc_html( $hero['subtitle'] ); ?></p>
		<?php endif; ?>

		<div class="hero__actions">
			<?php if ( ! empty( $hero['primary_cta']['label'] ) ) : ?>
				<a class="btn btn--primary" href="<?php echo esc_url( $hero['primary_cta']['url'] ); ?>">
					<?php echo esc_html( $hero['primary_cta']['label'] ); ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $hero['secondary_cta']['label'] ) ) : ?>
				<a class="btn btn--outline-on-dark" href="<?php echo esc_url( $hero['secondary_cta']['url'] ); ?>">
					<?php echo esc_html( $hero['secondary_cta']['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $hero['trust_badges'] ) ) : ?>
			<ul class="hero__trust">
				<?php foreach ( $hero['trust_badges'] as $trust_badge ) : ?>
					<li class="hero__trust-item">
						<span class="hero__trust-icon">
							<?php echo $trust_badge['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG icon markup defined by the theme itself, never user input. ?>
						</span>
						<span class="hero__trust-label"><?php echo esc_html( $trust_badge['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<div class="hero__media">
		<?php if ( $hero['image_url'] ) : ?>
			<img class="hero__image" src="<?php echo esc_url( $hero['image_url'] ); ?>" alt="<?php echo esc_attr( $hero['image_alt'] ); ?>" />
		<?php endif; ?>

		<?php
		$highlight = $hero['highlight'];
		$has_price = null !== $highlight['price'];
		?>
		<?php if ( '' !== $highlight['title'] || $has_price ) : ?>
			<div class="hero__highlight">
				<?php if ( '' !== $highlight['eyebrow'] ) : ?>
					<p class="hero__highlight-eyebrow"><?php echo esc_html( $highlight['eyebrow'] ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $highlight['title'] ) : ?>
					<p class="hero__highlight-title"><?php echo esc_html( $highlight['title'] ); ?></p>
				<?php endif; ?>

				<?php if ( $has_price ) : ?>
					<p class="hero__highlight-price">
						<?php echo wp_kses_post( solar_template_format_placeholder_price( (float) $highlight['price'], $highlight['currency_symbol'] ) ); ?>
						<?php if ( null !== $highlight['regular_price'] && $highlight['regular_price'] > $highlight['price'] ) : ?>
							<span class="hero__highlight-price-original">
								<?php echo wp_kses_post( solar_template_format_placeholder_price( (float) $highlight['regular_price'], $highlight['currency_symbol'] ) ); ?>
							</span>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( $highlight['progress_percent'] > 0 ) : ?>
					<div class="hero__highlight-progress">
						<div class="hero__highlight-progress-bar" style="width: <?php echo esc_attr( (int) $highlight['progress_percent'] ); ?>%"></div>
					</div>
				<?php endif; ?>

				<?php if ( '' !== $highlight['note'] ) : ?>
					<p class="hero__highlight-note"><?php echo esc_html( $highlight['note'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
