<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Reusable product card template-part (template-parts/product-card.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a single product card (image, corner badge, wishlist button, "add to cart"
 *          hover overlay, category, name, price, color swatches) from a plain `$args` array, so
 *          it can be reused by the catalog grid, the "similar products" section, etc. without
 *          duplicating markup. This step wires it with fake data only: `$args` is shaped like
 *          WooCommerce data on purpose (name/price/regular_price/currency_symbol) but is not read
 *          from a `WC_Product` yet — that mapping is added once the catalog is wired to real
 *          products, at which point the price formatting below is expected to be replaced by
 *          `wc_price()`.
 *
 * Expected `$args` keys (all optional, see $defaults below):
 * - product_id (int) real WC_Product ID, used by the wishlist toggle button's AJAX request
 * - image_url (string|null), image_alt (string)
 * - permalink (string) product page URL
 * - badge (array{type: string, label: string}|null) type is one of new|exclusive|sale
 * - category (string), name (string)
 * - price (float|null), regular_price (float|null), currency_symbol (string)
 * - discount_percent (int|null)
 * - in_wishlist (bool)
 * - swatches (string[]) hex colors
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'product_id'       => 0,
	'image_url'        => null,
	'image_alt'        => '',
	'permalink'        => '#',
	'badge'            => null,
	'category'         => '',
	'name'             => '',
	'price'            => null,
	'regular_price'    => null,
	'currency_symbol'  => '€',
	'discount_percent' => null,
	'in_wishlist'      => false,
	'swatches'         => array(),
);

$product = wp_parse_args( $args ?? array(), $defaults );

use Solar_Template\Support\PriceFormatter;
?>
<div class="product-card">
	<div class="product-card__media">
		<?php if ( $product['image_url'] ) : ?>
			<img
				class="product-card__image"
				src="<?php echo esc_url( $product['image_url'] ); ?>"
				alt="<?php echo esc_attr( $product['image_alt'] ); ?>"
				loading="lazy"
			/>
		<?php endif; ?>

		<?php if ( ! empty( $product['badge']['type'] ) && ! empty( $product['badge']['label'] ) ) : ?>
			<span class="product-card__badge badge badge--<?php echo esc_attr( $product['badge']['type'] ); ?>">
				<?php echo esc_html( $product['badge']['label'] ); ?>
			</span>
		<?php endif; ?>

		<button
			type="button"
			class="product-card__wishlist<?php echo $product['in_wishlist'] ? ' is-active' : ''; ?>"
			aria-label="<?php echo esc_attr__( 'Add to wishlist', 'solar-template' ); ?>"
			aria-pressed="<?php echo $product['in_wishlist'] ? 'true' : 'false'; ?>"
			data-product-id="<?php echo esc_attr( (string) $product['product_id'] ); ?>"
		>
			<svg viewBox="0 0 24 24" fill="<?php echo $product['in_wishlist'] ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<path d="M12 21s-7.5-4.6-10-9.2C0.3 8.4 1.9 4.8 5.3 4.1c2-.4 3.9.5 5 2.1 1.1-1.6 3-2.5 5-2.1 3.4.7 5 4.3 3.3 7.7C19.5 16.4 12 21 12 21z" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>

		<button type="button" class="product-card__cta">
			<?php esc_html_e( 'Add to cart', 'solar-template' ); ?>
		</button>
	</div>

	<a class="product-card__body" href="<?php echo esc_url( $product['permalink'] ); ?>">
		<?php if ( '' !== $product['category'] ) : ?>
			<span class="product-card__category"><?php echo esc_html( $product['category'] ); ?></span>
		<?php endif; ?>

		<span class="product-card__name"><?php echo esc_html( $product['name'] ); ?></span>

		<?php if ( null !== $product['price'] ) : ?>
			<p class="product-card__price">
				<?php echo wp_kses_post( PriceFormatter::format( (float) $product['price'], $product['currency_symbol'] ) ); ?>
				<?php if ( null !== $product['regular_price'] && $product['regular_price'] > $product['price'] ) : ?>
					<span class="product-card__price-original">
						<?php echo wp_kses_post( PriceFormatter::format( (float) $product['regular_price'], $product['currency_symbol'] ) ); ?>
					</span>
					<?php if ( null !== $product['discount_percent'] ) : ?>
						<span class="badge badge--discount">
							<?php
							printf(
								/* translators: %d: discount percentage. */
								esc_html__( '−%d%%', 'solar-template' ),
								(int) $product['discount_percent']
							);
							?>
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $product['swatches'] ) ) : ?>
			<span class="product-card__swatches">
				<?php foreach ( $product['swatches'] as $swatch_color ) : ?>
					<span class="product-card__swatch" style="background-color: <?php echo esc_attr( $swatch_color ); ?>"></span>
				<?php endforeach; ?>
			</span>
		<?php endif; ?>
	</a>
</div>
