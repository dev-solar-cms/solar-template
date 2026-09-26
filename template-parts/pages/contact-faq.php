<?php
/**
 * Created: 2026-09-26 21:55 CEST
 * Role: Contact page FAQ accordion template-part (template-parts/pages/contact-faq.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the FAQ section as a native <details>/<summary> accordion (opens/closes correctly
 *          with both mouse and keyboard with no JavaScript) from the plain content built by
 *          Solar_Template\Pages\Faq. Renders nothing when there is no FAQ entry to show.
 *
 * Expected `$args` keys:
 * - heading (array{eyebrow: string, heading: string})
 * - items (array<int, array{question: string, answer: string}>)
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
		'heading' => array(),
		'items'   => array(),
	)
);

if ( empty( $data['items'] ) ) {
	return;
}

$heading = wp_parse_args(
	$data['heading'],
	array(
		'eyebrow' => '',
		'heading' => '',
	)
);
?>
<section class="contact-faq">
	<div class="contact-faq__inner">
		<div class="contact-faq__header">
			<div class="contact-faq__eyebrow"><?php echo esc_html( $heading['eyebrow'] ); ?></div>
			<h2 class="contact-faq__title"><?php echo esc_html( $heading['heading'] ); ?></h2>
		</div>
		<div class="contact-faq__list">
			<?php foreach ( $data['items'] as $item ) : ?>
				<details class="contact-faq__item">
					<summary class="contact-faq__question">
						<?php echo esc_html( $item['question'] ); ?>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
					</summary>
					<div class="contact-faq__answer"><?php echo esc_html( $item['answer'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
