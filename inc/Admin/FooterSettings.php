<?php
/**
 * Created: 2026-09-26 10:10 CEST
 * Role: "Footer" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Column count, newsletter column toggle, payment method badges, and copyright text —
 *          wired onto Solar_Template\Footer\Footer's existing filters
 *          (`solar_template_footer_config`/`solar_template_footer_payment_icons`/
 *          `solar_template_footer_copyright`), left by Group 02 for this purpose, plus a real column
 *          count class applied in footer.php.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Footer" settings tab: column count, newsletter column, payment icons, copyright text.
 */
final class FooterSettings implements SettingsTabInterface {

	private const PAYMENT_ICONS = array(
		'visa'       => 'VISA',
		'mastercard' => 'MC',
		'paypal'     => 'PAYPAL',
		'stripe'     => 'STRIPE',
		'apple_pay'  => 'APPLE PAY',
		'google_pay' => 'GOOGLE PAY',
	);

	private const DEFAULT_PAYMENT_ICONS = array( 'visa', 'mastercard', 'paypal', 'stripe', 'apple_pay' );

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'footer';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Footer', 'solar-template' );
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
			'columns_count'      => '5',
			'newsletter_enabled' => '1',
			'payment_icons'      => implode( ',', self::DEFAULT_PAYMENT_ICONS ),
			'copyright_text'     => '',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$count = isset( $raw['columns_count'] ) ? sanitize_key( (string) $raw['columns_count'] ) : '5';
		$icons = isset( $raw['payment_icons'] ) && is_array( $raw['payment_icons'] )
			? array_values( array_intersect( array_map( 'sanitize_key', $raw['payment_icons'] ), array_keys( self::PAYMENT_ICONS ) ) )
			: array();

		return array(
			'columns_count'      => in_array( $count, array( '2', '3', '4', '5' ), true ) ? $count : '5',
			'newsletter_enabled' => ! empty( $raw['newsletter_enabled'] ) ? '1' : '0',
			'payment_icons'      => implode( ',', $icons ),
			'copyright_text'     => isset( $raw['copyright_text'] ) ? sanitize_text_field( (string) $raw['copyright_text'] ) : '',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		$enabled_icons = array_filter( explode( ',', $values['footer.payment_icons'] ) );
		?>
		<tr>
			<th><?php esc_html_e( 'Number of columns', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__radio-group">
					<?php foreach ( array( '2', '3', '4', '5' ) as $count ) : ?>
						<label><input type="radio" name="columns_count" value="<?php echo esc_attr( $count ); ?>" <?php checked( $values['footer.columns_count'], $count ); ?> /> <?php echo esc_html( $count ); ?></label>
					<?php endforeach; ?>
				</div>
				<p class="description"><?php esc_html_e( 'Brand column and (if enabled) the newsletter column always take one slot each; the rest go to Shop/Information/Legal, in that order.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Newsletter column', 'solar-template' ); ?></th>
			<td>
				<label>
					<input type="checkbox" class="solar-template-settings__toggle" name="newsletter_enabled" value="1" <?php checked( $values['footer.newsletter_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Payment icons', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__checkbox-group">
					<?php foreach ( self::PAYMENT_ICONS as $icon_key => $icon_label ) : ?>
						<label>
							<input type="checkbox" name="payment_icons[]" value="<?php echo esc_attr( $icon_key ); ?>" <?php checked( in_array( $icon_key, $enabled_icons, true ) ); ?> />
							<?php echo esc_html( $icon_label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="st-copyright-text"><?php esc_html_e( 'Copyright text', 'solar-template' ); ?></label></th>
			<td>
				<input type="text" id="st-copyright-text" name="copyright_text" value="<?php echo esc_attr( $values['footer.copyright_text'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( sprintf( /* translators: %s: site name. */ __( '© {year} %s — All rights reserved', 'solar-template' ), get_bloginfo( 'name' ) ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Supports {year} for the current year.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * @return int Configured column count (2-5).
	 */
	public static function columns_count(): int {
		return (int) SettingsRepository::get( 'footer.columns_count', '5' );
	}

	/**
	 * @return bool Whether the newsletter column is enabled.
	 */
	public static function is_newsletter_enabled(): bool {
		return '1' === SettingsRepository::get( 'footer.newsletter_enabled', '1' );
	}

	/**
	 * Trims $columns (the footer's Shop/Information/Legal column list) down to how many slots
	 * remain once the brand column, and the newsletter column if enabled, have each taken one.
	 *
	 * Pure function — no WordPress dependency — so it's unit tested directly.
	 *
	 * @param array<int, mixed> $columns            Full Shop/Information/Legal column list.
	 * @param int                $count              Configured total column count (2-5).
	 * @param bool               $newsletter_enabled Whether the newsletter column is enabled.
	 * @return array<int, mixed>
	 */
	public static function resolve_visible_columns( array $columns, int $count, bool $newsletter_enabled ): array {
		$reserved_slots  = $newsletter_enabled ? 2 : 1; // brand, plus newsletter if enabled.
		$available_slots = max( 0, $count - $reserved_slots );

		return array_slice( $columns, 0, $available_slots );
	}

	/**
	 * Filter callback for `solar_template_footer_config`: trims the Shop/Information/Legal columns
	 * to the configured count, and drops the newsletter column entirely when disabled.
	 *
	 * @param array $config Default footer configuration.
	 * @return array
	 */
	public static function apply_footer_config( array $config ): array {
		$newsletter_enabled = self::is_newsletter_enabled();

		$config['columns'] = self::resolve_visible_columns( $config['columns'], self::columns_count(), $newsletter_enabled );

		if ( ! $newsletter_enabled ) {
			$config['newsletter'] = null;
		}

		return $config;
	}

	/**
	 * Filter callback for `solar_template_footer_payment_icons`.
	 *
	 * @param string[] $default_icons Default payment method labels.
	 * @return string[]
	 */
	public static function apply_payment_icons( array $default_icons ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_icons is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		$enabled = array_filter( explode( ',', SettingsRepository::get( 'footer.payment_icons', implode( ',', self::DEFAULT_PAYMENT_ICONS ) ) ) );

		$icons = array();
		foreach ( self::PAYMENT_ICONS as $icon_key => $icon_label ) {
			if ( in_array( $icon_key, $enabled, true ) ) {
				$icons[] = $icon_label;
			}
		}

		return $icons;
	}

	/**
	 * Filter callback for `solar_template_footer_copyright`.
	 *
	 * @param string $template Default copyright line template (with a literal `{year}` placeholder).
	 * @return string
	 */
	public static function apply_copyright( string $template ): string {
		$configured = SettingsRepository::get( 'footer.copyright_text', '' );

		return '' !== $configured ? $configured : $template;
	}
}
