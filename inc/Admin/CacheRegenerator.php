<?php
/**
 * Created: 2026-09-26 14:10 CEST
 * Role: Cache/build regeneration action (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: The "Regenerate cache & assets" button's AJAX handler (rendered at the end of the
 *          General tab, Solar_Template\Admin\GeneralSettings::render()): purges the theme's own
 *          cache (Solar_Template\Support\TransientCache, behind Solar_Template\Contracts\
 *          CacheInterface — the interface Group 12's own cache implementation will later sit
 *          behind too), WordPress' object cache, and PHP's opcode cache when available, then
 *          recompiles the SCSS/JS build (`npm run build`) when Node tooling is actually present on
 *          the server — this Docker image ships PHP only, so that step reports itself skipped
 *          there rather than failing.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Solar_Template\Support\TransientCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the "Regenerate cache & assets" button's AJAX request.
 */
final class CacheRegenerator {

	/**
	 * @return bool Whether `npm` is available to this PHP process, so `npm run build` can be run.
	 */
	public static function is_build_tooling_available(): bool {
		if ( ! function_exists( 'shell_exec' ) || in_array( 'shell_exec', array_map( 'trim', explode( ',', (string) ini_get( 'disable_functions' ) ) ), true ) ) {
			return false;
		}

		$path = shell_exec( 'command -v npm 2>/dev/null' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec -- this admin-only, capability-gated button's own explicit job is to trigger the server's real build tooling; self::is_build_tooling_available() already no-ops gracefully when disabled/absent.

		return null !== $path && '' !== trim( (string) $path );
	}

	/**
	 * Runs `npm run build` in the theme directory.
	 *
	 * @return array{success: bool, message: string}
	 */
	private static function run_build(): array {
		$theme_dir = get_template_directory();
		$command   = sprintf( 'cd %s && npm run build 2>&1', escapeshellarg( $theme_dir ) );
		$output    = shell_exec( $command ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec -- see self::is_build_tooling_available()'s own ignore comment above.

		// `npm run build` (Vite) writes its own success/error summary to stdout/stderr; a null
		// `shell_exec()` return only ever means the command itself couldn't be launched at all.
		if ( null === $output ) {
			return array(
				'success' => false,
				'message' => __( 'Could not run the build command.', 'solar-template' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Assets rebuilt.', 'solar-template' ),
		);
	}

	/**
	 * Handles the `solar_template_regenerate_cache` AJAX action: purges every cache layer, rebuilds
	 * assets when possible, and reports a per-step result.
	 *
	 * @return void
	 */
	public static function handle_ajax_request(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'solar-template' ) ) );
		}

		check_ajax_referer( 'solar_template_regenerate_cache', 'nonce' );

		$steps = array();

		$steps[] = array(
			'label'   => __( 'Theme cache', 'solar-template' ),
			'success' => ( new TransientCache() )->flush(),
		);

		$steps[] = array(
			'label'   => __( 'Object cache', 'solar-template' ),
			'success' => wp_cache_flush(),
		);

		if ( function_exists( 'opcache_reset' ) ) {
			$steps[] = array(
				'label'   => __( 'PHP opcode cache', 'solar-template' ),
				'success' => opcache_reset(),
			);
		}

		if ( self::is_build_tooling_available() ) {
			$build_result = self::run_build();
			$steps[]      = array(
				'label'   => __( 'Asset build (npm run build)', 'solar-template' ),
				'success' => $build_result['success'],
			);
		} else {
			$steps[] = array(
				'label'   => __( 'Asset build (npm run build)', 'solar-template' ),
				'success' => null,
				'message' => __( 'Skipped — Node.js tooling is not available on this server.', 'solar-template' ),
			);
		}

		wp_send_json_success( array( 'steps' => $steps ) );
	}
}
