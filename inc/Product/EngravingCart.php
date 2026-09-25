<?php
/**
 * Created: 2026-09-25 17:10 CEST
 * Role: Custom engraving cart/order integration (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Capture a submitted engraving selection into the cart item, add its surcharge to the
 *          cart item's price, show it in the cart/checkout line item, and persist it onto the
 *          resulting order line item — the WordPress-hook "controller" (DECISIONS.md MVC sense) for
 *          the custom engraving feature's cart lifecycle.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks the custom engraving option into the WooCommerce cart/checkout/order lifecycle.
 */
final class EngravingCart {

	/**
	 * Captures a valid, submitted engraving selection into the cart item's own data, hooked to
	 * `woocommerce_add_cart_item_data`. Silently ignored (no engraving data attached) when the
	 * product does not have engraving enabled, the checkbox was not submitted, or the submitted
	 * text is empty — self::adjust_cart_item_price()/display_cart_item_data()/
	 * self::add_order_item_meta() all no-op on a cart item with no engraving data.
	 *
	 * @param array $cart_item_data Cart item data being built.
	 * @param int   $product_id     Product ID being added to the cart.
	 * @param int   $variation_id   Variation ID being added to the cart (0 for a simple product).
	 * @return array
	 */
	public static function add_cart_item_data( array $cart_item_data, int $product_id, int $variation_id ): array {
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( ! $product ) {
			return $cart_item_data;
		}

		$config = ProductEngraving::config_for_product( $product );

		if ( ! $config ) {
			return $cart_item_data;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only selection state, only ever used below once validated against the product's own real engraving config (never persisted otherwise).
		$is_enabled     = isset( $_POST['solar_template_engraving_enabled'] );
		$submitted_text = isset( $_POST['solar_template_engraving_text'] ) ? sanitize_text_field( wp_unslash( $_POST['solar_template_engraving_text'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $is_enabled || '' === $submitted_text ) {
			return $cart_item_data;
		}

		$cart_item_data['solar_template_engraving'] = array(
			'text'      => mb_substr( $submitted_text, 0, $config['max_length'] ),
			'surcharge' => $config['price'],
		);

		// Without this, WooCommerce would merge this line with any other cart item for the same
		// product/variation (its default behaviour for identical line items) even though the two
		// carry different engraving text.
		$cart_item_data['unique_key'] = wp_generate_uuid4();

		return $cart_item_data;
	}

	/**
	 * Adds each engraved cart item's surcharge to its price, hooked to
	 * `woocommerce_before_calculate_totals`. Recomputed from the underlying product/variation's own
	 * current price every time (never the cart item's own already-adjusted price) so repeated totals
	 * recalculations within the same request never compound the surcharge on top of itself.
	 *
	 * @param \WC_Cart $cart Cart being recalculated.
	 * @return void
	 */
	public static function adjust_cart_item_price( \WC_Cart $cart ): void {
		foreach ( $cart->get_cart() as $cart_item ) {
			if ( empty( $cart_item['solar_template_engraving']['surcharge'] ) ) {
				continue;
			}

			$base_product = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );

			if ( ! $base_product ) {
				continue;
			}

			$cart_item['data']->set_price( (float) $base_product->get_price() + (float) $cart_item['solar_template_engraving']['surcharge'] );
		}
	}

	/**
	 * Shows the engraving text as an extra line on the cart/checkout item, hooked to
	 * `woocommerce_get_item_data`.
	 *
	 * @param array $item_data Existing extra line items.
	 * @param array $cart_item The cart item being displayed.
	 * @return array
	 */
	public static function display_cart_item_data( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item['solar_template_engraving']['text'] ) ) {
			return $item_data;
		}

		$item_data[] = array(
			'name'  => __( 'Engraving', 'solar-template' ),
			'value' => wc_clean( $cart_item['solar_template_engraving']['text'] ),
		);

		return $item_data;
	}

	/**
	 * Persists the engraving text onto the resulting order line item, hooked to
	 * `woocommerce_checkout_create_order_line_item` — the acceptance criterion this whole class
	 * exists for: the submitted value ends up in the order, not just the cart.
	 *
	 * @param \WC_Order_Item_Product $item          Order line item being built.
	 * @param string                 $cart_item_key Cart item key (unused, part of the hook's signature).
	 * @param array                  $values        The source cart item.
	 * @return void
	 */
	public static function add_order_item_meta( \WC_Order_Item_Product $item, string $cart_item_key, array $values ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $cart_item_key is part of this hook's fixed signature.
		if ( empty( $values['solar_template_engraving']['text'] ) ) {
			return;
		}

		$item->add_meta_data( __( 'Engraving', 'solar-template' ), $values['solar_template_engraving']['text'] );
	}
}
