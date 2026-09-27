<?php
/**
 * Created: 2026-09-27 12:15 CEST
 * Role: Unit test for Solar_Template\Media\GdImageOptimizer.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the real compression behaviour (a noisy JPEG genuinely shrinks, an unsupported
 *          format and a missing file are both handled gracefully) — this is the default
 *          Solar_Template\Contracts\ImageOptimizerInterface implementation exercised by the real
 *          "Optimize images" admin action, verified end to end in Docker (see RELEASE.md).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Media;

use PHPUnit\Framework\TestCase;
use Solar_Template\Media\GdImageOptimizer;

/**
 * @covers \Solar_Template\Media\GdImageOptimizer
 */
final class GdImageOptimizerTest extends TestCase {

	/**
	 * @var array<int, string>
	 */
	private array $temp_files = array();

	/**
	 * @return void
	 */
	protected function setUp(): void {
		if ( ! extension_loaded( 'gd' ) ) {
			$this->markTestSkipped( 'The gd extension is not available in this environment.' );
		}
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		foreach ( $this->temp_files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- test cleanup, not theme code.
			}
		}
	}

	/**
	 * A noisy JPEG re-saved at a lower quality genuinely shrinks, and the file is replaced in
	 * place.
	 *
	 * @return void
	 */
	public function test_optimize_shrinks_a_noisy_jpeg(): void {
		$path = $this->create_noisy_jpeg();

		$original_size = filesize( $path );
		$result        = ( new GdImageOptimizer() )->optimize( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( $original_size, $result['original_size'] );
		$this->assertLessThan( $result['original_size'], $result['optimized_size'] );
		$this->assertSame( $result['optimized_size'], filesize( $path ) );
	}

	/**
	 * A PNG is never left larger than it started, whether or not compression found any gain.
	 *
	 * @return void
	 */
	public function test_optimize_never_grows_a_png(): void {
		$path = $this->create_png();

		$original_size = filesize( $path );
		$result        = ( new GdImageOptimizer() )->optimize( $path );

		$this->assertTrue( $result['success'] );
		$this->assertLessThanOrEqual( $original_size, $result['optimized_size'] );
		$this->assertLessThanOrEqual( $original_size, filesize( $path ) );
	}

	/**
	 * An unsupported format is left completely untouched, reported as already optimal rather than
	 * failing.
	 *
	 * @return void
	 */
	public function test_optimize_leaves_an_unsupported_format_untouched(): void {
		$path = sys_get_temp_dir() . '/solar-template-test-' . uniqid() . '.bmp';
		file_put_contents( $path, 'not a real image' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- test fixture, not theme code.
		$this->temp_files[] = $path;

		$result = ( new GdImageOptimizer() )->optimize( $path );

		$this->assertTrue( $result['success'] );
		$this->assertSame( $result['original_size'], $result['optimized_size'] );
		$this->assertSame( 'not a real image', file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * A missing file is reported as a failure rather than fataling.
	 *
	 * @return void
	 */
	public function test_optimize_reports_a_missing_file(): void {
		$result = ( new GdImageOptimizer() )->optimize( sys_get_temp_dir() . '/solar-template-does-not-exist.jpg' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 0, $result['original_size'] );
		$this->assertSame( 0, $result['optimized_size'] );
	}

	/**
	 * @return string Absolute path to a freshly created, maximum-quality noisy JPEG fixture.
	 */
	private function create_noisy_jpeg(): string {
		$path  = sys_get_temp_dir() . '/solar-template-test-' . uniqid() . '.jpg';
		$image = imagecreatetruecolor( 400, 400 );

		mt_srand( 12345 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_seeding_mt_srand -- a deterministic, repeatable test fixture, not real randomness.
		for ( $x = 0; $x < 400; $x++ ) {
			for ( $y = 0; $y < 400; $y++ ) {
				imagesetpixel( $image, $x, $y, imagecolorallocate( $image, mt_rand( 0, 255 ), mt_rand( 0, 255 ), mt_rand( 0, 255 ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_mt_rand -- a deterministic, repeatable test fixture, not real randomness.
			}
		}

		imagejpeg( $image, $path, 100 );

		$this->temp_files[] = $path;

		return $path;
	}

	/**
	 * @return string Absolute path to a freshly created PNG fixture.
	 */
	private function create_png(): string {
		$path  = sys_get_temp_dir() . '/solar-template-test-' . uniqid() . '.png';
		$image = imagecreatetruecolor( 200, 200 );
		imagefill( $image, 0, 0, imagecolorallocate( $image, 90, 140, 200 ) );
		imagepng( $image, $path, 1 );

		$this->temp_files[] = $path;

		return $path;
	}
}
