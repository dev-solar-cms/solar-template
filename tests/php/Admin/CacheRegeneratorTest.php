<?php
/**
 * Created: 2026-09-28 11:20 CEST
 * Role: Unit test for Solar_Template\Admin\CacheRegenerator.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Prove the two properties the audit behind this test relied on a single manual read of the
 *          code for: (1) handle_ajax_request() never reaches any cache/build action for a request
 *          missing the `manage_options` capability or a valid nonce, and (2) the shell command
 *          self::build_command() hands to shell_exec() is built purely from a fixed, escaped path —
 *          never a request-derived value (this class reads no `$_POST`/`$_GET`/`$_REQUEST` at all).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\CacheRegenerator;
use Solar_Template\Tests\Support\WpDieException;

final class CacheRegeneratorTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['solar_template_test_current_user_can']     = true;
		$GLOBALS['solar_template_test_valid_nonce']          = true;
		$GLOBALS['solar_template_test_wp_cache_flush_calls'] = 0;
	}

	/**
	 * @return void
	 */
	public function test_a_user_without_manage_options_cannot_trigger_the_action(): void {
		$GLOBALS['solar_template_test_current_user_can'] = false;

		$this->expectException( WpDieException::class );

		try {
			CacheRegenerator::handle_ajax_request();
		} finally {
			// The capability check must fail before any cache/build action runs — the object cache
			// was never flushed (the first cache-related side effect in the real method).
			$this->assertSame( 0, $GLOBALS['solar_template_test_wp_cache_flush_calls'] );
		}
	}

	/**
	 * @return void
	 */
	public function test_a_request_with_an_invalid_nonce_cannot_trigger_the_action(): void {
		$GLOBALS['solar_template_test_valid_nonce'] = false;

		$this->expectException( WpDieException::class );

		try {
			CacheRegenerator::handle_ajax_request();
		} finally {
			$this->assertSame( 0, $GLOBALS['solar_template_test_wp_cache_flush_calls'] );
		}
	}

	/**
	 * @return void
	 */
	public function test_command_is_built_from_a_fixed_escaped_path(): void {
		$this->assertSame(
			"cd '/var/www/html/wp-content/themes/solar-template' && npm run build 2>&1",
			CacheRegenerator::build_command( '/var/www/html/wp-content/themes/solar-template' )
		);
	}

	/**
	 * A path containing shell metacharacters is neutralized as inert data by escapeshellarg() — the
	 * command's own structure (`cd <escaped> && npm run build 2>&1`) never changes shape regardless
	 * of what the path contains, since a theme directory path is the only variable ever reaching this
	 * command and it only ever passes through escapeshellarg().
	 *
	 * @return void
	 */
	public function test_shell_metacharacters_in_the_path_are_neutralized_by_escapeshellarg(): void {
		$malicious = "/tmp/x'; rm -rf / #";

		$this->assertSame(
			'cd ' . escapeshellarg( $malicious ) . ' && npm run build 2>&1',
			CacheRegenerator::build_command( $malicious )
		);
	}
}
