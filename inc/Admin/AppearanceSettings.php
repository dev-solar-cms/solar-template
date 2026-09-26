<?php
/**
 * Created: 2026-09-26 09:40 CEST
 * Role: "Appearance" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Primary/accent/background colors, body/heading fonts, and element roundness — applied to
 *          the real front end at runtime through CSS custom properties printed in `wp_head` (see
 *          self::print_style_overrides()), which the compiled stylesheet's own tokens
 *          (assets/scss/_tokens.scss) already read via `var(--solar-*, …)`, needing no rebuild.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Appearance" settings tab: colors, fonts, element roundness.
 */
final class AppearanceSettings implements SettingsTabInterface {

	private const PRIMARY_PRESETS = array( '#0d0d0d', '#1a1a3e', '#1e3a5f', '#2d4a22', '#4a1a1a' );
	private const ACCENT_PRESETS  = array( '#c9a96e', '#e8b86d', '#b8895a', '#2271b1', '#2e7d32', '#9c27b0' );

	private const BODY_FONTS = array(
		'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@300;400;500;600;700;800',
		'DM Sans'           => 'DM+Sans:wght@400;500;600;700;800',
		'Inter'             => 'Inter:wght@400;500;600;700;800',
		'Outfit'            => 'Outfit:wght@400;500;600;700;800',
		'Nunito'            => 'Nunito:wght@400;500;600;700;800',
		'Roboto'            => 'Roboto:wght@400;500;700;900',
	);

	private const HEADING_FONTS = array(
		'Playfair Display' => 'Playfair+Display:wght@700;800',
		'Cormorant'        => 'Cormorant:wght@600;700',
		'Merriweather'     => 'Merriweather:wght@700;900',
	);

	private const RADII = array(
		'0'  => 'Square (0px)',
		'5'  => 'Subtle (5px)',
		'10' => 'Rounded (10px)',
		'99' => 'Pill (99px)',
	);

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'appearance';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Appearance', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-admin-appearance';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		return array(
			'primary_color'    => '#0d0d0d',
			'accent_color'     => '#c9a96e',
			'background_color' => '#f8f6f2',
			'font_body'        => 'Plus Jakarta Sans',
			'font_heading'     => '',
			'radius'           => '5',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$defaults     = self::defaults();
		$font_body    = isset( $raw['font_body'] ) ? sanitize_text_field( (string) $raw['font_body'] ) : '';
		$font_heading = isset( $raw['font_heading'] ) ? sanitize_text_field( (string) $raw['font_heading'] ) : '';
		$radius       = isset( $raw['radius'] ) ? sanitize_key( (string) $raw['radius'] ) : '';

		return array(
			'primary_color'    => self::sanitize_color( $raw['primary_color'] ?? '', $defaults['primary_color'] ),
			'accent_color'     => self::sanitize_color( $raw['accent_color'] ?? '', $defaults['accent_color'] ),
			'background_color' => self::sanitize_color( $raw['background_color'] ?? '', $defaults['background_color'] ),
			'font_body'        => array_key_exists( $font_body, self::BODY_FONTS ) ? $font_body : $defaults['font_body'],
			'font_heading'     => ( '' === $font_heading || array_key_exists( $font_heading, self::HEADING_FONTS ) ) ? $font_heading : '',
			'radius'           => array_key_exists( $radius, self::RADII ) ? $radius : $defaults['radius'],
		);
	}

