<?php
/**
 * Created: 2026-09-27 11:05 CEST
 * Role: Part of the theme's dependency-inversion layer (Solar_Template\Contracts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define the contract used by the theme to compress an image file in place, so the actual
 *          compression backend (currently PHP's own GD extension) can be swapped later (e.g. for a
 *          native Imagick implementation, or a paid third-party service) without changing any code
 *          that triggers an optimization.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compresses a single image file on disk, in place.
 */
interface ImageOptimizerInterface {

	/**
	 * Attempts to reduce $file_path's size on disk without a noticeable loss of visual quality.
	 * Implementations must never leave the file smaller-but-corrupted: when compression does not
	 * yield a smaller result, the original file is left untouched.
	 *
	 * @param string $file_path Absolute path to the image file, modified in place on success.
	 * @return array{success: bool, original_size: int, optimized_size: int, message?: string}
	 */
	public function optimize( string $file_path ): array;
}
