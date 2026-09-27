<?php
/**
 * Created: 2026-09-26 13:35 CEST
 * Role: "Blog" settings tab (Solar_Template\Admin).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Posts per page, the featured-article block toggle, grid columns, share buttons, and the
 *          number of related articles — every field wired for real onto the blog index/
 *          archives/article page.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Blog" settings tab.
 */
final class BlogSettings implements SettingsTabInterface {

	private const SHARE_NETWORKS = array(
		'facebook'  => 'Facebook',
		'x'         => 'X / Twitter',
		'pinterest' => 'Pinterest',
		'copy_link' => 'Copy link',
	);

	/**
	 * @inheritDoc
	 */
	public static function slug(): string {
		return 'blog';
	}

	/**
	 * @inheritDoc
	 */
	public static function label(): string {
		return __( 'Blog', 'solar-template' );
	}

	/**
	 * @inheritDoc
	 */
	public static function icon(): string {
		return 'dashicons-admin-post';
	}

	/**
	 * @inheritDoc
	 */
	public static function defaults(): array {
		return array(
			'posts_per_page'   => '9',
			'featured_enabled' => '1',
			'columns'          => '3',
			'share_networks'   => implode( ',', array_keys( self::SHARE_NETWORKS ) ),
			'related_count'    => '3',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function sanitize( array $raw ): array {
		$posts_per_page = isset( $raw['posts_per_page'] ) ? absint( $raw['posts_per_page'] ) : 9;
		$columns        = isset( $raw['columns'] ) ? absint( $raw['columns'] ) : 3;
		$related_count  = isset( $raw['related_count'] ) ? absint( $raw['related_count'] ) : 3;

		$networks = isset( $raw['share_networks'] ) && is_array( $raw['share_networks'] )
			? array_values( array_intersect( array_map( 'sanitize_key', $raw['share_networks'] ), array_keys( self::SHARE_NETWORKS ) ) )
			: array();

		return array(
			'posts_per_page'   => (string) max( 1, $posts_per_page ),
			'featured_enabled' => ! empty( $raw['featured_enabled'] ) ? '1' : '0',
			'columns'          => (string) max( 2, min( 4, $columns ) ),
			'share_networks'   => implode( ',', $networks ),
			'related_count'    => (string) max( 0, min( 6, $related_count ) ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function render( array $values ): void {
		$enabled_networks = array_filter( explode( ',', $values['blog.share_networks'] ) );
		?>
		<tr>
			<th><label for="st-blog-posts-per-page"><?php esc_html_e( 'Articles per page', 'solar-template' ); ?></label></th>
			<td>
				<input type="number" id="st-blog-posts-per-page" name="posts_per_page" value="<?php echo esc_attr( $values['blog.posts_per_page'] ); ?>" min="1" class="small-text" />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Featured article', 'solar-template' ); ?></th>
			<td>
				<label>
					<input type="checkbox" class="solar-template-settings__toggle" name="featured_enabled" value="1" <?php checked( $values['blog.featured_enabled'], '1' ); ?> />
					<?php esc_html_e( 'Enabled', 'solar-template' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Shows the sticky post in a featured block above the blog index grid.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Columns', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__radio-group">
					<?php foreach ( array( '2', '3', '4' ) as $count ) : ?>
						<label><input type="radio" name="columns" value="<?php echo esc_attr( $count ); ?>" <?php checked( $values['blog.columns'], $count ); ?> /> <?php echo esc_html( $count ); ?></label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Share buttons', 'solar-template' ); ?></th>
			<td>
				<div class="solar-template-settings__checkbox-group">
					<?php foreach ( self::SHARE_NETWORKS as $network_key => $network_label ) : ?>
						<label>
							<input type="checkbox" name="share_networks[]" value="<?php echo esc_attr( $network_key ); ?>" <?php checked( in_array( $network_key, $enabled_networks, true ) ); ?> />
							<?php echo esc_html( $network_label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="st-blog-related-count"><?php esc_html_e( 'Related articles', 'solar-template' ); ?></label></th>
			<td>
				<input type="number" id="st-blog-related-count" name="related_count" value="<?php echo esc_attr( $values['blog.related_count'] ); ?>" min="0" max="6" class="small-text" />
				<p class="description"><?php esc_html_e( '0 hides the "Related articles" section entirely.', 'solar-template' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Applies the configured posts-per-page count to the blog index/category/tag archive main query.
	 * Hooked to `pre_get_posts`.
	 *
	 * @param \WP_Query $query The query being filtered.
	 * @return void
	 */
	public static function apply_posts_per_page( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_home() && ! $query->is_category() && ! $query->is_tag() ) {
			return;
		}

		$query->set( 'posts_per_page', (int) SettingsRepository::get( 'blog.posts_per_page', '9' ) );
	}

	/**
	 * Filter callback for `solar_template_blog_featured_enabled`.
	 *
	 * @param bool $default_enabled Value the filter was called with.
	 * @return bool
	 */
	public static function apply_featured_enabled( bool $default_enabled ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_enabled is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return '1' === SettingsRepository::get( 'blog.featured_enabled', '1' );
	}

	/**
	 * Filter callback for `solar_template_blog_columns`.
	 *
	 * @param int $default_columns Value the filter was called with.
	 * @return int
	 */
	public static function apply_columns( int $default_columns ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_columns is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return (int) SettingsRepository::get( 'blog.columns', '3' );
	}

	/**
	 * Filter callback for `solar_template_blog_share_networks`.
	 *
	 * @param array<int, string> $default_networks Value the filter was called with.
	 * @return array<int, string>
	 */
	public static function apply_share_networks( array $default_networks ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_networks is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		$stored = SettingsRepository::get( 'blog.share_networks', implode( ',', array_keys( self::SHARE_NETWORKS ) ) );

		return array_filter( explode( ',', $stored ) );
	}

	/**
	 * Filter callback for `solar_template_related_articles_count`.
	 *
	 * @param int $default_count Value the filter was called with.
	 * @return int
	 */
	public static function apply_related_count( int $default_count ): int { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $default_count is WordPress' own filter-callback convention; this setting always takes precedence once configured.
		return (int) SettingsRepository::get( 'blog.related_count', '3' );
	}
}
