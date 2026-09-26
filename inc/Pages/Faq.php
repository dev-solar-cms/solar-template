<?php
/**
 * Created: 2026-09-26 21:15 CEST
 * Role: Contact page FAQ content (Solar_Template\Pages).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the contact page's FAQ accordion header and question/answer entries through a
 *          single filterable array, same convention as Solar_Template\FrontPage\Testimonials — no
 *          dedicated custom post type/admin screen for this step, a future administration tab is
 *          expected to hook into these filters.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact page FAQ accordion content.
 */
final class Faq {

	/**
	 * Returns the FAQ section header (eyebrow label, heading).
	 *
	 * @return array{eyebrow: string, heading: string}
	 */
	public static function heading(): array {
		$defaults = array(
			'eyebrow' => __( 'FAQ', 'solar-template' ),
			'heading' => __( 'Frequently asked questions', 'solar-template' ),
		);

		/**
		 * Filters the contact page's FAQ section header.
		 *
		 * @param array $config See self::heading()'s return type.
		 */
		return apply_filters( 'solar_template_faq_heading', $defaults );
	}

	/**
	 * Returns the FAQ accordion's question/answer entries.
	 *
	 * @return array<int, array{question: string, answer: string}>
	 */
	public static function items(): array {
		$defaults = array(
			array(
				'question' => __( 'What are the delivery times?', 'solar-template' ),
				'answer'   => __( 'Standard delivery takes 3 to 5 business days via tracked shipping. For urgent orders, Express delivery (24-48h) is available for a small fee. Shipping is free from €60 of purchase.', 'solar-template' ),
			),
			array(
				'question' => __( 'How do I return or exchange an item?', 'solar-template' ),
				'answer'   => __( 'You have 30 days from receiving your order to return an item in its original condition (unworn, tags attached, original packaging). Log in to your account and follow the return procedure. Refunds are processed within 5 to 7 business days.', 'solar-template' ),
			),
			array(
				'question' => __( 'Do you ship internationally?', 'solar-template' ),
				'answer'   => __( 'Yes, we ship across Europe. Rates and delivery times vary by destination country; additional fees may apply for some countries.', 'solar-template' ),
			),
			array(
				'question' => __( 'How can I track my order?', 'solar-template' ),
				'answer'   => __( 'As soon as your order ships, you receive a confirmation email with your tracking number. You can also track your order in real time from your account, under "My orders".', 'solar-template' ),
			),
			array(
				'question' => __( 'How do I personalize a product (engraving, embroidery)?', 'solar-template' ),
				'answer'   => __( 'On eligible product pages, a personalization option is available directly. Enable it, enter your text and a surcharge applies. Personalized orders cannot be exchanged.', 'solar-template' ),
			),
		);

		/**
		 * Filters the contact page's FAQ accordion entries.
		 *
		 * @param array $items See self::items()'s return type.
		 */
		return apply_filters( 'solar_template_faq_items', $defaults );
	}
}
