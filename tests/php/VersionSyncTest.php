<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Regression test guarding the project's version-sync convention.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Fail the build if `composer.json`, the `style.css` theme header and `.env.example`
 *          ever drift out of sync (see RELEASE.md "Versionnage"). Uses `.env.example` rather than
 *          the gitignored `.env` so this test also runs unmodified in CI.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests;

use PHPUnit\Framework\TestCase;

final class VersionSyncTest extends TestCase {

	/**
	 * The version declared in `composer.json`, `style.css` and `.env.example` must be identical.
	 *
	 * @return void
	 */
	public function test_version_is_identical_across_composer_style_and_env(): void {
		$root = dirname( __DIR__, 2 );

		$composer_version = json_decode( file_get_contents( "{$root}/composer.json" ), true )['version'];

		preg_match( '/^Version:\s*(.+)$/m', file_get_contents( "{$root}/style.css" ), $style_matches );
		$style_version = trim( $style_matches[1] );

		preg_match( '/^SOLAR_TEMPLATE_VERSION=(.+)$/m', file_get_contents( "{$root}/.env.example" ), $env_matches );
		$env_version = trim( $env_matches[1] );

		$this->assertSame( $composer_version, $style_version, 'composer.json and style.css versions differ.' );
		$this->assertSame( $composer_version, $env_version, 'composer.json and .env.example versions differ.' );
	}
}
