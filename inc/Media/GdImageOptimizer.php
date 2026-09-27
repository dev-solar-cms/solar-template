<?php
/**
 * Created: 2026-09-27 11:15 CEST
 * Role: Default image compression backend (Solar_Template\Media).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Implement Solar_Template\Contracts\ImageOptimizerInterface using PHP's own GD extension
 *          (already part of this Docker image, no third-party dependency) — the only implementation
 *          the theme ships, swappable later without touching any calling code. Re-encodes JPEG/PNG/
 *          WebP files at a slightly reduced quality/higher compression, discarding the result and
 *          keeping the original untouched whenever that does not actually shrink the file. Animated
 *          GIFs and any other format GD does not handle here are left completely untouched and
 *          reported as already optimal, rather than risking a broken animation.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Media;

use Solar_Template\Contracts\ImageOptimizerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compresses a JPEG/PNG/WebP file in place using GD.
 */
final class GdImageOptimizer implements ImageOptimizerInterface {

	private const JPEG_QUALITY     = 82;
	private const PNG_COMPRESSION  = 9;
	private const WEBP_QUALITY     = 82;
	private const TEMP_FILE_SUFFIX = '.solar-template-optimize.tmp';

	/**
	 * {@inheritDoc}
	 *
	 * @param string $file_path Absolute path to the image file, modified in place on success.
	 * @return array{success: bool, original_size: int, optimized_size: int, message?: string}
	 */
	public function optimize( string $file_path ): array {
		$original_size = file_exists( $file_path ) ? (int) filesize( $file_path ) : 0;

		if ( 0 === $original_size ) {
			return array(
				'success'        => false,
				'original_size'  => 0,
				'optimized_size' => 0,
				'message'        => __( 'File not found.', 'solar-template' ),
			);
		}

		$image_info = @getimagesize( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- an unreadable/corrupt image file is a normal, expected outcome here (treated as "left unchanged" below), not a bug to surface.
		$mime_type  = is_array( $image_info ) ? ( $image_info['mime'] ?? '' ) : '';

		$image_resource = self::create_image_resource( $file_path, $mime_type );

		if ( null === $image_resource ) {
			return array(
				'success'        => true,
				'original_size'  => $original_size,
				'optimized_size' => $original_size,
				'message'        => __( 'Unsupported image format — left unchanged.', 'solar-template' ),
			);
		}

		$temp_path = $file_path . self::TEMP_FILE_SUFFIX;
		// No imagedestroy() call: GD images have been garbage-collected automatically since PHP 8.0
		// (the function itself is a deprecated no-op as of PHP 8.5).
		$written = self::write_image_resource( $image_resource, $temp_path, $mime_type );

		if ( ! $written || ! file_exists( $temp_path ) ) {
			self::delete_if_exists( $temp_path );

			return array(
				'success'        => false,
				'original_size'  => $original_size,
				'optimized_size' => $original_size,
				'message'        => __( 'Compression failed.', 'solar-template' ),
			);
		}

		clearstatcache( true, $temp_path );
		$temp_size = (int) filesize( $temp_path );

		if ( $temp_size > 0 && $temp_size < $original_size ) {
			rename( $temp_path, $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename -- moving this theme-generated temp file over the original on the same local filesystem; WP_Filesystem's remote-transport abstraction buys nothing here (same convention as Theme::print_critical_css()'s own file_get_contents() ignore).
			clearstatcache( true, $file_path );

			return array(
				'success'        => true,
				'original_size'  => $original_size,
				'optimized_size' => (int) filesize( $file_path ),
			);
		}

		self::delete_if_exists( $temp_path );

		return array(
			'success'        => true,
			'original_size'  => $original_size,
			'optimized_size' => $original_size,
			'message'        => __( 'Already optimal — left unchanged.', 'solar-template' ),
		);
	}

	/**
	 * Creates a GD image resource from $file_path for a supported mime type.
	 *
	 * @param string $file_path Absolute path to the image file.
	 * @param string $mime_type Mime type reported by getimagesize().
	 * @return \GdImage|null
	 */
	private static function create_image_resource( string $file_path, string $mime_type ): ?\GdImage {
		switch ( $mime_type ) {
			case 'image/jpeg':
				$image = @imagecreatefromjpeg( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- a corrupt/truncated file is an expected outcome here, not a bug to surface.
				break;
			case 'image/png':
				$image = @imagecreatefrompng( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				break;
			case 'image/webp':
				$image = function_exists( 'imagecreatefromwebp' ) ? @imagecreatefromwebp( $file_path ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				break;
			default:
				$image = false;
		}

		return $image instanceof \GdImage ? $image : null;
	}

	/**
	 * Writes $image_resource to $temp_path, compressed according to $mime_type.
	 *
	 * @param \GdImage $image_resource GD resource created by self::create_image_resource().
	 * @param string   $temp_path      Destination path for the compressed copy.
	 * @param string   $mime_type      Mime type reported by getimagesize().
	 * @return bool
	 */
	private static function write_image_resource( \GdImage $image_resource, string $temp_path, string $mime_type ): bool {
		switch ( $mime_type ) {
			case 'image/jpeg':
				return (bool) imagejpeg( $image_resource, $temp_path, self::JPEG_QUALITY );
			case 'image/png':
				imagesavealpha( $image_resource, true );
				return (bool) imagepng( $image_resource, $temp_path, self::PNG_COMPRESSION );
			case 'image/webp':
				return function_exists( 'imagewebp' ) && (bool) imagewebp( $image_resource, $temp_path, self::WEBP_QUALITY );
			default:
				return false;
		}
	}

	/**
	 * Deletes $path if it exists, through WordPress' own file deletion wrapper.
	 *
	 * @param string $path Absolute path to delete.
	 * @return void
	 */
	private static function delete_if_exists( string $path ): void {
		if ( file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}
}
