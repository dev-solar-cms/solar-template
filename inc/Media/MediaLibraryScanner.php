<?php
/**
 * Created: 2026-09-27 11:25 CEST
 * Role: Media library query/meta glue for image optimization (Solar_Template\Media).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The only class that talks to WordPress (WP_Query/post meta/the filesystem) for the image
 *          optimization feature — every actual "is this still optimized?" decision is delegated to
 *          the pure Solar_Template\Media\ImageOptimizationStatus. An attachment is marked optimized
 *          by recording a signature (file size + modification time) in
 *          `_solar_template_optimized_signature`: a plain boolean flag alone could not tell an
 *          attachment that was never processed apart from one whose underlying file was silently
 *          replaced since (e.g. a manual re-upload to the same attachment ID) — this scanner's own
 *          scan step (see Solar_Template\Admin\MediaOptimizationController) re-verifies that
 *          signature against the real file and resets it when it no longer matches, so a later
 *          "optimize all" run picks that image back up.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Media;

use Solar_Template\Contracts\ImageOptimizerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queries the media library and reads/writes each image's optimization signature.
 */
final class MediaLibraryScanner {

	public const SIGNATURE_META_KEY = '_solar_template_optimized_signature';

	/**
	 * Total number of image attachments in the media library.
	 *
	 * @return int
	 */
	public static function total_image_count(): int {
		$query = new \WP_Query( self::base_query_args() );

		return (int) $query->found_posts;
	}

	/**
	 * Number of image attachments never yet recorded as optimized (a stale-but-flagged attachment
	 * whose file was replaced after the last scan is only caught by self::scan_attachment(), not
	 * counted here until a scan corrects it).
	 *
	 * @return int
	 */
	public static function unoptimized_count(): int {
		$query = new \WP_Query(
			array_merge(
				self::base_query_args(),
				array(
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- a small, capability-gated admin screen's own count, not a front-end query.
						array(
							'key'     => self::SIGNATURE_META_KEY,
							'compare' => 'NOT EXISTS',
						),
					),
				)
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * The attachment ID at position $index among every image attachment, ordered by ID — used by
	 * the scan step to walk the whole library one image at a time.
	 *
	 * @param int $index Zero-based position.
	 * @return int|null
	 */
	public static function image_id_at( int $index ): ?int {
		$ids = get_posts(
			array_merge(
				self::base_query_args(),
				array(
					'fields' => 'ids',
					'offset' => $index,
				)
			)
		);

		return $ids[0] ?? null;
	}

	/**
	 * The first image attachment never yet recorded as optimized — used by the "optimize all" step,
	 * which always re-queries rather than relying on a fixed offset, since each successful
	 * optimization removes that attachment from this same result set.
	 *
	 * @return int|null
	 */
	public static function next_unoptimized_attachment_id(): ?int {
		$ids = get_posts(
			array_merge(
				self::base_query_args(),
				array(
					'fields'     => 'ids',
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => self::SIGNATURE_META_KEY,
							'compare' => 'NOT EXISTS',
						),
					),
				)
			)
		);

		return $ids[0] ?? null;
	}

	/**
	 * Whether $attachment_id's real, current file still matches its last recorded signature.
	 *
	 * @param int $attachment_id Attachment post ID.
	 * @return bool
	 */
	public static function is_attachment_optimized( int $attachment_id ): bool {
		$file_path = get_attached_file( $attachment_id );

		if ( false === $file_path || ! file_exists( $file_path ) ) {
			return false;
		}

		$stored_signature = get_post_meta( $attachment_id, self::SIGNATURE_META_KEY, true );

		return ImageOptimizationStatus::is_optimized(
			'' === $stored_signature ? null : $stored_signature,
			(int) filesize( $file_path ),
			(int) filemtime( $file_path )
		);
	}

	/**
	 * Verifies $attachment_id against its real current file, correcting its stored signature when
	 * it is stale (the file was replaced since the last time it was marked optimized) so it is
	 * picked up again by self::next_unoptimized_attachment_id()/self::unoptimized_count().
	 *
	 * @param int $attachment_id Attachment post ID.
	 * @return bool Whether the attachment is (still) considered optimized after this verification.
	 */
	public static function scan_attachment( int $attachment_id ): bool {
		if ( self::is_attachment_optimized( $attachment_id ) ) {
			return true;
		}

		if ( '' !== get_post_meta( $attachment_id, self::SIGNATURE_META_KEY, true ) ) {
			delete_post_meta( $attachment_id, self::SIGNATURE_META_KEY );
		}

		return false;
	}

	/**
	 * Runs $optimizer against $attachment_id's real file, recording its new signature on success.
	 *
	 * @param int                     $attachment_id Attachment post ID.
	 * @param ImageOptimizerInterface $optimizer     Compression backend to run.
	 * @return array{success: bool, original_size: int, optimized_size: int, message?: string}
	 */
	public static function optimize_attachment( int $attachment_id, ImageOptimizerInterface $optimizer ): array {
		$file_path = get_attached_file( $attachment_id );

		if ( false === $file_path || ! file_exists( $file_path ) ) {
			// Recorded with an obviously-invalid signature anyway (never matches a real file's
			// "{size}:{mtime}") — an attachment record pointing at a file missing from disk can
			// never be optimized, so it must still leave the "next unoptimized" query, or "optimize
			// all" would retry it forever.
			update_post_meta( $attachment_id, self::SIGNATURE_META_KEY, 'missing-file' );

			return array(
				'success'        => false,
				'original_size'  => 0,
				'optimized_size' => 0,
				'message'        => __( 'File not found.', 'solar-template' ),
			);
		}

		$result = $optimizer->optimize( $file_path );

		// The signature is recorded whether or not compression actually shrank the file (a
		// genuine "already optimal"/"unsupported format" outcome from the optimizer itself is
		// `success => true` with no size change) — and even on a hard failure, so a file GD can
		// never successfully process is not retried on every single future "optimize all" run.
		clearstatcache( true, $file_path );
		update_post_meta(
			$attachment_id,
			self::SIGNATURE_META_KEY,
			ImageOptimizationStatus::signature( (int) filesize( $file_path ), (int) filemtime( $file_path ) )
		);

		return $result;
	}

	/**
	 * Shared `WP_Query`/`get_posts()` arguments selecting every image attachment, ordered by ID.
	 *
	 * @return array<string, mixed>
	 */
	private static function base_query_args(): array {
		return array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => false,
		);
	}
}
