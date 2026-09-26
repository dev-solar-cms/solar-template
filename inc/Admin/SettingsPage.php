<?php
/**
 * Created: 2026-09-26 09:10 CEST
 * Role: Theme settings admin page (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Register the "Solar Template" top-level admin menu page and render its chrome — header,
 *          save feedback notice, and a vertical tab layout (180px nav + content) — delegating each
 *          tab's own fields/sanitization to its Solar_Template\Admin\SettingsTabInterface
 *          implementation. Handles the shared save request (nonce, capability, dispatch to the
 *          submitted tab's own sanitize()) for every tab, so no tab class duplicates that plumbing.
 *          Built directly in the theme via the WordPress Settings API primitives
 *          (`register_setting()`/`add_settings_section()`/`add_settings_field()` — see
 *          self::register_settings_api_metadata()), not a separate plugin (DECISIONS.md §1).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders and handles the theme's settings admin page.
 */
final class SettingsPage {

	public const MENU_SLUG    = 'solar-template';
	public const NONCE_ACTION = 'solar_template_settings_save';

	/**
	 * Every registered settings tab, in display order.
	 *
	 * @return array<int, class-string<SettingsTabInterface>>
	 */
	public static function tabs(): array {
		return array(
			GeneralSettings::class,
			AppearanceSettings::class,
			HeaderSettings::class,
			FooterSettings::class,
		);
	}

	/**
	 * Registers the top-level "Solar Template" admin menu page.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_menu_page(
			__( 'Solar Template', 'solar-template' ),
			__( 'Solar Template', 'solar-template' ),
			'manage_options',
			self::MENU_SLUG,
			array( self::class, 'render' ),
			'dashicons-admin-customizer',
			59
		);
	}

	/**
	 * Registers every tab's fields with the WordPress Settings API
	 * (`register_setting()`/`add_settings_section()`/`add_settings_field()`), so this page follows
	 * the same core convention any other WordPress options screen does. Actual persistence goes
	 * through Solar_Template\Admin\SettingsRepository instead of `wp_options` (see
	 * self::maybe_handle_save()) — the theme's settings already live in a dedicated table since an
	 * earlier step, seeded in anticipation of this screen.
	 *
	 * @return void
	 */
	public static function register_settings_api_metadata(): void {
		foreach ( self::tabs() as $tab_class ) {
			$option_group = 'solar_template_' . $tab_class::slug();

			register_setting( $option_group, $option_group );
			add_settings_section( $option_group, $tab_class::label(), '__return_false', $option_group );

			foreach ( $tab_class::defaults() as $field_key => $default_value ) {
				add_settings_field( $field_key, $field_key, '__return_false', $option_group, $option_group );
			}
		}
	}

