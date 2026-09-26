<?php
/**
 * Created: 2026-09-26 09:05 CEST
 * Role: Settings tab contract (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Common shape every settings tab (General, Appearance, Header, Footer, and future ones)
 *          implements, so Solar_Template\Admin\SettingsPage can register/render/save any of them
 *          generically. Static methods, no instantiation — same all-static convention used
 *          throughout the theme's other content/config classes (e.g.
 *          Solar_Template\FrontPage\Testimonials).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contract implemented by every settings tab.
 */
interface SettingsTabInterface {

	/**
	 * @return string Unique tab slug, also used as the settings key prefix (`{slug}.{field}`).
	 */
	public static function slug(): string;

	/**
	 * @return string Translated tab label, shown in the settings page's nav and heading.
	 */
	public static function label(): string;

	/**
	 * @return string A dashicon class name (e.g. `dashicons-admin-generic`) shown next to the label.
	 */
	public static function icon(): string;

	/**
	 * @return array<string, string> Every field this tab persists, unprefixed key => default value.
	 */
	public static function defaults(): array;

	/**
	 * Sanitizes raw, unslashed POST data for this tab's own fields.
	 *
	 * @param array<string, mixed> $raw Raw (already `wp_unslash()`-ed) POST data for the whole request.
	 * @return array<string, string> Same keys as self::defaults(), sanitized values.
	 */
	public static function sanitize( array $raw ): array;

	/**
	 * Renders this tab's settings fields as `<tr>` rows of a `<table class="form-table">` (the
	 * surrounding `<table>`/`<form>`/save button are rendered once by SettingsPage).
	 *
	 * @param array<string, string> $values Current values, same keys as self::defaults().
	 * @return void
	 */
	public static function render( array $values ): void;
}
