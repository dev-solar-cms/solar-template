<?php
/**
 * Created: 2026-09-26 20:20 CEST
 * Role: Unit test for Solar_Template\Account\AccountSettingsFields.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert `sanitize_birthdate()` accepts a real `Y-m-d` date and rejects malformed input or
 *          a calendar-invalid date (e.g. 30 February), the only pure logic in this class — reading/
 *          saving user meta itself is exercised functionally in Docker (see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\AccountSettingsFields;

final class AccountSettingsFieldsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_accepts_a_real_date(): void {
		$this->assertSame( '1990-05-21', AccountSettingsFields::sanitize_birthdate( '1990-05-21' ) );
	}

	/**
	 * @return void
	 */
	public function test_rejects_a_calendar_invalid_date(): void {
		$this->assertSame( '', AccountSettingsFields::sanitize_birthdate( '1990-02-30' ) );
	}

	/**
	 * @return void
	 */
	public function test_rejects_a_malformed_value(): void {
		$this->assertSame( '', AccountSettingsFields::sanitize_birthdate( 'not-a-date' ) );
	}

	/**
	 * @return void
	 */
	public function test_empty_value_stays_empty(): void {
		$this->assertSame( '', AccountSettingsFields::sanitize_birthdate( '' ) );
	}
}
