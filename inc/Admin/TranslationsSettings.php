<?php
/**
 * Created: 2026-09-26 13:50 CEST
 * Role: "Translations" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Default language + automatic detection (real, applied on `determine_locale` for
 *          front-end visitors), entry points to the "Languages" menu (add-a-language screen and
 *          installed-languages listing — Solar_Template\Admin\LanguagesController) and the
 *          "Translation Editor" (Solar_Template\Admin\TranslationsEditorController), and a .po/.mo
 *          fallback: downloading each active language's already-compiled `.mo`, and importing a
 *          `.po`/`.mo` file into the theme's own translation catalog (Solar_Template\I18n\Translator)
 *          alongside that editor.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

use Gettext\Loader\MoLoader;
use Gettext\Loader\PoLoader;
use Solar_Template\I18n\Translator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Translations" settings tab.
 */
final class TranslationsSettings implements SettingsTabInterface {

	public const LANGUAGES_MENU_SLUG = 'solar-template-languages';

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'translations';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Translations', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-translation';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		return array(
			'default_language' => '',
			'auto_detect'      => '0',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$active_codes = array_keys( self::active_languages() );
		$default      = isset( $raw['default_language'] ) ? sanitize_text_field( (string) $raw['default_language'] ) : '';

		return array(
			'default_language' => in_array( $default, $active_codes, true ) ? $default : '',
			'auto_detect'      => ! empty( $raw['auto_detect'] ) ? '1' : '0',
		);
	}

