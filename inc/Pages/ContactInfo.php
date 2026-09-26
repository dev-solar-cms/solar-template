<?php
/**
 * Created: 2026-09-26 21:10 CEST
 * Role: Contact page "our details" panel content (Solar_Template\Pages).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Provide the contact page's editorial store details (address, phone, email, hours,
 *          guaranteed response time) through a single filterable array, same convention as
 *          Solar_Template\FrontPage\Hero::config()/BrandStory::config() — no dedicated admin
 *          screen yet, a future "General" administration tab is expected to hook into this filter.
 *          The "open now" indicator is real, computed from the configured weekly schedule rather
 *          than a hardcoded value.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact page store details + opening-hours computation.
 */
final class ContactInfo {

	/**
	 * Returns the contact page's store details.
	 *
	 * `schedule` maps ISO-8601 day-of-week numbers (1 = Monday … 7 = Sunday) to an [open, close]
	 * pair of 24h hours, used by self::is_currently_open().
	 *
	 * @return array{
	 *     address_lines: array<int, string>,
	 *     phone: string,
	 *     phone_href: string,
	 *     email: string,
	 *     hours_lines: array<int, string>,
	 *     schedule: array<int, array{0: int, 1: int}>,
	 *     response_time: string,
	 * }
	 */
	public static function config(): array {
		$defaults = array(
			'address_lines' => array(
				__( '12 rue de la Mode', 'solar-template' ),
				__( '75001 Paris, France', 'solar-template' ),
			),
			'phone'         => '+33 1 23 45 67 89',
			'phone_href'    => 'tel:+33123456789',
			'email'         => 'bonjour@solar-template.com',
			'hours_lines'   => array(
				__( 'Mon – Fri: 9am – 6pm', 'solar-template' ),
			),
			'schedule'      => array(
				1 => array( 9, 18 ),
				2 => array( 9, 18 ),
				3 => array( 9, 18 ),
				4 => array( 9, 18 ),
				5 => array( 9, 18 ),
			),
			'response_time' => __( '< 24h', 'solar-template' ),
		);

		/**
		 * Filters the contact page's store details.
		 *
		 * @param array $config See self::config()'s return type.
		 */
		return apply_filters( 'solar_template_contact_info', $defaults );
	}

	/**
	 * Whether the store is currently open, using the real current date/time (site timezone).
	 *
	 * @param array<int, array{0: int, 1: int}> $schedule See self::config()'s `schedule` key.
	 * @return bool
	 */
	public static function is_currently_open( array $schedule ): bool {
		return self::is_open_at( $schedule, (int) current_time( 'N' ), (int) current_time( 'G' ) );
	}

	/**
	 * Pure comparison behind self::is_currently_open(), split out so it can be unit tested with an
	 * explicit day/hour instead of the real current time.
	 *
	 * @param array<int, array{0: int, 1: int}> $schedule    See self::config()'s `schedule` key.
	 * @param int                               $day_of_week ISO-8601 day of week (1 = Monday … 7 = Sunday).
	 * @param int                               $hour        Hour of day, 0–23.
	 * @return bool
	 */
	public static function is_open_at( array $schedule, int $day_of_week, int $hour ): bool {
		if ( ! isset( $schedule[ $day_of_week ] ) ) {
			return false;
		}

		list( $open, $close ) = $schedule[ $day_of_week ];

		return $hour >= $open && $hour < $close;
	}
}
