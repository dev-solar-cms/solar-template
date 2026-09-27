<?php
/**
 * Created: 2026-09-26 12:10 CEST
 * Role: "Home page" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: A reorderable (drag & drop), toggleable list of the home page's own sections, wired for
 *          real onto front-page.php — reordering or disabling a section here changes
 *          which sections front-page.php actually renders, and in which order. Order/visibility are
 *          stored as two parallel comma-separated slug lists (`sections_order`/`sections_enabled`),
 *          same simple scalar-value convention as every other settings tab.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Home page" settings tab: reorderable, toggleable section list.
 */
final class HomeSettings implements SettingsTabInterface {

	/**
	 * Every home page section, in the design handoff's own default order: slug => translated label.
	 *
	 * @return array<string, string>
	 */
	private static function sections(): array {
		return array(
			'hero'              => __( 'Hero', 'solar-template' ),
			'featured-products' => __( 'Featured products', 'solar-template' ),
			'categories'        => __( 'Categories', 'solar-template' ),
			'brand-story'       => __( 'Our story', 'solar-template' ),
			'testimonials'      => __( 'Testimonials', 'solar-template' ),
			'blog-preview'      => __( 'Blog', 'solar-template' ),
			'newsletter'        => __( 'Newsletter', 'solar-template' ),
		);
	}

	/**
	 * Maps each section slug to the front-page.php template-part it renders.
	 *
	 * @return array<string, string>
	 */
	private static function template_parts(): array {
		return array(
			'hero'              => 'template-parts/front-page/hero',
			'featured-products' => 'template-parts/front-page/featured-products',
			'categories'        => 'template-parts/front-page/categories',
			'brand-story'       => 'template-parts/front-page/brand-story',
			'testimonials'      => 'template-parts/front-page/testimonials',
			'blog-preview'      => 'template-parts/front-page/blog-preview',
			'newsletter'        => 'template-parts/front-page/newsletter',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'home';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Home page', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-admin-home';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		$slugs = array_keys( self::sections() );

		return array(
			'sections_order'   => implode( ',', $slugs ),
			'sections_enabled' => implode( ',', $slugs ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$known_slugs = array_keys( self::sections() );

		$requested_order = isset( $raw['sections_order'] ) ? explode( ',', sanitize_text_field( (string) $raw['sections_order'] ) ) : array();
		$order           = self::resolve_order( $known_slugs, $requested_order );

		$requested_enabled = isset( $raw['sections_enabled'] ) && is_array( $raw['sections_enabled'] )
			? array_map( 'sanitize_key', $raw['sections_enabled'] )
			: array();
		$enabled           = array_values( array_intersect( $known_slugs, $requested_enabled ) );

		return array(
			'sections_order'   => implode( ',', $order ),
			'sections_enabled' => implode( ',', $enabled ),
		);
	}

	/**
	 * Builds a full, valid section order from a (possibly partial/stale/tampered-with) requested
	 * order: every known slug found in $requested_order keeps its relative order, and any known slug
	 * missing from it (a section added to the theme after this was last saved) is appended at the
	 * end — so the list is always complete, never silently dropping a section.
	 *
	 * Pure function — no WordPress dependency — so it's unit tested directly.
	 *
	 * @param array<int, string> $known_slugs     Every valid section slug, in the default order.
	 * @param array<int, string> $requested_order Submitted order, possibly partial/invalid.
	 * @return array<int, string> Complete, valid, deduplicated order.
	 */
	public static function resolve_order( array $known_slugs, array $requested_order ): array {
		$order = array_values( array_intersect( array_unique( $requested_order ), $known_slugs ) );

		foreach ( $known_slugs as $slug ) {
			if ( ! in_array( $slug, $order, true ) ) {
				$order[] = $slug;
			}
		}

		return $order;
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		$order          = self::resolve_order( array_keys( self::sections() ), explode( ',', $values['home.sections_order'] ) );
		$enabled_slugs  = array_filter( explode( ',', $values['home.sections_enabled'] ) );
		$section_labels = self::sections();
		?>
		<tr>
			<th><?php esc_html_e( 'Sections', 'solar-template' ); ?></th>
			<td>
				<p class="description"><?php esc_html_e( 'Drag to reorder. Untick a section to hide it from the home page.', 'solar-template' ); ?></p>
				<ul class="solar-template-settings__sortable" data-solar-sortable>
					<?php foreach ( $order as $slug ) : ?>
						<li class="solar-template-settings__sortable-item" draggable="true" data-slug="<?php echo esc_attr( $slug ); ?>">
							<span class="solar-template-settings__sortable-handle" aria-hidden="true">⠿</span>
							<label>
								<input type="checkbox" name="sections_enabled[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $enabled_slugs, true ) ); ?> />
								<?php echo esc_html( $section_labels[ $slug ] ?? $slug ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="sections_order" id="st-sections-order" value="<?php echo esc_attr( implode( ',', $order ) ); ?>" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Returns the ordered list of enabled home page sections as
	 * `{slug, template_part}` pairs, ready for front-page.php to loop over.
	 *
	 * @return array<int, array{slug: string, template_part: string}>
	 */
	public static function visible_sections(): array {
		$known_slugs    = array_keys( self::sections() );
		$template_parts = self::template_parts();

		$order   = self::resolve_order( $known_slugs, explode( ',', SettingsRepository::get( 'home.sections_order', implode( ',', $known_slugs ) ) ) );
		$enabled = array_filter( explode( ',', SettingsRepository::get( 'home.sections_enabled', implode( ',', $known_slugs ) ) ) );

		$sections = array();

		foreach ( $order as $slug ) {
			if ( in_array( $slug, $enabled, true ) && isset( $template_parts[ $slug ] ) ) {
				$sections[] = array(
					'slug'          => $slug,
					'template_part' => $template_parts[ $slug ],
				);
			}
		}

		return $sections;
	}
}
