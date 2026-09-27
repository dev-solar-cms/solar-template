<?php
/**
 * Created: 2026-09-27 12:05 CEST
 * Role: Unit test for Solar_Template\Media\ImageOptimizationStatus.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure "is this file still optimized?" signature comparison behind the real
 *          media library scan step (verified end to end in Docker, see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Media;

use PHPUnit\Framework\TestCase;
use Solar_Template\Media\ImageOptimizationStatus;

/**
 * @covers \Solar_Template\Media\ImageOptimizationStatus
 */
final class ImageOptimizationStatusTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_signature_combines_filesize_and_mtime(): void {
		$this->assertSame( '1024:1700000000', ImageOptimizationStatus::signature( 1024, 1700000000 ) );
	}

	/**
	 * @return void
	 */
	public function test_a_never_processed_attachment_is_not_optimized(): void {
		$this->assertFalse( ImageOptimizationStatus::is_optimized( null, 1024, 1700000000 ) );
		$this->assertFalse( ImageOptimizationStatus::is_optimized( '', 1024, 1700000000 ) );
	}

	/**
	 * @return void
	 */
	public function test_a_matching_signature_is_optimized(): void {
		$signature = ImageOptimizationStatus::signature( 2048, 1700000500 );

		$this->assertTrue( ImageOptimizationStatus::is_optimized( $signature, 2048, 1700000500 ) );
	}

	/**
	 * @return void
	 */
	public function test_a_file_replaced_since_the_last_optimization_is_no_longer_considered_optimized(): void {
		$signature = ImageOptimizationStatus::signature( 2048, 1700000500 );

		// The file's real size changed (a manual re-upload to the same attachment ID) — the
		// recorded signature no longer matches.
		$this->assertFalse( ImageOptimizationStatus::is_optimized( $signature, 4096, 1700000500 ) );

		// Same size, but the modification time changed — also no longer a match.
		$this->assertFalse( ImageOptimizationStatus::is_optimized( $signature, 2048, 1700009999 ) );
	}
}
