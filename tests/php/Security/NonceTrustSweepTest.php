<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Regression guard for `inc/`'s "trusts a hook's own nonce" pattern (Solar_Template\Tests\Security).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Solar_Template\Account\AccountSettingsFields::save_from_request() and
 *          Solar_Template\Product\EngravingAdminFields::save() both write request data with no local
 *          nonce check of their own — a deliberate, reviewed choice, since each is registered
 *          exclusively on a native WooCommerce hook (`woocommerce_save_account_details`/
 *          `woocommerce_process_product_meta`) that WooCommerce itself only ever fires after its own
 *          nonce check already passed. This test scans every file under `inc/` and fails the moment a
 *          new, un-reviewed file starts writing `$_POST`/`$_FILES` data with zero local nonce
 *          verification anywhere in it — the exact audit this file's own history (see RELEASE.md)
 *          performed once by hand, now automated. Solar_Template\Account\ReorderHandler and
 *          Solar_Template\Account\SupportRequestController are the theme's own reference for the
 *          opposite, safer pattern: a nonce verified locally (`wp_verify_nonce()`/
 *          `check_admin_referer()`) before any write, regardless of which hook triggers them.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Security;

use PHPUnit\Framework\TestCase;

final class NonceTrustSweepTest extends TestCase {

	/**
	 * Files reviewed and confirmed safe to write `$_POST`/`$_FILES` data with no local nonce check of
	 * their own, relative to `inc/`, each documenting in its own code exactly why:
	 * - `Account/AccountSettingsFields.php` and `Product/EngravingAdminFields.php` are registered
	 *   exclusively on a native WooCommerce hook that WooCommerce itself only fires after its own
	 *   nonce check already passed (verified against `inc/Theme.php`'s own hook registrations).
	 * - `Product/EngravingCart.php` rides along WooCommerce's own "Add to cart" submission, which is
	 *   not nonce-protected by WooCommerce core itself either — the same trust level as any other
	 *   add-to-cart field, not a new gap introduced by this theme.
	 *
	 * @var array<int, string>
	 */
	private const REVIEWED_FILES = array(
		'Account/AccountSettingsFields.php',
		'Product/EngravingAdminFields.php',
		'Product/EngravingCart.php',
	);

	/**
	 * @return void
	 */
	public function test_only_the_reviewed_files_write_request_data_with_no_local_nonce_check(): void {
		$inc_dir   = dirname( __DIR__, 3 ) . '/inc';
		$offenders = array();

		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $inc_dir, \FilesystemIterator::SKIP_DOTS ) );

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || 'php' !== $file->getExtension() ) {
				continue;
			}

			$contents = (string) file_get_contents( $file->getPathname() );

			if ( ! preg_match( '/\$_POST\[|\$_FILES\[/', $contents ) ) {
				continue;
			}

			if ( preg_match( '/check_admin_referer\(|check_ajax_referer\(|wp_verify_nonce\(/', $contents ) ) {
				continue;
			}

			$offenders[] = ltrim( str_replace( $inc_dir, '', $file->getPathname() ), '/' );
		}

		sort( $offenders );
		$expected = self::REVIEWED_FILES;
		sort( $expected );

		$this->assertSame(
			$expected,
			$offenders,
			'A file under inc/ writes $_POST/$_FILES data with no local nonce check and is not in the ' .
			'reviewed allow-list above — either add a local check, or review it and add it to the list.'
		);
	}
}
