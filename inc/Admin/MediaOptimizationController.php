<?php
/**
 * Created: 2026-09-27 11:40 CEST
 * Role: "Image Optimization" admin page controller (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the admin screen DESIGN_INTEGRATION.md's "Non prévue" brief asks for — a count of
 *          not-yet-optimized media library images, a "Scan library" action verifying that count
 *          image by image with a progress bar, and an "Optimize images" action compressing every
 *          remaining one the same way — both driven entirely by sequential `fetch()` requests from
 *          the browser (never a single long-running PHP request) so the admin UI never blocks or
 *          times out on a large library. The actual query/meta logic lives in
 *          Solar_Template\Media\MediaLibraryScanner; the actual compression lives behind
 *          Solar_Template\Contracts\ImageOptimizerInterface (self::optimizer(), filterable) — this
 *          class only wires both to real WordPress AJAX requests.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Solar_Template\Contracts\ImageOptimizerInterface;
use Solar_Template\Media\GdImageOptimizer;
use Solar_Template\Media\MediaLibraryScanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the "Image Optimization" admin page and handles its scan/optimize AJAX requests.
 */
final class MediaOptimizationController {

	public const MENU_SLUG       = 'solar-template-media-optimization';
	public const NONCE_ACTION    = 'solar_template_media_optimization';
	public const SCAN_ACTION     = 'solar_template_media_scan_step';
	public const OPTIMIZE_ACTION = 'solar_template_media_optimize_step';

	/**
	 * Registers the "Image Optimization" submenu page.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			SettingsPage::MENU_SLUG,
			__( 'Image Optimization', 'solar-template' ),
			__( 'Image Optimization', 'solar-template' ),
			'manage_options',
			self::MENU_SLUG,
			array( self::class, 'render_page' )
		);
	}

	/**
	 * The compression backend used by self::handle_optimize_step(), filterable so a site can swap
	 * in a different Solar_Template\Contracts\ImageOptimizerInterface implementation (e.g. a paid
	 * third-party service) without touching this controller.
	 *
	 * @return ImageOptimizerInterface
	 */
	public static function optimizer(): ImageOptimizerInterface {
		$optimizer = apply_filters( 'solar_template_image_optimizer', new GdImageOptimizer() );

		return $optimizer instanceof ImageOptimizerInterface ? $optimizer : new GdImageOptimizer();
	}

