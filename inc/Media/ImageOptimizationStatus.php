<?php
/**
 * Created: 2026-09-27 11:10 CEST
 * Role: Pure "optimized/not optimized" detection logic (Solar_Template\Media).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Decide whether a media library image still counts as "optimized" from a plain file
 *          signature (size + modification time), with no WordPress dependency, so the rule itself
 *          is unit tested directly. Solar_Template\Media\MediaLibraryScanner is the only caller,
 *          providing the real attachment meta/filesystem values this class compares.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pure helpers deciding whether a stored optimization signature still matches a file's current
 * state.
 */
final class ImageOptimizationStatus {

	/**
	 * Builds the signature recorded on an attachment right after a successful optimization (or a
	 * scan that confirmed the file is already optimal). Comparing this value again later is how a
	 * later scan detects that the underlying file was replaced since — a plain "optimized" boolean
	 * flag alone could never tell the two situations apart.
	 *
	 * @param int $filesize File size in bytes.
	 * @param int $mtime    File modification time (unix timestamp).
	 * @return string
	 */
	public static function signature( int $filesize, int $mtime ): string {
		return "{$filesize}:{$mtime}";
	}

	/**
	 * Whether a file already known as optimized (its last recorded signature) is still optimized
	 * given its current, real filesystem state. A missing/mismatched stored signature both mean
	 * "not optimized" — the former for a file that was never processed, the latter for one that was
	 * replaced since.
	 *
	 * @param string|null $stored_signature  Signature recorded at the last successful optimization,
	 *                                        or null when the attachment was never processed.
	 * @param int         $current_filesize  File's current size in bytes.
	 * @param int         $current_mtime     File's current modification time (unix timestamp).
	 * @return bool
	 */
	public static function is_optimized( ?string $stored_signature, int $current_filesize, int $current_mtime ): bool {
		if ( null === $stored_signature || '' === $stored_signature ) {
			return false;
		}

		return self::signature( $current_filesize, $current_mtime ) === $stored_signature;
	}
}
