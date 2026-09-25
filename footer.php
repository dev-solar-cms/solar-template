<?php
/**
 * Created: 2026-09-25 09:00 CEST
 * Role: Global footer template (footer.php), loaded by every page via get_footer().
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the footer (brand/shop/information/legal columns, a newsletter sign-up form,
 *          payment method badges, copyright line) and close the document opened by header.php.
 *          Content comes from filterable configuration (solar_template_footer_config() and
 *          friends in functions.php) rather than hardcoded values, ready for the future Group 10
 *          administration screen to hook into. The newsletter form is markup only — no submission
 *          handling, out of scope for this step.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_config = solar_template_footer_config();
?>
<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__columns">
			<div class="site-footer__brand-column">
				<span class="site-footer__brand"><?php echo esc_html( $footer_config['brand']['name'] ); ?></span>
				<p class="site-footer__brand-description"><?php echo esc_html( $footer_config['brand']['description'] ); ?></p>
				<div class="site-footer__social">
					<?php foreach ( solar_template_social_links() as $social_link ) : ?>
						<a
							href="<?php echo esc_url( $social_link['url'] ); ?>"
							class="site-footer__social-link"
							aria-label="<?php echo esc_attr( $social_link['label'] ); ?>"
						>
							<?php echo $social_link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG icon markup defined by the theme itself, never user input. ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<?php foreach ( $footer_config['columns'] as $column ) : ?>
				<div class="site-footer__column">
					<span class="site-footer__column-heading"><?php echo esc_html( $column['heading'] ); ?></span>
					<div class="site-footer__column-links">
						<?php foreach ( $column['links'] as $footer_link ) : ?>
							<a class="site-footer__column-link" href="<?php echo esc_url( $footer_link['url'] ); ?>">
								<?php echo esc_html( $footer_link['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="site-footer__newsletter-column">
				<span class="site-footer__column-heading"><?php echo esc_html( $footer_config['newsletter']['heading'] ); ?></span>
				<p class="site-footer__newsletter-description"><?php echo esc_html( $footer_config['newsletter']['description'] ); ?></p>
				<form class="site-footer__newsletter-form">
					<label class="screen-reader-text" for="site-footer-newsletter-email">
						<?php esc_html_e( 'Email address', 'solar-template' ); ?>
					</label>
					<input
						type="email"
						id="site-footer-newsletter-email"
						name="email"
						class="site-footer__newsletter-input"
						placeholder="<?php echo esc_attr__( 'email@example.com', 'solar-template' ); ?>"
					/>
					<button type="submit" class="site-footer__newsletter-submit" aria-label="<?php esc_attr_e( 'Subscribe', 'solar-template' ); ?>">
						→
					</button>
				</form>
			</div>
		</div>

		<div class="site-footer__bottom">
			<span class="site-footer__copyright"><?php echo esc_html( solar_template_footer_copyright() ); ?></span>
			<div class="site-footer__payment-icons">
				<?php foreach ( solar_template_footer_payment_icons() as $payment_icon ) : ?>
					<span class="site-footer__payment-icon"><?php echo esc_html( $payment_icon ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
