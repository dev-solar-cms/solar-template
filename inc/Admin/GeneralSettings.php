<?php
/**
 * Created: 2026-09-26 09:20 CEST
 * Role: "General" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Shop name, logo/favicon, currency/currency position (applied to WooCommerce when set —
 *          see self::apply_currency_overrides()), and maintenance mode. Implements
 *          SettingsTabInterface for the admin screen, and exposes the same values as public static
 *          read accessors the rest of the theme (header.php, `wp_head`, WooCommerce filters) calls
 *          directly — same "content + admin" convention as e.g. Solar_Template\Header\MegaMenu.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "General" settings tab: shop name, logo/favicon, currency, maintenance mode.
 */
final class GeneralSettings implements SettingsTabInterface {

	private const CURRENCIES = array(
		'EUR' => 'Euro (€) — EUR',
		'USD' => 'Dollar ($) — USD',
		'GBP' => 'Pound (£) — GBP',
		'CHF' => 'Swiss franc (CHF)',
	);

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'general';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'General', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-admin-generic';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		return array(
			'shop_name'         => '',
			'logo_id'           => '0',
			'favicon_id'        => '0',
			'currency'          => '',
			'currency_position' => '',
			'maintenance_mode'  => '0',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$currency = isset( $raw['currency'] ) ? strtoupper( sanitize_key( (string) $raw['currency'] ) ) : '';
		$position = isset( $raw['currency_position'] ) ? sanitize_key( (string) $raw['currency_position'] ) : '';

