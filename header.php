<?php
/**
 * Created: 2026-09-25 08:00 CEST
 * Role: Global header template (header.php), loaded by every page via get_header().
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Open the document (`<html>`/`<head>`/`<body>`) and render the sticky site header: a
 *          top bar (social links, brand, search/account/cart icons) and a primary navigation bar
 *          with a "Collections" mega menu (open/close behaviour in assets/js/header.js; real
 *          category content is out of scope, see Solar_Template\Header\MegaMenu::columns()). Also renders
 *          the full-screen search overlay toggled by the search icon, wrapping the theme's own
 *          searchform.php — a native WordPress search, which already includes WooCommerce
 *          products in its results once it's active. The AJAX-driven cart badge is added by a
 *          later step.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'solar-template' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'solar-template' ); ?></a>

<header class="site-header" id="site-header">
	<div class="site-header__topbar">
		<div class="site-header__topbar-inner">
			<div class="site-header__social">
				<?php foreach ( \Solar_Template\Header\SocialLinks::links() as $network => $social_link ) : ?>
					<a
						href="<?php echo esc_url( $social_link['url'] ); ?>"
						class="site-header__social-link"
						aria-label="<?php echo esc_attr( $social_link['label'] ); ?>"
					>
						<?php echo $social_link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG icon markup defined by the theme itself, never user input. ?>
					</a>
				<?php endforeach; ?>
			</div>

			<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>

			<div class="site-header__actions">
				<button
					type="button"
					class="site-header__action site-header__search-toggle"
					id="site-search-toggle"
					aria-haspopup="true"
					aria-expanded="false"
					aria-controls="site-search"
				>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'solar-template' ); ?></span>
					<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				</button>

				<a class="site-header__action" href="<?php echo esc_url( \Solar_Template\Header\Cart::account_url() ); ?>" aria-label="<?php esc_attr_e( 'My account', 'solar-template' ); ?>">
					<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
				</a>

				<a class="site-header__action site-header__cart" href="<?php echo esc_url( \Solar_Template\Header\Cart::cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'solar-template' ); ?>">
					<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
					<?php get_template_part( 'template-parts/cart-badge' ); ?>
				</a>
			</div>
		</div>
	</div>

	<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'solar-template' ); ?>">
		<div class="site-header__nav-inner">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-header__menu',
					'fallback_cb'    => array( \Solar_Template\Header\Nav::class, 'render_fallback' ),
				)
			);
			?>
		</div>

		<?php if ( \Solar_Template\Header\MegaMenu::enabled() ) : ?>
			<?php get_template_part( 'template-parts/mega-menu' ); ?>
		<?php endif; ?>
	</nav>
</header>

<div class="site-search-overlay" id="site-search">
	<div class="site-search-overlay__inner">
		<?php get_search_form(); ?>
		<button type="button" class="site-search-overlay__close" id="site-search-close">
			<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'solar-template' ); ?></span>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>
</div>