	/**
	 * Renders the "Image Optimization" admin page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$total       = MediaLibraryScanner::total_image_count();
		$unoptimized = MediaLibraryScanner::unoptimized_count();

		self::render_styles();
		?>
		<div class="wrap solar-template-media-optimization">
			<h1><?php esc_html_e( 'Image Optimization', 'solar-template' ); ?></h1>
			<p>
				<?php esc_html_e( 'Scan the media library to verify which images are already optimized, then compress every remaining one — both run image by image in the background, without reloading this page.', 'solar-template' ); ?>
			</p>

			<div class="solar-template-media-optimization__stats" id="solar-template-media-stats">
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: number of not-yet-optimized images, 2: total number of images. */
							__( '%1$d of %2$d images are not optimized yet.', 'solar-template' ),
							$unoptimized,
							$total
						)
					);
					?>
				</p>
			</div>

			<div class="solar-template-media-optimization__panel">
				<h2><?php esc_html_e( 'Scan library', 'solar-template' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Re-checks every image against its own last known state, so an image replaced outside this screen is detected again.', 'solar-template' ); ?></p>
				<button type="button" class="button" id="solar-template-media-scan-start" data-total="<?php echo esc_attr( (string) $total ); ?>">
					<?php esc_html_e( 'Scan library', 'solar-template' ); ?>
				</button>
				<div class="solar-template-media-optimization__progress" id="solar-template-media-scan-progress" hidden>
					<div class="solar-template-media-optimization__bar"><span></span></div>
					<p class="solar-template-media-optimization__count"></p>
				</div>
			</div>

			<div class="solar-template-media-optimization__panel">
				<h2><?php esc_html_e( 'Optimize images', 'solar-template' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Compresses every not-yet-optimized image, one at a time, without a noticeable loss of visual quality.', 'solar-template' ); ?></p>
				<button type="button" class="button button-primary" id="solar-template-media-optimize-start">
					<?php esc_html_e( 'Optimize images', 'solar-template' ); ?>
				</button>
				<div class="solar-template-media-optimization__progress" id="solar-template-media-optimize-progress" hidden>
					<div class="solar-template-media-optimization__bar"><span></span></div>
					<p class="solar-template-media-optimization__count"></p>
				</div>
			</div>
		</div>
		<?php
		self::render_scripts();
	}

	/**
	 * Handles the `solar_template_media_scan_step` AJAX action: verifies one image (identified by
	 * its zero-based position among every image attachment) and reports whether it is optimized.
	 *
	 * @return void
	 */
	public static function handle_scan_step(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'solar-template' ) ) );
		}

		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$index = isset( $_POST['index'] ) ? max( 0, (int) $_POST['index'] ) : 0;
		$total = MediaLibraryScanner::total_image_count();

		$attachment_id = $index < $total ? MediaLibraryScanner::image_id_at( $index ) : null;

		if ( null === $attachment_id ) {
			wp_send_json_success(
				array(
					'done'              => true,
					'total'             => $total,
					'index'             => $index,
					'unoptimized_count' => MediaLibraryScanner::unoptimized_count(),
				)
			);
		}

		$is_optimized = MediaLibraryScanner::scan_attachment( $attachment_id );

		wp_send_json_success(
			array(
				'done'              => ( $index + 1 ) >= $total,
				'total'             => $total,
				'index'             => $index,
				'attachment_id'     => $attachment_id,
				'is_optimized'      => $is_optimized,
				'unoptimized_count' => MediaLibraryScanner::unoptimized_count(),
			)
		);
	}

	/**
	 * Handles the `solar_template_media_optimize_step` AJAX action: compresses the next
	 * not-yet-optimized image, if any.
	 *
	 * @return void
	 */
	public static function handle_optimize_step(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'solar-template' ) ) );
		}

		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$remaining_before = MediaLibraryScanner::unoptimized_count();
		$attachment_id    = 0 === $remaining_before ? null : MediaLibraryScanner::next_unoptimized_attachment_id();

		if ( null === $attachment_id ) {
			wp_send_json_success(
				array(
					'done'      => true,
					'remaining' => 0,
				)
			);
		}

		$result = MediaLibraryScanner::optimize_attachment( $attachment_id, self::optimizer() );

		wp_send_json_success(
			array(
				'done'           => false,
				'remaining'      => max( 0, $remaining_before - 1 ),
				'attachment_id'  => $attachment_id,
				'success'        => $result['success'],
				'original_size'  => $result['original_size'],
				'optimized_size' => $result['optimized_size'],
			)
		);
	}

	/**
	 * Echoes this page's own layout styles.
	 *
	 * @return void
	 */
	private static function render_styles(): void {
		?>
		<style>
			.solar-template-media-optimization__stats { font-size: 14px; margin: 16px 0; }
			.solar-template-media-optimization__panel { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 16px 20px; margin-bottom: 16px; max-width: 640px; }
			.solar-template-media-optimization__panel h2 { margin-top: 0; }
			.solar-template-media-optimization__progress { margin-top: 14px; }
			.solar-template-media-optimization__bar { background: #f0f0f1; border-radius: 999px; height: 10px; overflow: hidden; }
			.solar-template-media-optimization__bar span { display: block; height: 100%; width: 0; background: #c9a227; transition: width 0.2s ease; }
			.solar-template-media-optimization__count { font-size: 12.5px; color: #646970; margin: 6px 0 0; }
		</style>
		<?php
	}

	/**
	 * Echoes this page's own scan/optimize progress scripts.
	 *
	 * @return void
	 */
	private static function render_scripts(): void {
		$ajax_url = admin_url( 'admin-ajax.php' );
		$nonce    = wp_create_nonce( self::NONCE_ACTION );
		?>
		<script>
			(function () {
				var ajaxUrl = <?php echo wp_json_encode( $ajax_url ); ?>;
				var nonce = <?php echo wp_json_encode( $nonce ); ?>;
				var stats = document.getElementById('solar-template-media-stats');

				function updateStats(unoptimizedCount, total) {
					if (typeof unoptimizedCount !== 'number' || typeof total !== 'number') {
						return;
					}
					stats.innerHTML = '<p>' + unoptimizedCount + ' / ' + total + '</p>';
				}

				function runSequentialSteps(options) {
					var button = options.button;
					var progress = options.progress;
					var bar = progress.querySelector('.solar-template-media-optimization__bar span');
					var count = progress.querySelector('.solar-template-media-optimization__count');
					var maxSteps = options.maxSteps;
					var steps = 0;

					button.disabled = true;
					progress.hidden = false;
					bar.style.width = '0%';

					function step() {
						steps += 1;

						options.request().then(function (json) {
							if (!json.success) {
								button.disabled = false;
								count.textContent = (json.data && json.data.message) || <?php echo wp_json_encode( __( 'Something went wrong. Please try again.', 'solar-template' ) ); ?>;
								return;
							}

							options.onStep(json.data, bar, count);

							if (json.data.done || steps >= maxSteps) {
								button.disabled = false;
								options.onDone(json.data);
								return;
							}

							step();
						});
					}

					step();
				}

				var scanButton = document.getElementById('solar-template-media-scan-start');
				if (scanButton) {
					scanButton.addEventListener('click', function () {
						var total = parseInt(scanButton.dataset.total, 10) || 0;
						var index = 0;

						runSequentialSteps({
							button: scanButton,
							progress: document.getElementById('solar-template-media-scan-progress'),
							maxSteps: Math.max(total, 1),
							request: function () {
								var body = new URLSearchParams();
								body.set('action', <?php echo wp_json_encode( self::SCAN_ACTION ); ?>);
								body.set('nonce', nonce);
								body.set('index', String(index));
								index += 1;

								return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body }).then(function (response) {
									return response.json();
								});
							},
							onStep: function (data, bar, count) {
								var percent = data.total > 0 ? Math.round(((data.index + 1) / data.total) * 100) : 100;
								bar.style.width = percent + '%';
								count.textContent = (data.index + 1) + ' / ' + data.total;
								updateStats(data.unoptimized_count, data.total);
							},
							onDone: function (data) {
								updateStats(data.unoptimized_count, data.total);
							}
						});
					});
				}

				var optimizeButton = document.getElementById('solar-template-media-optimize-start');
				if (optimizeButton) {
					optimizeButton.addEventListener('click', function () {
						var total = null;
						var processed = 0;

						runSequentialSteps({
							button: optimizeButton,
							progress: document.getElementById('solar-template-media-optimize-progress'),
							maxSteps: 100000,
							request: function () {
								var body = new URLSearchParams();
								body.set('action', <?php echo wp_json_encode( self::OPTIMIZE_ACTION ); ?>);
								body.set('nonce', nonce);

								return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body }).then(function (response) {
									return response.json();
								});
							},
							onStep: function (data, bar, count) {
								if (null === total) {
									total = data.remaining + 1;
								}
								processed += 1;
								var percent = total > 0 ? Math.round((processed / total) * 100) : 100;
								bar.style.width = percent + '%';
								count.textContent = processed + ' / ' + total;
								updateStats(data.remaining, total);
							},
							onDone: function () {
								if (null !== total) {
									updateStats(0, total);
								}
							}
						});
					});
				}
			})();
		</script>
		<?php
	}
}
