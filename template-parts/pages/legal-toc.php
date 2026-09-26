<?php
/**
 * Created: 2026-09-26 21:40 CEST
 * Role: Legal page sticky table of contents template-part (template-parts/pages/legal-toc.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the sticky table of contents from the plain heading list already built by
 *          Solar_Template\Pages\LegalPageController::annotate_headings(), plus a "need help?" box
 *          linking to the real contact page.
 *
 * Expected `$args` keys:
 * - headings (array<int, array{id: string, label: string}>)
 * - contact_url (string) real permalink of the site's contact page (already resolved by the caller)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args(
	$args ?? array(),
	array(
		'headings'    => array(),
		'contact_url' => home_url( '/' ),
	)
);

if ( empty( $data['headings'] ) ) {
	return;
}
?>
<aside class="legal-toc">
	<div class="legal-toc__label"><?php esc_html_e( 'Table of contents', 'solar-template' ); ?></div>
	<nav class="legal-toc__list">
		<?php foreach ( $data['headings'] as $index => $heading ) : ?>
			<a href="#<?php echo esc_attr( $heading['id'] ); ?>" class="legal-toc__item">
				<span class="legal-toc__number"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
				<?php echo esc_html( $heading['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<div class="legal-toc__help">
		<div class="legal-toc__help-title"><?php esc_html_e( 'Need help?', 'solar-template' ); ?></div>
		<p class="legal-toc__help-text"><?php esc_html_e( 'Our team replies within 24 business hours.', 'solar-template' ); ?></p>
		<a class="legal-toc__help-link" href="<?php echo esc_url( $data['contact_url'] ); ?>">
			<?php esc_html_e( 'Get in touch', 'solar-template' ); ?> →
		</a>
	</div>
</aside>
