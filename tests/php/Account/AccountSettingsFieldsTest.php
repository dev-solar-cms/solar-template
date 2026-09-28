<?php
/**
 * Created: 2026-09-26 20:20 CEST
 * Role: Unit test for Solar_Template\Account\AccountSettingsFields.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert `sanitize_birthdate()` accepts a real `Y-m-d` date and rejects malformed input or
 *          a calendar-invalid date (e.g. 30 February), plus save_from_request() persisting exactly
 *          the submitted fields — it has no local nonce check of its own by design, trusting the
 *          native `woocommerce_save_account_details` hook it's exclusively registered on
 *          (Solar_Template\Theme::boot()) to have already verified one; that trust boundary itself is
 *          enforced by tests/php/Security/NonceTrustSweepTest.php, not re-implemented here.
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
	protected function tearDown(): void {
		$_POST                                    = array();
		$GLOBALS['solar_template_test_user_meta'] = array();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_save_from_request_persists_both_submitted_fields(): void {
		$_POST['solar_template_phone']     = '+33 6 12 34 56 78';
		$_POST['solar_template_birthdate'] = '1990-05-21';

		AccountSettingsFields::save_from_request( 7 );

		$this->assertSame( '+33 6 12 34 56 78', AccountSettingsFields::phone( 7 ) );
		$this->assertSame( '1990-05-21', AccountSettingsFields::birthdate( 7 ) );
	}

	/**
	 * An invalid birthdate is stored as an empty string rather than the malformed input, matching
	 * sanitize_birthdate()'s own contract — the field stays optional either way.
	 *
	 * @return void
	 */
	public function test_save_from_request_stores_an_empty_birthdate_when_the_submitted_value_is_invalid(): void {
		$_POST['solar_template_birthdate'] = 'not-a-date';

		AccountSettingsFields::save_from_request( 7 );

		$this->assertSame( '', AccountSettingsFields::birthdate( 7 ) );
	}

	/**
	 * A field absent from the request is left untouched rather than overwritten with an empty value.
	 *
	 * @return void
	 */
	public function test_save_from_request_leaves_an_absent_field_untouched(): void {
		AccountSettingsFields::save_from_request( 7 );

		$this->assertSame( '', AccountSettingsFields::phone( 7 ) );
	}

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