	/**
	 * @return array<string, array{label: string, flag: string, is_active: bool, is_default: bool}>
	 *              Every registered language that is currently active.
	 */
	private static function active_languages(): array {
		return array_filter(
			Translator::instance()->get_languages(),
			static function ( array $language ): bool {
				return $language['is_active'];
			}
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		$languages     = self::active_languages();
		$languages_url = admin_url( 'admin.php?page=' . self::LANGUAGES_MENU_SLUG );
		?>
		<tr>
			<th><label for="st-default-language"><?php esc_html_e( 'Default language', 'solar-template' ); ?></label></th>
			<td>
				<select id="st-default-language" name="default_language">
					<option value=""><?php esc_html_e( "Use WordPress' own site language", 'solar-template' ); ?></option>
					<?php foreach ( $languages as $code => $language ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $values['translations.default_language'], $code ); ?>>
							<?php echo esc_html( $language['flag'] . ' ' . $language['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Automatic detection', 'solar-template' ); ?></th>
			<td>
				<label>
					<input type="checkbox" class="solar-template-settings__toggle" name="auto_detect" value="1" <?php checked( $values['translations.auto_detect'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
				<p class="description"><?php esc_html_e( "Serve visitors the language closest to their browser's own language, among the ones active below, before falling back to the default language.", 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Languages', 'solar-template' ); ?></th>
			<td>
				<p>
					<a class="button" href="<?php echo esc_url( $languages_url ); ?>"><?php esc_html_e( 'Manage languages →', 'solar-template' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . TranslationsEditorController::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Translate strings →', 'solar-template' ); ?></a>
				</p>
				<table class="solar-template-settings__lang-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Language', 'solar-template' ); ?></th>
							<th><?php esc_html_e( 'Compiled file', 'solar-template' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $languages as $code => $language ) : ?>
							<?php $mo_path = get_template_directory() . "/languages/{$code}.mo"; ?>
							<tr>
								<td><?php echo esc_html( $language['flag'] . ' ' . $language['label'] ); ?></td>
								<td>
									<?php if ( file_exists( $mo_path ) ) : ?>
										<a href="<?php echo esc_url( get_template_directory_uri() . "/languages/{$code}.mo" ); ?>" download>
											<?php esc_html_e( 'Download .mo', 'solar-template' ); ?>
										</a>
									<?php else : ?>
										<span class="description"><?php esc_html_e( 'Not compiled yet', 'solar-template' ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Import translations', 'solar-template' ); ?></th>
			<td>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="solar_template_import_translations" />
					<?php wp_nonce_field( 'solar_template_import_translations' ); ?>
					<p>
						<select name="language_code">
							<?php foreach ( $languages as $code => $language ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $language['flag'] . ' ' . $language['label'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<div class="solar-template-settings__dropzone" data-solar-dropzone>
						<p><?php esc_html_e( 'Drag & drop a .po or .mo file here, or click to choose one.', 'solar-template' ); ?></p>
						<p data-solar-dropzone-filename></p>
						<input type="file" name="import_file" accept=".po,.mo" />
					</div>
					<p>
						<button type="submit" class="button"><?php esc_html_e( 'Import', 'solar-template' ); ?></button>
					</p>
				</form>
				<?php if ( isset( $_GET['solar_template_import'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feedback flag, not a state-changing request. ?>
					<?php if ( 'success' === $_GET['solar_template_import'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
						<p class="description"><?php esc_html_e( 'Import completed and the language recompiled.', 'solar-template' ); ?></p>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Import failed — please check the file and try again.', 'solar-template' ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Registers the "Languages" submenu page: the real add-a-language screen and installed-languages
	 * listing now live in Solar_Template\Admin\LanguagesController (see DECISIONS.md §9); this tab's
	 * own "Manage languages →" link above points here.
	 *
	 * @return void
	 */
	public static function register_languages_menu(): void {
		add_submenu_page(
			SettingsPage::MENU_SLUG,
			__( 'Languages', 'solar-template' ),
			__( 'Languages', 'solar-template' ),
			'manage_options',
			self::LANGUAGES_MENU_SLUG,
			array( LanguagesController::class, 'render_page' )
		);
	}

	/**
	 * Resolves which locale a front-end visitor should be served, given the theme's own
	 * language/auto-detect configuration. Pure function — no WordPress dependency — so it's unit
	 * tested directly.
	 *
	 * @param array<int, string> $active_codes      Every currently active language code.
	 * @param string             $configured_default Configured default language code, '' for none.
	 * @param bool                $auto_detect        Whether automatic detection is enabled.
	 * @param string              $accept_language    Raw `Accept-Language` header value, '' if absent.
	 * @param string              $site_locale        WordPress' own site locale, used as the final
	 *                                                  fallback.
	 * @return string
	 */
	public static function resolve_locale( array $active_codes, string $configured_default, bool $auto_detect, string $accept_language, string $site_locale ): string {
		if ( $auto_detect && '' !== $accept_language ) {
			$detected = self::match_accept_language( $active_codes, $accept_language );

			if ( null !== $detected ) {
				return $detected;
			}
		}

		if ( '' !== $configured_default && in_array( $configured_default, $active_codes, true ) ) {
			return $configured_default;
		}

		return $site_locale;
	}

	/**
	 * Matches a raw `Accept-Language` header against the theme's active language codes, preferring
	 * an exact match (`fr-FR` → `fr_FR`) over a same-primary-subtag one (`fr` → the first active
	 * `fr_*` code), in the header's own quality-value order.
	 *
	 * @param array<int, string> $active_codes    Every currently active language code.
	 * @param string              $accept_language Raw `Accept-Language` header value.
	 * @return string|null The matched code, or null when none of the header's preferences match.
	 */
	public static function match_accept_language( array $active_codes, string $accept_language ): ?string {
		$preferences = array();

		foreach ( explode( ',', $accept_language ) as $part ) {
			$part = trim( $part );

			if ( '' === $part ) {
				continue;
			}

			$pieces  = explode( ';q=', $part );
			$tag     = strtolower( trim( $pieces[0] ) );
			$quality = isset( $pieces[1] ) ? (float) $pieces[1] : 1.0;

			$preferences[] = array(
				'tag'     => $tag,
				'quality' => $quality,
			);
		}

		usort(
			$preferences,
			static function ( array $a, array $b ): int {
				return $b['quality'] <=> $a['quality'];
			}
		);

		$normalized_active = array();
		foreach ( $active_codes as $code ) {
			$normalized_active[ strtolower( $code ) ] = $code;
		}

		foreach ( $preferences as $preference ) {
			$normalized_tag = str_replace( '-', '_', $preference['tag'] );

			if ( isset( $normalized_active[ $normalized_tag ] ) ) {
				return $normalized_active[ $normalized_tag ];
			}

			$primary_subtag = strtok( $normalized_tag, '_' );

			foreach ( $normalized_active as $lowercase_code => $code ) {
				if ( strtok( $lowercase_code, '_' ) === $primary_subtag ) {
					return $code;
				}
			}
		}

		return null;
	}

	/**
	 * Filter callback for `determine_locale`: resolves the front-end visitor's locale from the
	 * theme's own configuration. Left untouched in wp-admin, so the signed-in administrator's own
	 * profile language is never overridden.
	 *
	 * @param string $locale Value the filter was called with.
	 * @return string
	 */
	public static function filter_locale( string $locale ): string {
		if ( is_admin() ) {
			return $locale;
		}

		$active_codes = array_keys( self::active_languages() );

		if ( empty( $active_codes ) ) {
			return $locale;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only, parsed by self::match_accept_language() below, never used as-is.
		$accept_language = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? (string) $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

		return self::resolve_locale(
			$active_codes,
			SettingsRepository::get( 'translations.default_language', '' ),
			'1' === SettingsRepository::get( 'translations.auto_detect', '0' ),
			$accept_language,
			$locale
		);
	}

	/**
	 * Handles the "Import translations" form's submission (`admin-post.php?action=
	 * solar_template_import_translations`): parses the uploaded `.po`/`.mo` file with gettext/gettext
	 * (already a dependency — see Solar_Template\I18n\GettextMoCompiler) and registers every entry it
	 * contains for the chosen language, then recompiles that language's `.mo`.
	 *
	 * @return void
	 */
	public static function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to import translations.', 'solar-template' ) );
		}

		check_admin_referer( 'solar_template_import_translations' );

		$language_code = isset( $_POST['language_code'] ) ? sanitize_text_field( wp_unslash( $_POST['language_code'] ) ) : '';
		$active_codes  = array_keys( self::active_languages() );
		$status        = 'error';

		if (
			in_array( $language_code, $active_codes, true )
			&& isset( $_FILES['import_file'] )
			&& UPLOAD_ERR_OK === (int) $_FILES['import_file']['error']
		) {
			$tmp_path  = (string) $_FILES['import_file']['tmp_name'];
			$file_name = sanitize_file_name( (string) $_FILES['import_file']['name'] );
			$extension = strtolower( (string) pathinfo( $file_name, PATHINFO_EXTENSION ) );

			if ( in_array( $extension, array( 'po', 'mo' ), true ) && self::import_file( $tmp_path, $extension, $language_code ) ) {
				$status = 'success';
			}
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                  => SettingsPage::MENU_SLUG,
					'tab'                   => self::slug(),
					'solar_template_import' => $status,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Parses $tmp_path with the loader matching $extension and registers every entry it contains for
	 * $language_code, then recompiles that language.
	 *
	 * @param string $tmp_path      Absolute path of the uploaded file.
	 * @param string $extension     `po` or `mo`.
	 * @param string $language_code Locale code to import the file's entries into.
	 * @return bool True on success.
	 */
	private static function import_file( string $tmp_path, string $extension, string $language_code ): bool {
		try {
			$loader       = 'po' === $extension ? new PoLoader() : new MoLoader();
			$translations = $loader->loadFile( $tmp_path );
		} catch ( \Throwable $exception ) {
			return false;
		}

		$translator = Translator::instance();
		$imported   = 0;

		foreach ( $translations as $translation ) {
			$singular = $translation->getTranslation();

			if ( null === $singular || '' === $singular ) {
				continue;
			}

			$plural = null;
			if ( null !== $translation->getPlural() ) {
				$plural_forms = $translation->getPluralTranslations();
				$plural       = $plural_forms[0] ?? null;
			}

			$translator->set_string( $language_code, $translation->getOriginal(), $singular, $plural, (string) $translation->getContext() );
			++$imported;
		}

		if ( 0 === $imported ) {
			return false;
		}

		return $translator->compile( $language_code );
	}
}