	/**
	 * Enqueues WordPress' native media uploader (`wp.media`) on this settings page only, for the
	 * General tab's logo/favicon fields.
	 *
	 * @param string $hook_suffix Current admin page's hook suffix.
	 * @return void
	 */
	public static function enqueue_media_uploader( string $hook_suffix ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- mirrors the admin_enqueue_scripts hook signature; matched via $_GET['page'] instead, see below.
		if ( ! isset( $_GET['page'] ) || self::MENU_SLUG !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page identifier, not a state-changing request.
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * Handles the settings form's POST submission: verifies the nonce/capability, sanitizes the
	 * submitted tab's own fields, persists them, and redirects back with a `?updated=1` flag.
	 * Silently does nothing for any other request.
	 *
	 * @return void
	 */
	public static function maybe_handle_save(): void {
		if ( ! isset( $_POST['solar_template_settings_save'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to change these settings.', 'solar-template' ) );
		}

		check_admin_referer( self::NONCE_ACTION );

		$tab_slug = isset( $_POST['solar_template_tab'] ) ? sanitize_key( wp_unslash( $_POST['solar_template_tab'] ) ) : '';
		$raw      = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- wp_unslash() applied here, each tab's own sanitize() validates/escapes every field it reads.

		foreach ( self::tabs() as $tab_class ) {
			if ( $tab_class::slug() !== $tab_slug ) {
				continue;
			}

			SettingsRepository::set_many( $tab_slug, $tab_class::sanitize( $raw ) );
			break;
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::MENU_SLUG,
					'tab'     => $tab_slug,
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Renders the settings page: header, save feedback, and the active tab's fields inside the
	 * vertical tab layout.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs        = self::tabs();
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector, not a state-changing request.
		$active_tab  = self::resolve_active_tab( $tabs, $current_tab );

		self::render_styles();
		?>
		<div class="wrap solar-template-settings">
			<div class="solar-template-settings__header">
				<div class="solar-template-settings__brand">
					<span class="solar-template-settings__logo"><?php echo esc_html__( 'ST', 'solar-template' ); ?></span>
					<div>
						<h1><?php esc_html_e( 'Solar Template', 'solar-template' ); ?></h1>
						<p><?php esc_html_e( 'WooCommerce theme · Full configuration', 'solar-template' ); ?></p>
					</div>
				</div>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'View site', 'solar-template' ); ?> →
				</a>
			</div>

			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request. ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'solar-template' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="solar-template-settings__layout">
				<nav class="solar-template-settings__nav">
					<?php
					foreach ( $tabs as $tab_class ) :
						$tab_url = add_query_arg(
							array(
								'page' => self::MENU_SLUG,
								'tab'  => $tab_class::slug(),
							),
							admin_url( 'admin.php' )
						);
						?>
						<a
							class="solar-template-settings__nav-item<?php echo $tab_class === $active_tab ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( $tab_url ); ?>"
						>
							<span class="dashicons <?php echo esc_attr( $tab_class::icon() ); ?>"></span>
							<?php echo esc_html( $tab_class::label() ); ?>
						</a>
					<?php endforeach; ?>
				</nav>

				<div class="solar-template-settings__content">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ); ?>">
						<?php wp_nonce_field( self::NONCE_ACTION ); ?>
						<input type="hidden" name="solar_template_tab" value="<?php echo esc_attr( $active_tab::slug() ); ?>" />

						<div class="solar-template-settings__content-header">
							<h2><?php echo esc_html( $active_tab::label() ); ?></h2>
							<button type="submit" name="solar_template_settings_save" value="1" class="button button-primary">
								<?php esc_html_e( 'Save changes', 'solar-template' ); ?>
							</button>
						</div>

						<table class="form-table" role="presentation">
							<tbody>
								<?php $active_tab::render( SettingsRepository::get_many( self::prefixed_defaults( $active_tab ) ) ); ?>
							</tbody>
						</table>

						<p class="submit">
							<button type="submit" name="solar_template_settings_save" value="1" class="button button-primary">
								<?php esc_html_e( 'Save changes', 'solar-template' ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Resolves which registered tab class matches $requested_slug, falling back to the first
	 * registered tab when it matches none (an unknown/missing `?tab=` value never fatals).
	 *
	 * @param array<int, class-string<SettingsTabInterface>> $tabs            Every registered tab.
	 * @param string                                          $requested_slug Slug from the current request.
	 * @return class-string<SettingsTabInterface>
	 */
	private static function resolve_active_tab( array $tabs, string $requested_slug ): string {
		foreach ( $tabs as $tab_class ) {
			if ( $tab_class::slug() === $requested_slug ) {
				return $tab_class;
			}
		}

		return $tabs[0];
	}

	/**
	 * Builds the `{tab}.{field}` => default map SettingsRepository::get_many() expects, for the
	 * given tab class.
	 *
	 * @param class-string<SettingsTabInterface> $tab_class Tab to build defaults for.
	 * @return array<string, string>
	 */
	private static function prefixed_defaults( string $tab_class ): array {
		$prefixed = array();

		foreach ( $tab_class::defaults() as $field_key => $default_value ) {
			$prefixed[ "{$tab_class::slug()}.{$field_key}" ] = $default_value;
		}

		return $prefixed;
	}

	/**
	 * Echoes the settings page's own layout styles. Kept inline (no separate compiled admin
	 * stylesheet/build step for a single admin screen) and only on this page — see the
	 * `admin_print_styles-{$hook}` guard in Solar_Template\Theme::boot().
	 *
	 * @return void
	 */
	private static function render_styles(): void {
		?>
		<style>
			.solar-template-settings__header { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 16px 0 20px; }
			.solar-template-settings__brand { display: flex; align-items: center; gap: 14px; }
			.solar-template-settings__logo { width: 40px; height: 40px; background: #0d0d0d; color: #c9a96e; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; flex-shrink: 0; }
			.solar-template-settings__brand h1 { font-size: 22px; margin: 0 0 2px; }
			.solar-template-settings__brand p { font-size: 12px; color: #646970; margin: 0; }
			.solar-template-settings__layout { display: grid; grid-template-columns: 180px 1fr; gap: 0; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04); background: #fff; }
			.solar-template-settings__nav { background: #f6f7f7; border: 1px solid #c3c4c7; border-right: none; display: flex; flex-direction: column; padding: 8px 0; }
			.solar-template-settings__nav-item { display: flex; align-items: center; gap: 8px; padding: 9px 14px; font-size: 12.5px; color: #50575e; text-decoration: none; border-left: 3px solid transparent; }
			.solar-template-settings__nav-item:hover { color: #1d2327; background: rgba(0, 0, 0, 0.03); }
			.solar-template-settings__nav-item.is-active { color: #c9a96e; font-weight: 700; background: rgba(201, 169, 110, 0.08); border-left-color: #c9a96e; }
			.solar-template-settings__content { border: 1px solid #c3c4c7; padding: 0 20px 20px; }
			.solar-template-settings__content-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 0; border-bottom: 1px solid #e0e0e0; margin-bottom: 8px; }
			.solar-template-settings__content-header h2 { margin: 0; font-size: 16px; }
			.solar-template-settings__swatches { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
			.solar-template-settings__swatch { width: 28px; height: 28px; border-radius: 3px; cursor: pointer; border: 2px solid transparent; padding: 0; }
			.solar-template-settings__swatch:hover, .solar-template-settings__swatch:focus-visible { border-color: #2271b1; }
			.solar-template-settings__color-row { display: flex; gap: 10px; align-items: center; }
			.solar-template-settings__radio-group { display: flex; gap: 16px; flex-wrap: wrap; }
			.solar-template-settings__radio-group label { font-weight: 400; display: flex; align-items: center; gap: 6px; }
			.solar-template-settings__checkbox-group { display: flex; flex-direction: column; gap: 7px; }
			.solar-template-settings__checkbox-group label { font-weight: 400; display: flex; align-items: center; gap: 7px; }
			.solar-template-settings__layout-option { display: flex; align-items: center; gap: 10px; border: 1px solid #c3c4c7; border-radius: 3px; padding: 10px 14px; font-weight: 400; margin-bottom: 8px; max-width: 480px; }
			.solar-template-settings__layout-option.is-selected { border-color: #2271b1; background: #ecf5fd; }
			.solar-template-settings__social-row { display: flex; gap: 8px; align-items: center; max-width: 420px; margin-bottom: 8px; }
			.solar-template-settings__social-row span { width: 90px; font-size: 12px; color: #646970; flex-shrink: 0; }
			.solar-template-settings__toggle { appearance: none; width: 42px; height: 22px; border-radius: 11px; background: #d63638; position: relative; cursor: pointer; margin: 0 8px 0 0; vertical-align: middle; }
			.solar-template-settings__toggle:checked { background: #2271b1; }
			.solar-template-settings__toggle::before { content: ''; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: left 0.15s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3); }
			.solar-template-settings__toggle:checked::before { left: 22px; }
		</style>
		<script>
			document.addEventListener('click', function (event) {
				var swatch = event.target.closest('.solar-template-settings__swatch');
				if (swatch) {
					var target = document.getElementById(swatch.dataset.target);
					if (target) {
						target.value = swatch.dataset.color;
					}
					return;
				}

				var mediaButton = event.target.closest('.solar-template-media-button');
				if (mediaButton && window.wp && window.wp.media) {
					event.preventDefault();
					var fieldId = 'st-' + mediaButton.dataset.target;
					var frame = window.wp.media({ multiple: false });
					frame.on('select', function () {
						var attachment = frame.state().get('selection').first().toJSON();
						document.getElementById(fieldId).value = attachment.id;
						var preview = mediaButton.closest('.solar-template-settings__color-row').querySelector('.solar-template-media-preview');
						preview.innerHTML = '<img src="' + attachment.url + '" alt="" style="max-width:100%;max-height:100%;" />';
					});
					frame.open();
					return;
				}

				var removeButton = event.target.closest('.solar-template-media-remove');
				if (removeButton) {
					event.preventDefault();
					var removeFieldId = 'st-' + removeButton.dataset.target;
					document.getElementById(removeFieldId).value = '0';
					var removePreview = removeButton.closest('.solar-template-settings__color-row').querySelector('.solar-template-media-preview');
					removePreview.innerHTML = '<span style="font-size:10px;color:#646970;"><?php echo esc_js( __( 'No file', 'solar-template' ) ); ?></span>';
				}
			});
		</script>
		<?php
	}
}
