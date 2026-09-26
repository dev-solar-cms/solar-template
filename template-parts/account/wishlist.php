<?php
/**
 * Created: 2026-09-26 17:15 CEST
 * Role: My Account wishlist template-part (template-parts/account/wishlist.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the customer's saved products as a grid, reusing the existing product card
 *          component, or an empty-state message. Used by
 *          Solar_Template\Account\WishlistController::render_wishlist_page().
 *
 * Expected `$args` keys:
 * - products (array<int, array>) each shaped like template-parts/product-card.php's own `$args`
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'products' => array(),
);

$data = wp_parse_args( $args ?? array(), $defaults );
?>
<h1>
	<?php esc_html_e( 'Wishlist', 'solar-template' ); ?>
	<span class="account-wishlist__count">
		(<?php echo esc_html( (string) count( $data['products'] ) ); ?>)
	</span>
</h1>

<?php if ( empty( $data['products'] ) ) : ?>
	<p class="account-panel__empty"><?php esc_html_e( 'You have not saved any product yet.', 'solar-template' ); ?></p>
<?php else : ?>
	<div class="account-wishlist__grid">
		<?php foreach ( $data['products'] as $product_card_args ) : ?>
			<?php get_template_part( 'template-parts/product-card', null, $product_card_args ); ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
