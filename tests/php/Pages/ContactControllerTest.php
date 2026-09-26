<?php
/**
 * Created: 2026-09-26 22:35 CEST
 * Role: Unit test for Solar_Template\Pages\ContactController.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure validation logic (required fields, email format, consent) and the
 *          honeypot spam check, independently of the real WordPress request-handling flow (verified
 *          manually in Docker instead, per tests/php/bootstrap.php's own purpose note).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Pages;

use PHPUnit\Framework\TestCase;
use Solar_Template\Pages\ContactController;

final class ContactControllerTest extends TestCase {

	/**
	 * @return array{first_name: string, last_name: string, email: string, subject: string, message: string, consent: bool}
	 */
	private function valid_data(): array {
		return array(
			'first_name' => 'Marie',
			'last_name'  => 'Dupont',
			'email'      => 'marie@example.com',
			'subject'    => 'product_question',
			'message'    => 'Do you have this in size M?',
			'consent'    => true,
		);
	}

	/**
	 * A fully filled, valid submission has no errors.
	 *
	 * @return void
	 */
	public function test_valid_submission_has_no_errors(): void {
		$this->assertSame( array(), ContactController::validate( $this->valid_data() ) );
	}

	/**
	 * @return void
	 */
	public function test_missing_required_text_fields_are_reported(): void {
		$data               = $this->valid_data();
		$data['first_name'] = '   ';
		$data['message']    = '';

		$errors = ContactController::validate( $data );

		$this->assertContains( 'first_name', $errors );
		$this->assertContains( 'message', $errors );
		$this->assertNotContains( 'last_name', $errors );
	}

	/**
	 * @return void
	 */
	public function test_invalid_email_is_reported(): void {
		$data          = $this->valid_data();
		$data['email'] = 'not-an-email';

		$this->assertContains( 'email', ContactController::validate( $data ) );
	}

	/**
	 * @return void
	 */
	public function test_missing_subject_is_reported(): void {
		$data            = $this->valid_data();
		$data['subject'] = '';

		$this->assertContains( 'subject', ContactController::validate( $data ) );
	}

	/**
	 * @return void
	 */
	public function test_missing_consent_is_reported(): void {
		$data            = $this->valid_data();
		$data['consent'] = false;

		$this->assertContains( 'consent', ContactController::validate( $data ) );
	}

	/**
	 * @return void
	 */
	public function test_honeypot_field_left_empty_is_not_spam(): void {
		$this->assertFalse( ContactController::is_spam( '' ) );
	}

	/**
	 * @return void
	 */
	public function test_honeypot_field_filled_in_is_spam(): void {
		$this->assertTrue( ContactController::is_spam( 'Some Company LLC' ) );
	}

	/**
	 * @return void
	 */
	public function test_whitespace_only_honeypot_is_not_spam(): void {
		$this->assertFalse( ContactController::is_spam( '   ' ) );
	}
}