		return array(
			'shop_name'         => isset( $raw['shop_name'] ) ? sanitize_text_field( (string) $raw['shop_name'] ) : '',
			'logo_id'           => isset( $raw['logo_id'] ) ? (string) absint( $raw['logo_id'] ) : '0',
			'favicon_id'        => isset( $raw['favicon_id'] ) ? (string) absint( $raw['favicon_id'] ) : '0',
			'currency'          => array_key_exists( $currency, self::CURRENCIES ) ? $currency : '',
			'currency_position' => in_array( $position, array( 'before', 'after' ), true ) ? $position : '',
			'maintenance_mode'  => ! empty( $raw['maintenance_mode'] ) ? '1' : '0',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		$logo_id    = (int) $values['general.logo_id'];
		$favicon_id = (int) $values['general.favicon_id'];
		?>
		<tr>
			<th><label for="st-shop-name"><?php esc_html_e( 'Shop name', 'solar-template' ); ?></label></th>
			<td>
				<input type="text" id="st-shop-name" name="shop_name" value="<?php echo esc_attr( $values['general.shop_name'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Shown in the header and transactional emails.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Logo', 'solar-template' ); ?></th>
			<td>
				<?php self::render_media_field( 'logo_id', $logo_id, __( 'Recommended: PNG/SVG/WebP, 320×80px.', 'solar-template' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Favicon', 'solar-template' ); ?></th>
			<td>
				<?php self::render_media_field( 'favicon_id', $favicon_id, __( 'Recommended: 32×32 or 64×64px, ICO or PNG.', 'solar-template' ) ); ?>
			</td>
		</tr>
		<tr>
			<th><label for="st-currency"><?php esc_html_e( 'Currency', 'solar-template' ); ?></label></th>
			<td>
				<select id="st-currency" name="currency">
					<option value=""><?php esc_html_e( "Use WooCommerce's own setting", 'solar-template' ); ?></option>
					<?php foreach ( self::CURRENCIES as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $values['general.currency'], $code ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Symbol position', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__radio-group">
					<label><input type="radio" name="currency_position" value="before" <?php checked( $values['general.currency_position'], 'before' ); ?> /> <?php esc_html_e( 'Before (€ 89.00)', 'solar-template' ); ?></label>
					<label><input type="radio" name="currency_position" value="after" <?php checked( $values['general.currency_position'], 'after' ); ?> /> <?php esc_html_e( 'After (89.00 €)', 'solar-template' ); ?></label>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Maintenance mode', 'solar-template' ); ?></th>
			<td>
				<label>
					<input type="checkbox" class="solar-template-settings__toggle" name="maintenance_mode" value="1" <?php checked( $values['general.maintenance_mode'], '1' ); ?> />
					<?php esc_html_e( 'Show a maintenance page to visitors', 'solar-template' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Signed-in administrators can still browse the real site while this is on.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renders a logo/favicon media field: a preview (image if one is set, a dashed placeholder
	 * otherwise), WordPress' native media uploader button, and a hidden input storing the attachment
	 * ID.
	 *
	 * @param string $field_name  `logo_id` or `favicon_id`.
	 * @param int    $attachment_id Currently selected attachment ID, 0 when none.
	 * @param string $hint        Format/size recommendation shown under the button.
	 * @return void
	 */
	private static function render_media_field( string $field_name, int $attachment_id, string $hint ): void {
		$url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
		?>
		<div class="solar-template-settings__color-row">
			<div class="solar-template-media-preview" style="width:100px;height:56px;border:1px dashed #c3c4c7;border-radius:3px;background:#f8f6f2;display:flex;align-items:center;justify-content:center;overflow:hidden;">
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="" style="max-width:100%;max-height:100%;" />
				<?php else : ?>
					<span style="font-size:10px;color:#646970;"><?php esc_html_e( 'No file', 'solar-template' ); ?></span>
				<?php endif; ?>
			</div>
			<div>
				<button type="button" class="button solar-template-media-button" data-target="<?php echo esc_attr( $field_name ); ?>">
					<?php esc_html_e( 'Choose a file', 'solar-template' ); ?>
				</button>
				<button type="button" class="button-link solar-template-media-remove" data-target="<?php echo esc_attr( $field_name ); ?>" style="margin-left:8px;color:#b32d2e;">
					<?php esc_html_e( 'Remove', 'solar-template' ); ?>
				</button>
				<input type="hidden" id="st-<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>" />
				<p class="description"><?php echo esc_html( $hint ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * @return string The configured shop name, falling back to the site's own title when unset.
	 */
	public static function shop_name(): string {
		$value = SettingsRepository::get( 'general.shop_name', '' );

		return '' !== $value ? $value : get_bloginfo( 'name' );
	}

	/**
	 * @return int The configured logo's attachment ID, 0 when none is set.
	 */
	public static function logo_id(): int {
		return (int) SettingsRepository::get( 'general.logo_id', '0' );
	}

	/**
	 * @return int The configured favicon's attachment ID, 0 when none is set.
	 */
	public static function favicon_id(): int {
		return (int) SettingsRepository::get( 'general.favicon_id', '0' );
	}

	/**
	 * @return string A 3-letter ISO currency code, or '' to keep WooCommerce's own setting.
	 */
	public static function currency(): string {
		return SettingsRepository::get( 'general.currency', '' );
	}

	/**
	 * @return string 'before', 'after', or '' to keep WooCommerce's own setting.
	 */
	public static function currency_position(): string {
		return SettingsRepository::get( 'general.currency_position', '' );
	}

	/**
	 * @return bool Whether maintenance mode is currently enabled.
	 */
	public static function is_maintenance_mode(): bool {
		return '1' === SettingsRepository::get( 'general.maintenance_mode', '0' );
	}

	/**
	 * Filter callback for `pre_option_woocommerce_currency`: short-circuits WooCommerce's own
	 * currency option when this setting is configured.
	 *
	 * @param mixed $pre_option Value WordPress would return unless short-circuited.
	 * @return mixed
	 */
	public static function filter_currency_pre_option( $pre_option ) {
		$currency = self::currency();

		return '' !== $currency ? $currency : $pre_option;
	}

	/**
	 * Filter callback for `pre_option_woocommerce_currency_pos`: short-circuits WooCommerce's own
	 * currency position option when this setting is configured. Maps this theme's simpler
	 * before/after choice to WooCommerce's own `left_space`/`right_space` values (symbol with a
	 * space before the amount).
	 *
	 * @param mixed $pre_option Value WordPress would return unless short-circuited.
	 * @return mixed
	 */
	public static function filter_currency_position_pre_option( $pre_option ) {
		$position = self::currency_position();

		if ( '' === $position ) {
			return $pre_option;
		}

		return 'before' === $position ? 'left_space' : 'right_space';
	}

	/**
	 * Filter callback for `wp_mail_from_name`.
	 *
	 * @param string $default_name Value the filter was called with.
	 * @return string
	 */
	public static function filter_mail_from_name( string $default_name ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_name is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return self::shop_name();
	}

	/**
	 * Prints a `<link rel="icon">` for the configured favicon, when one is set. Hooked on `wp_head`.
	 *
	 * @return void
	 */
	public static function print_favicon(): void {
		$favicon_id = self::favicon_id();

		if ( ! $favicon_id ) {
			return;
		}

		$url = wp_get_attachment_image_url( $favicon_id, 'full' );

		if ( ! $url ) {
			return;
		}

		printf( '<link rel="icon" href="%s" />' . "\n", esc_url( $url ) );
	}
}
