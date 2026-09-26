<?php
/**
 * Created: 2026-09-26 10:40 CEST
 * Role: Cart/checkout item meta override (woocommerce/cart/cart-item-data.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a cart/checkout line item's variation attributes and any custom data added via
 *          the `woocommerce_get_item_data` filter (e.g. Solar_Template\Product\EngravingCart's
 *          engraving line) as a single inline "Key: Value · Key: Value" line instead of
 *          WooCommerce's default definition list, matching the design handoff's cart mockup.
 *          Called by wc_get_formatted_cart_item_data(), so $item_data keeps its exact shape.
 *
 * @package Solar_Template
 * @var array<int, array{key: string, display: string}> $item_data
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $item_data ) ) {
	return;
}
?>
<div class="cart-item-meta">
	<?php foreach ( $item_data as $solar_item_data_index => $solar_item_data_entry ) : ?>
		<?php if ( $solar_item_data_index > 0 ) : ?>
			<span class="cart-item-meta__separator" aria-hidden="true"> · </span>
		<?php endif; ?>
		<span class="cart-item-meta__entry">
			<?php echo wp_kses_post( $solar_item_data_entry['key'] ); ?>:
			<?php echo wp_kses_post( wp_strip_all_tags( $solar_item_data_entry['display'] ) ); ?>
		</span>
	<?php endforeach; ?>
</div>
