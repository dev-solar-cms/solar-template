<?php
/**
 * Created: 2026-09-25 08:30 CEST
 * Role: Reusable "Collections" mega menu panel template-part (template-parts/mega-menu.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the dropdown panel attached to the primary navigation's "Collections" item.
 *          Content is placeholder columns (see Solar_Template\Header\MegaMenu::columns()) —
 *          wiring real WooCommerce product categories is out of scope for this step. Open/close behaviour (hover, focus, Escape, outside click) lives in
 *          assets/js/header.js, which targets this panel by its `site-mega-menu` id.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="site-header__mega-menu" id="site-mega-menu">
	<div class="site-header__mega-menu-inner">
		<?php foreach ( \Solar_Template\Header\MegaMenu::columns() as $column ) : ?>
			<div class="site-header__mega-menu-column">
				<?php if ( '' !== $column['heading'] ) : ?>
					<span class="site-header__mega-menu-heading"><?php echo esc_html( $column['heading'] ); ?></span>
				<?php endif; ?>

				<?php foreach ( $column['links'] as $menu_link ) : ?>
					<a class="site-header__mega-menu-link" href="<?php echo esc_url( $menu_link['url'] ); ?>">
						<?php echo esc_html( $menu_link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
