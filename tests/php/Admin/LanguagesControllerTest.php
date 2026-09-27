<?php
/**
 * Created: 2026-09-27 09:15 CEST
 * Role: Unit test for Solar_Template\Admin\LanguagesController.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure list-filtering/removal-guard logic, independently of the real
 *          WordPress admin-post request-handling flow (verified manually in Docker instead, per
 *          tests/php/bootstrap.php's own purpose note).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\LanguagesController;

/**
 * @covers \Solar_Template\Admin\LanguagesController
 */
final class LanguagesControllerTest extends TestCase {

	/**
	 * available_to_add() removes every already-installed language from the standard list.
	 *
	 * @return void
	 */
	public function test_available_to_add_excludes_installed_languages(): void {
		$standard  = array(
			'fr_FR' => array(
				'label'       => 'Français',
				'flag'        => '🇫🇷',
				'plural_rule' => 'n > 1',
			),
			'es_ES' => array(
				'label'       => 'Español',
				'flag'        => '🇪🇸',
				'plural_rule' => 'n != 1',
			),
		);
		$installed = array(
			'fr_FR' => array(
				'label'      => 'Français',
				'flag'       => '🇫🇷',
				'is_active'  => true,
				'is_default' => true,
			),
		);

		$available = LanguagesController::available_to_add( $standard, $installed );

		$this->assertSame( array( 'es_ES' ), array_keys( $available ) );
	}

	/**
	 * available_to_add() returns nothing when every standard language is already installed.
	 *
	 * @return void
	 */
	public function test_available_to_add_returns_empty_when_nothing_left(): void {
		$standard  = array(
			'fr_FR' => array(
				'label'       => 'Français',
				'flag'        => '🇫🇷',
				'plural_rule' => 'n > 1',
			),
		);
		$installed = array(
			'fr_FR' => array(
				'label'      => 'Français',
				'flag'       => '🇫🇷',
				'is_active'  => true,
				'is_default' => false,
			),
		);

		$this->assertSame( array(), LanguagesController::available_to_add( $standard, $installed ) );
	}

	/**
	 * can_remove() refuses the current default language.
	 *
	 * @return void
	 */
	public function test_can_remove_refuses_the_default_language(): void {
		$this->assertFalse(
			LanguagesController::can_remove(
				array(
					'label'      => 'Français',
					'flag'       => '🇫🇷',
					'is_active'  => true,
					'is_default' => true,
				)
			)
		);
	}

	/**
	 * can_remove() allows a non-default language.
	 *
	 * @return void
	 */
	public function test_can_remove_allows_a_non_default_language(): void {
		$this->assertTrue(
			LanguagesController::can_remove(
				array(
					'label'      => 'English (US)',
					'flag'       => '🇺🇸',
					'is_active'  => true,
					'is_default' => false,
				)
			)
		);
	}
}
