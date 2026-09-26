<?php
/**
 * Created: 2026-09-26 22:40 CEST
 * Role: Unit test for Solar_Template\Pages\ContactInfo.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the "open now" pure comparison against an explicit day/hour, independently of the
 *          real current time (self::is_currently_open() itself is verified manually in Docker).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Pages;

use PHPUnit\Framework\TestCase;
use Solar_Template\Pages\ContactInfo;

final class ContactInfoTest extends TestCase {

	/**
	 * @return array<int, array{0: int, 1: int}>
	 */
	private function weekday_schedule(): array {
		return array(
			1 => array( 9, 18 ),
			2 => array( 9, 18 ),
			3 => array( 9, 18 ),
			4 => array( 9, 18 ),
			5 => array( 9, 18 ),
		);
	}

	/**
	 * @return void
	 */
	public function test_open_during_business_hours(): void {
		$this->assertTrue( ContactInfo::is_open_at( $this->weekday_schedule(), 3, 14 ) );
	}

	/**
	 * @return void
	 */
	public function test_closed_before_opening_hour(): void {
		$this->assertFalse( ContactInfo::is_open_at( $this->weekday_schedule(), 3, 8 ) );
	}

	/**
	 * The closing hour itself is already closed (half-open interval).
	 *
	 * @return void
	 */
	public function test_closed_at_exact_closing_hour(): void {
		$this->assertFalse( ContactInfo::is_open_at( $this->weekday_schedule(), 3, 18 ) );
	}

	/**
	 * @return void
	 */
	public function test_closed_on_a_day_not_in_the_schedule(): void {
		$this->assertFalse( ContactInfo::is_open_at( $this->weekday_schedule(), 6, 14 ) );
	}
}