	/**
	 * @param string $value   Raw submitted hex color.
	 * @param string $default_value Value used when $value isn't a valid `#rrggbb`/`#rgb` color.
	 * @return string
	 */
	private static function sanitize_color( string $value, string $default_value ): string {
		$sanitized = sanitize_hex_color( $value );

		return $sanitized ? $sanitized : $default_value;
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		?>
		<tr>
			<th><label for="st-primary-color"><?php esc_html_e( 'Primary color', 'solar-template' ); ?></label></th>
			<td>
				<div class="solar-template-settings__color-row">
					<span style="width:36px;height:36px;border-radius:4px;border:1px solid #c3c4c7;background:<?php echo esc_attr( $values['appearance.primary_color'] ); ?>;"></span>
					<input type="text" id="st-primary-color" name="primary_color" value="<?php echo esc_attr( $values['appearance.primary_color'] ); ?>" class="regular-text" style="max-width:120px;" />
				</div>
				<p class="description"><?php esc_html_e( 'Buttons, headings, dark navigation.', 'solar-template' ); ?></p>
				<?php self::render_swatches( 'st-primary-color', self::PRIMARY_PRESETS ); ?>
			</td>
		</tr>
		<tr>
			<th><label for="st-accent-color"><?php esc_html_e( 'Accent color', 'solar-template' ); ?></label></th>
			<td>
				<div class="solar-template-settings__color-row">
					<span style="width:36px;height:36px;border-radius:4px;border:1px solid #c3c4c7;background:<?php echo esc_attr( $values['appearance.accent_color'] ); ?>;"></span>
					<input type="text" id="st-accent-color" name="accent_color" value="<?php echo esc_attr( $values['appearance.accent_color'] ); ?>" class="regular-text" style="max-width:120px;" />
				</div>
				<p class="description"><?php esc_html_e( 'Golden accent for badges, prices, CTAs, active links.', 'solar-template' ); ?></p>
				<?php self::render_swatches( 'st-accent-color', self::ACCENT_PRESETS ); ?>
			</td>
		</tr>
		<tr>
			<th><label for="st-background-color"><?php esc_html_e( 'Background color', 'solar-template' ); ?></label></th>
			<td>
				<div class="solar-template-settings__color-row">
					<span style="width:36px;height:36px;border-radius:4px;border:1px solid #c3c4c7;background:<?php echo esc_attr( $values['appearance.background_color'] ); ?>;"></span>
					<input type="text" id="st-background-color" name="background_color" value="<?php echo esc_attr( $values['appearance.background_color'] ); ?>" class="regular-text" style="max-width:120px;" />
				</div>
				<p class="description"><?php esc_html_e( 'Overall page background.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="st-font-body"><?php esc_html_e( 'Main font', 'solar-template' ); ?></label></th>
			<td>
				<select id="st-font-body" name="font_body">
					<?php foreach ( array_keys( self::BODY_FONTS ) as $font_name ) : ?>
						<option value="<?php echo esc_attr( $font_name ); ?>" <?php selected( $values['appearance.font_body'], $font_name ); ?>><?php echo esc_html( $font_name ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Loaded via Google Fonts. Applies to all running text.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="st-font-heading"><?php esc_html_e( 'Heading font', 'solar-template' ); ?></label></th>
			<td>
				<select id="st-font-heading" name="font_heading">
					<option value=""><?php esc_html_e( 'Same as the main font', 'solar-template' ); ?></option>
					<?php foreach ( array_keys( self::HEADING_FONTS ) as $font_name ) : ?>
						<option value="<?php echo esc_attr( $font_name ); ?>" <?php selected( $values['appearance.font_heading'], $font_name ); ?>><?php echo esc_html( $font_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Element roundness', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__radio-group">
					<?php foreach ( self::RADII as $value => $label ) : ?>
						<label><input type="radio" name="radius" value="<?php echo esc_attr( $value ); ?>" <?php checked( $values['appearance.radius'], $value ); ?> /> <?php echo esc_html( $label ); ?></label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders a row of clickable preset color swatches that fill $target_input_id's value (see the
	 * shared click handler in Solar_Template\Admin\SettingsPage::render_styles()).
	 *
	 * @param string          $target_input_id Id of the text input the swatches fill in.
	 * @param array<int, string> $colors       Hex colors to offer as presets.
	 * @return void
	 */
	private static function render_swatches( string $target_input_id, array $colors ): void {
		?>
		<div class="solar-template-settings__swatches">
			<?php foreach ( $colors as $color ) : ?>
				<button type="button" class="solar-template-settings__swatch" style="background:<?php echo esc_attr( $color ); ?>;" data-target="<?php echo esc_attr( $target_input_id ); ?>" data-color="<?php echo esc_attr( $color ); ?>" aria-label="<?php echo esc_attr( $color ); ?>"></button>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * @return string Hex primary color.
	 */
	public static function primary_color(): string {
		return SettingsRepository::get( 'appearance.primary_color', self::defaults()['primary_color'] );
	}

	/**
	 * @return string Hex accent color.
	 */
	public static function accent_color(): string {
		return SettingsRepository::get( 'appearance.accent_color', self::defaults()['accent_color'] );
	}

	/**
	 * @return string Hex background color.
	 */
	public static function background_color(): string {
		return SettingsRepository::get( 'appearance.background_color', self::defaults()['background_color'] );
	}

	/**
	 * @return string Main (body) font family name.
	 */
	public static function font_body(): string {
		return SettingsRepository::get( 'appearance.font_body', self::defaults()['font_body'] );
	}

	/**
	 * @return string Heading font family name, or '' to inherit the main font.
	 */
	public static function font_heading(): string {
		return SettingsRepository::get( 'appearance.font_heading', '' );
	}

	/**
	 * @return int Element roundness, in pixels (0, 5, 10 or 99).
	 */
	public static function radius(): int {
		return (int) SettingsRepository::get( 'appearance.radius', self::defaults()['radius'] );
	}

	/**
	 * Prints the `:root` custom properties the compiled stylesheet's own design tokens read (see
	 * assets/scss/_tokens.scss), overriding colors/roundness/fonts without a rebuild. Hooked on
	 * `wp_head` after the compiled stylesheet (see Solar_Template\Theme::boot()'s priority).
	 *
	 * @return void
	 */
	public static function print_style_overrides(): void {
		$declarations = array(
			'--solar-color-dark' => self::primary_color(),
			'--solar-color-gold' => self::accent_color(),
			'--solar-color-bg'   => self::background_color(),
			'--solar-radius'     => self::radius() . 'px',
		);

		$font_body = self::font_body();
		if ( '' !== $font_body && 'Plus Jakarta Sans' !== $font_body ) {
			$declarations['--solar-font-body'] = "'" . $font_body . "'";
		}

		$font_heading = self::font_heading();
		if ( '' !== $font_heading ) {
			$declarations['--solar-font-heading'] = "'" . $font_heading . "'";
		}

		$css = '';
		foreach ( $declarations as $property => $value ) {
			$css .= "{$property}:{$value};";
		}

		printf( '<style id="solar-template-appearance-overrides">:root{%s}</style>', $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every value above is either a sanitize_hex_color()-validated color, a digit-only radius, or a font name matched against a fixed internal allow-list.
	}

	/**
	 * Enqueues the Google Fonts stylesheet for any non-default body/heading font chosen in the
	 * admin. Hooked on `wp_enqueue_scripts`, after Solar_Template\Theme::enqueue_assets().
	 *
	 * @return void
	 */
	public static function enqueue_google_fonts(): void {
		$families = array();

		$font_body = self::font_body();
		if ( 'Plus Jakarta Sans' !== $font_body && isset( self::BODY_FONTS[ $font_body ] ) ) {
			$families[] = self::BODY_FONTS[ $font_body ];
		}

		$font_heading = self::font_heading();
		if ( isset( self::HEADING_FONTS[ $font_heading ] ) ) {
			$families[] = self::HEADING_FONTS[ $font_heading ];
		}

		if ( empty( $families ) ) {
			return;
		}

		$query = implode(
			'&',
			array_map(
				static function ( string $family ): string {
					return 'family=' . $family;
				},
				$families
			)
		);

		wp_enqueue_style(
			'solar-template-appearance-fonts',
			"https://fonts.googleapis.com/css2?{$query}&display=swap",
			array(),
			wp_get_theme( get_template() )->get( 'Version' )
		);
	}
}
