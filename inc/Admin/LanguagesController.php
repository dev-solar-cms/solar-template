<?php
/**
 * Created: 2026-09-27 09:15 CEST
 * Role: "Languages" admin page controller (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the theme's own "add a language" screen — a searchable list of the standard
 *          languages Solar_Template\I18n\LanguageCatalog already knows about, each with its classic
 *          locale code and flag — plus the list of languages already added, with a "set as default"
 *          and "remove" action for each (DECISIONS.md §9). Every read/write goes through
 *          Solar_Template\I18n\Translator, the same translator interface the rest of the theme's
 *          i18n system already uses — no language is ever hardcoded here.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Solar_Template\I18n\LanguageCatalog;
use Solar_Template\I18n\Translator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the "Languages" admin page and handles its add/set-default/remove actions.
 */
final class LanguagesController {

	public const ADD_ACTION         = 'solar_template_add_language';
	public const SET_DEFAULT_ACTION = 'solar_template_set_default_language';
	public const REMOVE_ACTION      = 'solar_template_remove_language';

	/**
	 * Renders the "Languages" admin page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$added     = Translator::instance()->get_languages();
		$available = self::available_to_add( LanguageCatalog::available(), $added );

		self::render_styles();
		?>
		<div class="wrap solar-template-languages">
			<h1><?php esc_html_e( 'Languages', 'solar-template' ); ?></h1>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . SettingsPage::MENU_SLUG . '&tab=' . TranslationsSettings::slug() ) ); ?>">
					&larr; <?php esc_html_e( 'Back to Translations settings', 'solar-template' ); ?>
				</a>
			</p>

			<?php self::render_feedback(); ?>

			<h2><?php esc_html_e( 'Installed languages', 'solar-template' ); ?></h2>
			<table class="widefat striped solar-template-languages__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Language', 'solar-template' ); ?></th>
						<th><?php esc_html_e( 'Code', 'solar-template' ); ?></th>
						<th><?php esc_html_e( 'Default', 'solar-template' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'solar-template' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $added as $code => $language ) : ?>
						<tr>
							<td><?php echo esc_html( $language['flag'] . ' ' . $language['label'] ); ?></td>
							<td><code><?php echo esc_html( $code ); ?></code></td>
							<td>
								<?php if ( $language['is_default'] ) : ?>
									<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
									<?php esc_html_e( 'Default', 'solar-template' ); ?>
								<?php endif; ?>
							</td>
							<td class="solar-template-languages__actions">
								<?php if ( ! $language['is_default'] ) : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="<?php echo esc_attr( self::SET_DEFAULT_ACTION ); ?>" />
										<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>" />
										<?php wp_nonce_field( self::SET_DEFAULT_ACTION ); ?>
										<button type="submit" class="button button-small"><?php esc_html_e( 'Set as default', 'solar-template' ); ?></button>
									</form>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="<?php echo esc_attr( self::REMOVE_ACTION ); ?>" />
										<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>" />
										<?php wp_nonce_field( self::REMOVE_ACTION ); ?>
										<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Remove', 'solar-template' ); ?></button>
									</form>
								<?php else : ?>
									<span class="description"><?php esc_html_e( 'Cannot remove the default language.', 'solar-template' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Add a language', 'solar-template' ); ?></h2>
			<p>
				<input
					type="search"
					id="solar-template-language-search"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Search a language…', 'solar-template' ); ?>"
					aria-controls="solar-template-language-list"
				/>
			</p>
			<ul class="solar-template-languages__available" id="solar-template-language-list">
				<?php foreach ( $available as $code => $language ) : ?>
					<li data-solar-language-label="<?php echo esc_attr( strtolower( $language['label'] . ' ' . $code ) ); ?>">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="<?php echo esc_attr( self::ADD_ACTION ); ?>" />
							<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>" />
							<?php wp_nonce_field( self::ADD_ACTION ); ?>
							<button type="submit" class="button">
								<?php echo esc_html( $language['flag'] . ' ' . $language['label'] . ' (' . $code . ')' ); ?>
								&mdash; <?php esc_html_e( 'Add', 'solar-template' ); ?>
							</button>
						</form>
					</li>
				<?php endforeach; ?>
				<?php if ( empty( $available ) ) : ?>
					<li><?php esc_html_e( 'Every standard language is already added.', 'solar-template' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Handles the "Add" form's submission (`admin-post.php?action=solar_template_add_language`):
	 * registers the chosen standard language and compiles an (initially empty) `.mo` for it, so it
	 * immediately shows up wherever active languages are listed.
	 *
	 * @return void
	 */
	public static function handle_add(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'solar-template' ) );
		}

		check_admin_referer( self::ADD_ACTION );

		$code      = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$available = LanguageCatalog::available();
		$already   = Translator::instance()->get_languages();
		$status    = 'error';

		if ( isset( $available[ $code ] ) && ! isset( $already[ $code ] ) ) {
			$translator = Translator::instance();
			$translator->add_language( $code, $available[ $code ]['label'], $available[ $code ]['flag'] );
			$translator->compile( $code );
			$status = 'added';
		}

		self::redirect( $status );
	}

	/**
	 * Handles the "Set as default" form's submission
	 * (`admin-post.php?action=solar_template_set_default_language`).
	 *
	 * @return void
	 */
	public static function handle_set_default(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'solar-template' ) );
		}

		check_admin_referer( self::SET_DEFAULT_ACTION );

		$code      = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$languages = Translator::instance()->get_languages();
		$status    = 'error';

		if ( isset( $languages[ $code ] ) ) {
			Translator::instance()->add_language( $code, $languages[ $code ]['label'], $languages[ $code ]['flag'], true );
			$status = 'default-updated';
		}

		self::redirect( $status );
	}

	/**
	 * Handles the "Remove" form's submission (`admin-post.php?action=solar_template_remove_language`).
	 * Refuses to remove the current default language — a new default must be chosen first.
	 *
	 * @return void
	 */
	public static function handle_remove(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'solar-template' ) );
		}

		check_admin_referer( self::REMOVE_ACTION );

		$code      = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$languages = Translator::instance()->get_languages();
		$status    = 'error';

		if ( isset( $languages[ $code ] ) && self::can_remove( $languages[ $code ] ) ) {
			Translator::instance()->remove_language( $code );

			$mo_path = get_template_directory() . "/languages/{$code}.mo";
			if ( file_exists( $mo_path ) ) {
				wp_delete_file( $mo_path );
			}

			$status = 'removed';
		}

		self::redirect( $status );
	}

	/**
	 * Pure: which standard languages can still be offered for adding — every one
	 * Solar_Template\I18n\LanguageCatalog knows about that isn't already registered. No WordPress
	 * dependency, so it's unit tested directly.
	 *
	 * @param array<string, array{label: string, flag: string, plural_rule: string}> $standard  Every
	 *        standard language (LanguageCatalog::available()).
	 * @param array<string, array{label: string, flag: string, is_active: bool, is_default: bool}> $installed
	 *        Every already-registered language (Translator::get_languages()).
	 * @return array<string, array{label: string, flag: string, plural_rule: string}>
	 */
	public static function available_to_add( array $standard, array $installed ): array {
		return array_diff_key( $standard, $installed );
	}

	/**
	 * Pure: whether a registered language can be removed — never the current default, since a new
	 * default must be chosen first. No WordPress dependency, so it's unit tested directly.
	 *
	 * @param array{label: string, flag: string, is_active: bool, is_default: bool} $language Registered
	 *        language entry, as returned by Translator::get_languages().
	 * @return bool
	 */
	public static function can_remove( array $language ): bool {
		return empty( $language['is_default'] );
	}

	/**
	 * Redirects back to the "Languages" page with a `?solar_template_language=` feedback flag.
	 *
	 * @param string $status One of `added`, `default-updated`, `removed`, `error`.
	 * @return void
	 */
	private static function redirect( string $status ): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                    => TranslationsSettings::LANGUAGES_MENU_SLUG,
					'solar_template_language' => $status,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Echoes the feedback notice matching the current `?solar_template_language=` flag, if any.
	 *
	 * @return void
	 */
	private static function render_feedback(): void {
		if ( ! isset( $_GET['solar_template_language'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.
			return;
		}

		$status   = sanitize_key( wp_unslash( $_GET['solar_template_language'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.
		$messages = array(
			'added'           => array( 'success', __( 'Language added.', 'solar-template' ) ),
			'default-updated' => array( 'success', __( 'Default language updated.', 'solar-template' ) ),
			'removed'         => array( 'success', __( 'Language removed.', 'solar-template' ) ),
			'error'           => array( 'error', __( 'Something went wrong. Please try again.', 'solar-template' ) ),
		);

		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $status ];
		?>
		<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Echoes this page's own layout styles/script (installed-languages table, and the vanilla-JS
	 * search filter over the "Add a language" list) — same inline convention as
	 * Solar_Template\Admin\SettingsPage::render_styles(), no separate compiled admin stylesheet for
	 * a couple of admin screens.
	 *
	 * @return void
	 */
	private static function render_styles(): void {
		?>
		<style>
			.solar-template-languages__table { max-width: 720px; margin-bottom: 24px; }
			.solar-template-languages__actions { display: flex; gap: 6px; align-items: center; }
			.solar-template-languages__actions form { display: inline; margin: 0; }
			.solar-template-languages__available { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 8px; max-width: 720px; }
			.solar-template-languages__available li form { margin: 0; }
		</style>
		<script>
			(function () {
				var search = document.getElementById('solar-template-language-search');
				var list = document.getElementById('solar-template-language-list');

				if (!search || !list) {
					return;
				}

				search.addEventListener('input', function () {
					var query = search.value.trim().toLowerCase();

					list.querySelectorAll('[data-solar-language-label]').forEach(function (item) {
						var matches = '' === query || item.dataset.solarLanguageLabel.indexOf(query) !== -1;
						item.hidden = !matches;
					});
				});
			})();
		</script>
		<?php
	}
}
