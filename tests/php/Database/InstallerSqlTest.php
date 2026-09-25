<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for the pure SQL-builder methods of Solar_Template\Database\Installer.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Guard the `dbDelta()`-specific formatting quirks (double space before `KEY`) that are
 *          easy to break by accident and hard to notice without running the theme in WordPress.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Database;

use PHPUnit\Framework\TestCase;
use Solar_Template\Database\Installer;

/**
 * @covers \Solar_Template\Database\Installer
 */
final class InstallerSqlTest extends TestCase {

	/**
	 * The languages table SQL uses the given prefix/charset and the dbDelta-required double space
	 * before `PRIMARY KEY`.
	 *
	 * @return void
	 */
	public function test_languages_table_sql_uses_prefix_and_dbdelta_formatting(): void {
		$sql = Installer::languages_table_sql( 'wptests_', 'DEFAULT CHARACTER SET utf8mb4' );

		$this->assertStringContainsString( 'CREATE TABLE wptests_solar_template_languages', $sql );
		$this->assertStringContainsString( 'PRIMARY KEY  (id)', $sql );
		$this->assertStringContainsString( 'UNIQUE KEY code (code)', $sql );
		$this->assertStringContainsString( 'DEFAULT CHARACTER SET utf8mb4', $sql );
	}

	/**
	 * The translations table SQL declares the composite unique key used by set_string()/replace().
	 *
	 * @return void
	 */
	public function test_translations_table_sql_declares_composite_unique_key(): void {
		$sql = Installer::translations_table_sql( 'wptests_', 'DEFAULT CHARACTER SET utf8mb4' );

		$this->assertStringContainsString( 'CREATE TABLE wptests_solar_template_translations', $sql );
		$this->assertStringContainsString(
			'UNIQUE KEY language_string_context (language_code,string_key,context)',
			$sql
		);
	}

	/**
	 * The settings table SQL declares a unique key on `setting_key` so `replace()` upserts correctly.
	 *
	 * @return void
	 */
	public function test_settings_table_sql_declares_unique_setting_key(): void {
		$sql = Installer::settings_table_sql( 'wptests_', 'DEFAULT CHARACTER SET utf8mb4' );

		$this->assertStringContainsString( 'CREATE TABLE wptests_solar_template_settings', $sql );
		$this->assertStringContainsString( 'UNIQUE KEY setting_key (setting_key)', $sql );
	}
}
