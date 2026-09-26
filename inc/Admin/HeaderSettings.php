<?php
/**
 * Created: 2026-09-26 09:55 CEST
 * Role: "Header" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Header layout, mega menu, promo bar, social links, and transparent-on-home — every field
 *          wired for real onto header.php/assets/scss/_header.scss, not just stored. The mega
 *          menu/social links fields hook into the exact filters
 *          (`solar_template_header_mega_menu_enabled`/`solar_template_social_links`) Group 02 left
 *          for this purpose.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Header" settings tab: layout, mega menu, promo bar, social links, transparent-on-home.
 */
final class HeaderSettings implements SettingsTabInterface {

	private const LAYOUTS = array(
		'centered'    => array(
			'label'       => 'Centered logo · Social left · Cart right',
			'description' => "Solar Template's default layout",
		),
		'left-center' => array(
			'label'       => 'Left logo · Centered navigation · Icons right',
			'description' => '',
		),
		'left-right'  => array(
			'label'       => 'Left logo · Right navigation',
			'description' => '',
		),
	);

	private const SOCIAL_NETWORKS = array(
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'pinterest' => 'Pinterest',
		'x'         => 'X / Twitter',
	);

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'header';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Header', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-editor-insertmore';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		return array(
			'layout'            => 'centered',
			'mega_menu_enabled' => '1',
			'promo_bar_enabled' => '0',
			'promo_bar_text'    => '',
			'social_instagram'  => '',
			'social_facebook'   => '',
			'social_pinterest'  => '',
			'social_x'          => '',
			'transparent_home'  => '0',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$layout = isset( $raw['layout'] ) ? sanitize_key( (string) $raw['layout'] ) : 'centered';

		$values = array(
			'layout'            => array_key_exists( $layout, self::LAYOUTS ) ? $layout : 'centered',
			'mega_menu_enabled' => ! empty( $raw['mega_menu_enabled'] ) ? '1' : '0',
			'promo_bar_enabled' => ! empty( $raw['promo_bar_enabled'] ) ? '1' : '0',
			'promo_bar_text'    => isset( $raw['promo_bar_text'] ) ? sanitize_text_field( (string) $raw['promo_bar_text'] ) : '',
			'transparent_home'  => ! empty( $raw['transparent_home'] ) ? '1' : '0',
		);

		foreach ( array_keys( self::SOCIAL_NETWORKS ) as $network ) {
			$field            = "social_{$network}";
			$values[ $field ] = isset( $raw[ $field ] ) ? esc_url_raw( (string) $raw[ $field ] ) : '';
		}

		return $values;
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		?>
		<tr>
			<th><?php esc_html_e( 'Header layout', 'solar-template' ); ?></th>
			<td>
				<?php foreach ( self::LAYOUTS as $layout_slug => $layout ) : ?>
					<label class="solar-template-settings__layout-option<?php echo $values['header.layout'] === $layout_slug ? ' is-selected' : ''; ?>">
						<input type="radio" name="layout" value="<?php echo esc_attr( $layout_slug ); ?>" <?php checked( $values['header.layout'], $layout_slug ); ?> />
						<span>
							<strong style="display:block;"><?php echo esc_html( $layout['label'] ); ?></strong>
							<?php if ( $layout['description'] ) : ?>
								<span class="description"><?php echo esc_html( $layout['description'] ); ?></span>
							<?php endif; ?>
						</span>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Mega menu', 'solar-template' ); ?></th>
			<td>
				<label>
					<input type="checkbox" class="solar-template-settings__toggle" name="mega_menu_enabled" value="1" <?php checked( $values['header.mega_menu_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Shows the "Collections" mega menu on the primary navigation.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Promo bar', 'solar-template' ); ?></th>
			<td>
				<label style="display:block;margin-bottom:10px;">
					<input type="checkbox" class="solar-template-settings__toggle" name="promo_bar_enabled" value="1" <?php checked( $values['header.promo_bar_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
				<input type="text" name="promo_bar_text" value="<?php echo esc_attr( $values['header.promo_bar_text'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Free shipping over €60 🚚', 'solar-template' ); ?>" />
				<p class="description"><?php esc_html_e( 'Information bar shown above the main header.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Social links', 'solar-template' ); ?></th>
			<td>
				<?php foreach ( self::SOCIAL_NETWORKS as $network => $network_label ) : ?>
					<div class="solar-template-settings__social-row">
						<span><?php echo esc_html( $network_label ); ?></span>
						<input type="url" name="social_<?php echo esc_attr( $network ); ?>" value="<?php echo esc_attr( $values[ "header.social_{$network}" ] ); ?>" class="regular-text" placeholder="https://" />
					</div>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Transparent header', 'solar-template' ); ?></th>
			<td>
				<label>
					<input type="checkbox" class="solar-template-settings__toggle" name="transparent_home" value="1" <?php checked( $values['header.transparent_home'], '1' ); ?> />
					<?php esc_html_e( 'Enabled on the home page only', 'solar-template' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * @return string CSS modifier class for the configured layout, '' for the default one.
	 */
	public static function layout_class(): string {
		$layout = SettingsRepository::get( 'header.layout', 'centered' );

		return 'centered' === $layout ? '' : "site-header--layout-{$layout}";
	}

	/**
	 * Filter callback for `solar_template_header_mega_menu_enabled`.
	 *
	 * @param bool $default_enabled Value the filter was called with.
	 * @return bool
	 */
	public static function apply_mega_menu_enabled( bool $default_enabled ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_enabled is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return '1' === SettingsRepository::get( 'header.mega_menu_enabled', '1' );
	}

	/**
	 * @return string The promo bar's text, or '' when disabled/empty.
	 */
	public static function promo_bar_text(): string {
		if ( '1' !== SettingsRepository::get( 'header.promo_bar_enabled', '0' ) ) {
			return '';
		}

		return SettingsRepository::get( 'header.promo_bar_text', '' );
	}

	/**
	 * Filter callback for `solar_template_social_links`: overrides each network's URL with the
	 * configured one, when set.
	 *
	 * @param array<string, array{url: string, label: string, icon: string}> $links Default links.
	 * @return array<string, array{url: string, label: string, icon: string}>
	 */
	public static function apply_social_links( array $links ): array {
		foreach ( array_keys( self::SOCIAL_NETWORKS ) as $network ) {
			$configured_url = SettingsRepository::get( "header.social_{$network}", '' );

			if ( '' !== $configured_url && isset( $links[ $network ] ) ) {
				$links[ $network ]['url'] = $configured_url;
			}
		}

		return $links;
	}

	/**
	 * @return bool Whether the header should render transparent over the hero on the front page.
	 */
	public static function is_transparent_on_home(): bool {
		return '1' === SettingsRepository::get( 'header.transparent_home', '0' );
	}
}
