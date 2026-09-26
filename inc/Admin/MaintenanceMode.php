<?php
/**
 * Created: 2026-09-26 09:35 CEST
 * Role: Front-end maintenance mode gate (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: When the General settings tab's "Maintenance mode" toggle is on, block the front end for
 *          anyone who can't manage the site, showing a minimal maintenance page with a real 503
 *          status instead of the real content. Signed-in administrators keep browsing the real site
 *          normally, so they can verify their changes while it's on.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gates the front end while maintenance mode is enabled.
 */
final class MaintenanceMode {

	/**
	 * Hooked on `template_redirect`. Renders the maintenance page and stops execution when
	 * maintenance mode applies to the current request.
	 *
	 * @return void
	 */
	public static function maybe_block_request(): void {
		if ( ! self::applies_to_current_request() ) {
			return;
		}

		self::render();
		exit;
	}

	/**
	 * Whether the current request should be blocked: maintenance mode is on, the visitor can't
	 * manage the site, and this isn't an admin/login/REST/cron request (those never go through
	 * `template_redirect` for the front end anyway, but this keeps the check explicit and safe if
	 * ever hooked elsewhere).
	 *
	 * @return bool
	 */
	public static function applies_to_current_request(): bool {
		if ( ! GeneralSettings::is_maintenance_mode() ) {
			return false;
		}

		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}

		return ! current_user_can( 'manage_options' );
	}

	/**
	 * Renders the maintenance page.
	 *
	 * @return void
	 */
	private static function render(): void {
		status_header( 503 );
		nocache_headers();
		header( 'Retry-After: 3600' );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<title><?php echo esc_html( GeneralSettings::shop_name() ); ?></title>
			<style>
				body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #0d0d0d; color: #fff; font-family: -apple-system, BlinkMacSystemFont, sans-serif; text-align: center; padding: 24px; box-sizing: border-box; }
				h1 { font-size: clamp(24px, 4vw, 36px); margin: 0 0 12px; letter-spacing: -0.02em; }
				p { color: rgba(255, 255, 255, 0.5); font-size: 15px; max-width: 440px; margin: 0 auto; line-height: 1.7; }
				span { display: block; color: #c9a96e; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; margin-bottom: 14px; }
			</style>
		</head>
		<body>
			<div>
				<span><?php echo esc_html( GeneralSettings::shop_name() ); ?></span>
				<h1><?php esc_html_e( 'Site under maintenance', 'solar-template' ); ?></h1>
				<p><?php esc_html_e( "We're currently performing some updates. Please check back shortly.", 'solar-template' ); ?></p>
			</div>
		</body>
		</html>
		<?php
	}
}
