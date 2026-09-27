<?php
/**
 * Created: 2026-09-27 10:20 CEST
 * Role: "Translation Editor" admin page controller (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the section DESIGN_INTEGRATION.md's Internationalisation brief asks for: every
 *          translatable string the theme's code registers, listed and directly editable per
 *          language from the admin — not just a `.po`/`.mo` import — with placeholders (`%s`/`%d`/
 *          `%1$s`...) shown explicitly and singular/plural edited separately when a string uses
 *          `_n()`. Saving writes straight to Solar_Template\I18n\Translator and recompiles that
 *          language's `.mo`, so the change is live immediately. The actual filtering/pagination
 *          rules live in the pure Solar_Template\Admin\TranslationCatalog; this class only wires
 *          them to the real WordPress request/response.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Solar_Template\I18n\Translator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the "Translation Editor" admin page and handles its save action.
 */
final class TranslationsEditorController {

	public const MENU_SLUG   = 'solar-template-translation-editor';
	public const SAVE_ACTION = 'solar_template_save_translations';
	private const PER_PAGE   = 50;

	/**
	 * Registers the "Translation Editor" submenu page.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			SettingsPage::MENU_SLUG,
			__( 'Translation Editor', 'solar-template' ),
			__( 'Translation Editor', 'solar-template' ),
			'manage_options',
			self::MENU_SLUG,
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Renders the "Translation Editor" admin page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$languages = Translator::instance()->get_languages();
		$locale    = self::requested_locale( $languages );
		$search    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only search/pagination, not a state-changing request.
		$page      = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$current_strings = array() === $languages ? array() : Translator::instance()->get_strings( $locale );
		$result          = TranslationCatalog::build_rows( TranslationCatalog::all_keys(), $current_strings, $search, $page, self::PER_PAGE );

		self::render_styles();
		?>
		<div class="wrap solar-template-translation-editor">
			<h1><?php esc_html_e( 'Translation Editor', 'solar-template' ); ?></h1>

			<?php self::render_feedback(); ?>

			<?php if ( empty( $languages ) ) : ?>
				<p><?php esc_html_e( 'Add a language first, from the Languages page, to start translating.', 'solar-template' ); ?></p>
				<?php return; ?>
			<?php endif; ?>

			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="solar-template-translation-editor__filters">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>" />
				<select name="language" onchange="this.form.submit()">
					<?php foreach ( $languages as $code => $language ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $locale, $code ); ?>>
							<?php echo esc_html( $language['flag'] . ' ' . $language['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search a string…', 'solar-template' ); ?>" />
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'solar-template' ); ?></button>
			</form>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::SAVE_ACTION ); ?>" />
				<input type="hidden" name="language" value="<?php echo esc_attr( $locale ); ?>" />
				<input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>" />
				<input type="hidden" name="paged" value="<?php echo esc_attr( (string) $result['page'] ); ?>" />
				<?php wp_nonce_field( self::SAVE_ACTION ); ?>

				<table class="widefat striped solar-template-translation-editor__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Original string', 'solar-template' ); ?></th>
							<th><?php esc_html_e( 'Translation', 'solar-template' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['rows'] as $index => $row ) : ?>
							<tr>
								<td>
									<?php echo wp_kses_post( self::highlight_placeholders( $row['key'] ) ); ?>
									<?php if ( ! empty( $row['placeholders'] ) ) : ?>
										<p class="description">
											<?php
											echo esc_html(
												sprintf(
													/* translators: %s: comma-separated list of placeholders, e.g. "%s, %d". */
													__( 'Placeholders: %s', 'solar-template' ),
													implode( ', ', $row['placeholders'] )
												)
											);
											?>
										</p>
									<?php endif; ?>
								</td>
								<td>
									<input type="hidden" name="rows[<?php echo esc_attr( (string) $index ); ?>][key]" value="<?php echo esc_attr( $row['key'] ); ?>" />
									<label class="screen-reader-text" for="st-singular-<?php echo esc_attr( (string) $index ); ?>">
										<?php esc_html_e( 'Singular', 'solar-template' ); ?>
									</label>
									<textarea id="st-singular-<?php echo esc_attr( (string) $index ); ?>" name="rows[<?php echo esc_attr( (string) $index ); ?>][singular]" rows="2" class="large-text"><?php echo esc_textarea( $row['singular'] ); ?></textarea>
									<?php if ( $row['has_plural'] ) : ?>
										<label class="screen-reader-text" for="st-plural-<?php echo esc_attr( (string) $index ); ?>">
											<?php esc_html_e( 'Plural', 'solar-template' ); ?>
										</label>
										<p class="description"><?php esc_html_e( 'Plural form', 'solar-template' ); ?></p>
										<textarea id="st-plural-<?php echo esc_attr( (string) $index ); ?>" name="rows[<?php echo esc_attr( (string) $index ); ?>][plural]" rows="2" class="large-text"><?php echo esc_textarea( $row['plural'] ); ?></textarea>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p class="solar-template-translation-editor__save">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save translations', 'solar-template' ); ?></button>
				</p>
			</form>

			<?php self::render_pagination( $result['page'], $result['total_pages'], $locale, $search ); ?>
		</div>
		<?php
	}

	/**
	 * Handles the "Save translations" form's submission
	 * (`admin-post.php?action=solar_template_save_translations`).
	 *
	 * @return void
	 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'solar-template' ) );
		}

		check_admin_referer( self::SAVE_ACTION );

		$locale    = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
		$languages = Translator::instance()->get_languages();
		$status    = 'error';

		if ( isset( $languages[ $locale ] ) ) {
			self::save_rows( $locale, isset( $_POST['rows'] ) && is_array( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- wp_unslash() applied here, self::save_rows() sanitizes every field it reads.
			Translator::instance()->compile( $locale );
			$status = 'saved';
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                        => self::MENU_SLUG,
					'language'                    => $locale,
					's'                           => isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '',
					'paged'                       => isset( $_POST['paged'] ) ? max( 1, (int) $_POST['paged'] ) : 1,
					'solar_template_translations' => $status,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Registers every submitted row's translation for $locale — guarding against a key that isn't
	 * part of the real catalog (a tampered request), and only reading a plural value for a key that
	 * actually has one.
	 *
	 * @param string                                              $locale Locale code to save into.
	 * @param array<int, array{key?: string, singular?: string, plural?: string}> $rows Submitted rows.
	 * @return void
	 */
	private static function save_rows( string $locale, array $rows ): void {
		$known_keys = TranslationCatalog::all_keys();
		$translator = Translator::instance();

		foreach ( $rows as $row ) {
			$key = isset( $row['key'] ) ? (string) $row['key'] : '';

			if ( '' === $key || ! in_array( $key, $known_keys, true ) ) {
				continue;
			}

			$singular = isset( $row['singular'] ) ? wp_kses_post( (string) $row['singular'] ) : '';
			$plural   = null;

			if ( TranslationCatalog::has_plural( $key ) && isset( $row['plural'] ) ) {
				$plural = wp_kses_post( (string) $row['plural'] );
			}

			$translator->set_string( $locale, $key, $singular, $plural );
		}
	}

	/**
	 * Resolves the requested `?language=` against the registered languages, falling back to the
	 * default language, then to the first registered one.
	 *
	 * @param array<string, array{label: string, flag: string, is_active: bool, is_default: bool}> $languages
	 *        Every registered language (Translator::get_languages()).
	 * @return string
	 */
	private static function requested_locale( array $languages ): string {
		$requested = isset( $_GET['language'] ) ? sanitize_text_field( wp_unslash( $_GET['language'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only language selector, not a state-changing request.

		if ( isset( $languages[ $requested ] ) ) {
			return $requested;
		}

		foreach ( $languages as $code => $language ) {
			if ( ! empty( $language['is_default'] ) ) {
				return $code;
			}
		}

		$codes = array_keys( $languages );

		return $codes[0] ?? '';
	}

	/**
	 * Wraps every printf-style placeholder in $text with `<code>` so it stands out in the "Original
	 * string" column, after escaping the rest of the text.
	 *
	 * @param string $text Original/English string.
	 * @return string HTML fragment.
	 */
	private static function highlight_placeholders( string $text ): string {
		return preg_replace( '/(%(?:\d+\$)?[sd])/', '<code>$1</code>', esc_html( $text ) );
	}

	/**
	 * Echoes the feedback notice matching the current `?solar_template_translations=` flag, if any.
	 *
	 * @return void
	 */
	private static function render_feedback(): void {
		if ( ! isset( $_GET['solar_template_translations'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request.
			return;
		}

		$status = sanitize_key( wp_unslash( $_GET['solar_template_translations'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'saved' !== $status && 'error' !== $status ) {
			return;
		}

		$type    = 'saved' === $status ? 'success' : 'error';
		$message = 'saved' === $status
			? __( 'Translations saved.', 'solar-template' )
			: __( 'Something went wrong. Please try again.', 'solar-template' );
		?>
		<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Echoes numbered pagination links preserving the current language/search.
	 *
	 * @param int    $page        Current page.
	 * @param int    $total_pages Total number of pages.
	 * @param string $locale      Currently selected language code.
	 * @param string $search      Currently active search term.
	 * @return void
	 */
	private static function render_pagination( int $page, int $total_pages, string $locale, string $search ): void {
		if ( $total_pages < 2 ) {
			return;
		}
		?>
		<div class="solar-template-translation-editor__pagination">
			<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
				<?php
				$url = add_query_arg(
					array(
						'page'     => self::MENU_SLUG,
						'language' => $locale,
						's'        => $search,
						'paged'    => $i,
					),
					admin_url( 'admin.php' )
				);
				?>
				<a class="<?php echo $i === $page ? 'current' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( (string) $i ); ?></a>
			<?php endfor; ?>
		</div>
		<?php
	}

	/**
	 * Echoes this page's own layout styles.
	 *
	 * @return void
	 */
	private static function render_styles(): void {
		?>
		<style>
			.solar-template-translation-editor__filters { display: flex; gap: 8px; align-items: center; margin: 16px 0; }
			.solar-template-translation-editor__table { max-width: 1000px; }
			.solar-template-translation-editor__table td { vertical-align: top; padding: 10px; }
			.solar-template-translation-editor__table textarea { width: 100%; max-width: 480px; }
			.solar-template-translation-editor__save { margin-top: 12px; }
			.solar-template-translation-editor__pagination { display: flex; gap: 6px; margin-top: 16px; }
			.solar-template-translation-editor__pagination a { padding: 4px 9px; border: 1px solid #c3c4c7; border-radius: 3px; text-decoration: none; }
			.solar-template-translation-editor__pagination a.current { background: #2271b1; color: #fff; border-color: #2271b1; }
		</style>
		<?php
	}
}
