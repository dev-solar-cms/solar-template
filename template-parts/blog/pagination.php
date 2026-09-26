<?php
/**
 * Created: 2026-09-26 21:52 CEST
 * Role: Blog archive pagination template-part (template-parts/blog/pagination.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the numbered pagination circles from Solar_Template\Blog\BlogPagination::links(),
 *          built on top of WordPress' own native `paginate_links()`. Renders nothing on a single-page
 *          archive.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Blog\BlogPagination;

global $wp_query;

$links = BlogPagination::links( $wp_query );

if ( empty( $links ) ) {
	return;
}
?>
<nav class="blog-pagination" aria-label="<?php esc_attr_e( 'Article pages', 'solar-template' ); ?>">
	<?php foreach ( $links as $page_link ) : ?>
		<?php echo wp_kses_post( $page_link ); ?>
	<?php endforeach; ?>
</nav>
