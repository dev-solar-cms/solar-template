<?php
/**
 * Created: 2026-09-26 21:35 CEST
 * Role: "Contact" page template (page-contact.php), auto-matched by WordPress' own page template
 *       hierarchy for the page whose slug is "contact" — same file-based convention as
 *       archive-product.php/single-product.php, no manual template selection needed.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the contact hero, native contact form + store details panel, a map placeholder and
 *          the FAQ accordion, all fed by Solar_Template\Pages\ContactController/ContactInfo/Faq per
 *          DECISIONS.md's MVC split — this file only displays what those classes return.
 *
 * @package Solar_Template
 */

use Solar_Template\Pages\ContactController;
use Solar_Template\Pages\ContactInfo;
use Solar_Template\Pages\Faq;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="contact-hero">
	<div class="contact-hero__inner">
		<div class="contact-hero__eyebrow"><?php esc_html_e( 'We are here for you', 'solar-template' ); ?></div>
		<h1 class="contact-hero__title"><?php esc_html_e( 'Contact us', 'solar-template' ); ?></h1>
		<p class="contact-hero__subtitle"><?php esc_html_e( 'Our team replies within 24 business hours, Monday to Friday, 9am to 6pm.', 'solar-template' ); ?></p>
	</div>
</section>

<div class="contact-page">
	<div class="contact-page__inner">
		<div class="contact-page__grid">
			<?php
			get_template_part(
				'template-parts/pages/contact-form',
				null,
				array(
					'subjects' => ContactController::subjects(),
					'feedback' => ContactController::feedback_from_query(),
				)
			);

			get_template_part(
				'template-parts/pages/contact-info',
				null,
				array( 'info' => ContactInfo::config() )
			);
			?>
		</div>
	</div>
</div>

<div class="contact-map-placeholder">
	<div class="contact-map-placeholder__inner">
		<span class="contact-map-placeholder__icon" aria-hidden="true">📍</span>
		<span class="contact-map-placeholder__text"><?php esc_html_e( 'Interactive map coming soon', 'solar-template' ); ?></span>
	</div>
</div>

<?php
get_template_part(
	'template-parts/pages/contact-faq',
	null,
	array(
		'heading' => Faq::heading(),
		'items'   => Faq::items(),
	)
);

get_footer();
