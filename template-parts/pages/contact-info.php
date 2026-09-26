<?php
/**
 * Created: 2026-09-26 21:50 CEST
 * Role: Contact page "our details" panel template-part (template-parts/pages/contact-info.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the store details card (address, phone, email, hours with a real open-now
 *          indicator, social links) and the guaranteed response time card, from the plain config
 *          built by Solar_Template\Pages\ContactInfo::config(). Reuses
 *          Solar_Template\Header\SocialLinks::links(), same social icons as the header/footer.
 *
 * Expected `$args` keys:
 * - info (array) see Solar_Template\Pages\ContactInfo::config()'s return type
 *
 * @package Solar_Template
 * @var array $args
 */

use Solar_Template\Header\SocialLinks;
use Solar_Template\Pages\ContactInfo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args( $args ?? array(), array( 'info' => array() ) );
$info = wp_parse_args(
	$data['info'],
	array(
		'address_lines' => array(),
		'phone'         => '',
		'phone_href'    => '',
		'email'         => '',
		'hours_lines'   => array(),
		'schedule'      => array(),
		'response_time' => '',
	)
);

$is_open = ContactInfo::is_currently_open( $info['schedule'] );
?>
<div class="contact-info-column">
	<div class="contact-info-card">
		<h2 class="contact-info-card__title"><?php esc_html_e( 'Our details', 'solar-template' ); ?></h2>
		<div class="contact-info-card__rows">
			<div class="contact-info-row">
				<div>
					<div class="contact-info-row__label"><?php esc_html_e( 'Address', 'solar-template' ); ?></div>
					<div class="contact-info-row__value"><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $info['address_lines'] ) ) ); ?></div>
				</div>
			</div>
			<div class="contact-info-row">
				<div>
					<div class="contact-info-row__label"><?php esc_html_e( 'Phone', 'solar-template' ); ?></div>
					<a class="contact-info-row__value" href="<?php echo esc_attr( $info['phone_href'] ); ?>"><?php echo esc_html( $info['phone'] ); ?></a>
				</div>
			</div>
			<div class="contact-info-row">
				<div>
					<div class="contact-info-row__label"><?php esc_html_e( 'Email', 'solar-template' ); ?></div>
					<a class="contact-info-row__value" href="mailto:<?php echo esc_attr( $info['email'] ); ?>"><?php echo esc_html( $info['email'] ); ?></a>
				</div>
			</div>
			<div class="contact-info-row">
				<div>
					<div class="contact-info-row__label"><?php esc_html_e( 'Hours', 'solar-template' ); ?></div>
					<div class="contact-info-row__value"><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $info['hours_lines'] ) ) ); ?></div>
					<div class="contact-info-row__status contact-info-row__status--<?php echo $is_open ? 'open' : 'closed'; ?>">
						<?php echo $is_open ? esc_html__( '● Open now', 'solar-template' ) : esc_html__( '● Closed now', 'solar-template' ); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="contact-info-card__social">
			<div class="contact-info-card__social-label"><?php esc_html_e( 'Follow us', 'solar-template' ); ?></div>
			<div class="contact-info-card__social-links">
				<?php foreach ( SocialLinks::links() as $social_link ) : ?>
					<a
						href="<?php echo esc_url( $social_link['url'] ); ?>"
						class="contact-info-card__social-link"
						aria-label="<?php echo esc_attr( $social_link['label'] ); ?>"
					>
						<?php echo $social_link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG icon markup defined by the theme itself, never user input. ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="contact-response-card">
		<div class="contact-response-card__value"><?php echo esc_html( $info['response_time'] ); ?></div>
		<div class="contact-response-card__title"><?php esc_html_e( 'Guaranteed response time', 'solar-template' ); ?></div>
		<p class="contact-response-card__text"><?php esc_html_e( 'Monday to Friday, business days. For urgent matters, prefer our live chat.', 'solar-template' ); ?></p>
	</div>
</div>
