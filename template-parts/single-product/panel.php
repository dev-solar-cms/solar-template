<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Product panel template-part (template-parts/single-product/panel.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product page's right-hand, sticky panel: badges, title, rating/stock status,
 *          price, Color/Size selectors + custom engraving + "Add to cart" form, a Wishlist/Share
 *          action row, short description, trust badges and the shipping/size/care accordion —
 *          everything fed as plain, already-computed data (single-product.php calls
 *          Solar_Template\Product\ProductBadges/ProductStock/ProductPanel/ProductCartForm/
 *          ProductEngraving), same convention as template-parts/single-product/gallery.php. The
 *          price display and the "Add to cart" button stay separate elements (rather than the
 *          mockup's single "Add · {price}" button text) so assets/js/product.js only ever
 *          recomputes one canonical price location. Selecting a Color/Size resolves the matching
 *          real WooCommerce variation (initProductVariations()), updating the price, the hidden
 *          `variation_id`, and the "Add to cart" button's disabled state accordingly; toggling
 *          engraving on adds its surcharge to that same price. The Wishlist button reuses the
 *          product card component's own toggle button/behaviour (`.product-card__wishlist`,
 *          assets/js/account.js's `initWishlistToggle()`, already wired site-wide); the Share
 *          button reuses the blog article page's own share button/behaviour
 *          (`.article-share-button`, assets/js/blog.js's `initArticleShare()`, also already wired
 *          site-wide) — no new JavaScript needed for either.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type string     $title             Product title.
 *     @type array      $badges            See Solar_Template\Product\ProductBadges::for_product().
 *     @type array      $rating            See Solar_Template\Product\ProductPanel::rating_summary().
 *     @type array      $stock             See Solar_Template\Product\ProductStock::for_product().
 *     @type string     $short_description Pre-filtered, already-safe HTML.
 *     @type array      $cart_form         See Solar_Template\Product\ProductCartForm::for_product().
 *     @type array|null $engraving         See Solar_Template\Product\ProductEngraving::config_for_product().
 *     @type array      $trust_badges      See Solar_Template\Product\ProductPanel::trust_badges().
 *     @type array      $accordion_sections See Solar_Template\Product\ProductPanel::accordion_sections().
 *     @type array      $wishlist_share    See Solar_Template\Product\ProductPanel::wishlist_share().
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\Product\ProductEngraving;

$panel = wp_parse_args(
	$args ?? array(),
	array(
		'title'              => '',
		'badges'             => array(),
		'rating'             => array(
			'average'       => 0.0,
			'rounded_stars' => 0,
			'review_count'  => 0,
		),
		'stock'              => array(
			'label'    => '',
			'modifier' => 'in-stock',
		),
		'short_description'  => '',
		'cart_form'          => array(
			'product_id'       => 0,
			'is_variable'      => false,
			'price_html'       => '',
			'can_add_to_cart'  => false,
			'variation_groups' => array(),
		),
		'engraving'          => null,
		'trust_badges'       => array(),
		'accordion_sections' => array(),
		'wishlist_share'     => array(
			'product_id'  => 0,
			'in_wishlist' => false,
			'share_url'   => '',
			'share_title' => '',
		),
	)
);
?>
<div class="product-panel">
	<?php if ( ! empty( $panel['badges'] ) ) : ?>
		<div class="product-panel__badges">
			<?php foreach ( $panel['badges'] as $badge ) : ?>
				<span class="product-panel__badge product-panel__badge--<?php echo esc_attr( $badge['type'] ); ?>">
					<?php echo esc_html( $badge['label'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<h1 class="product-panel__title"><?php echo esc_html( $panel['title'] ); ?></h1>

	<div class="product-panel__meta">
		<span class="product-panel__stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating out of 5 stars. */ __( '%d out of 5 stars', 'solar-template' ), $panel['rating']['rounded_stars'] ) ); ?>">
			<?php for ( $star_position = 1; $star_position <= 5; $star_position++ ) : ?>
				<?php echo $star_position <= $panel['rating']['rounded_stars'] ? '&#9733;' : '&#9734;'; ?>
			<?php endfor; ?>
		</span>
		<span class="product-panel__rating-average"><?php echo esc_html( number_format_i18n( $panel['rating']['average'], 1 ) ); ?></span>
		<?php if ( $panel['rating']['review_count'] > 0 ) : ?>
			<a class="product-panel__reviews-link" href="#reviews">
				<?php
				printf(
					/* translators: %d: number of reviews. */
					esc_html( _n( '%d review →', '%d reviews →', $panel['rating']['review_count'], 'solar-template' ) ),
					(int) $panel['rating']['review_count']
				);
				?>
			</a>
		<?php endif; ?>
		<span class="product-panel__stock product-panel__stock--<?php echo esc_attr( $panel['stock']['modifier'] ); ?>">
			<?php echo esc_html( $panel['stock']['label'] ); ?>
		</span>
	</div>

	<?php if ( '' !== $panel['cart_form']['price_html'] ) : ?>
		<div class="product-panel__price">
			<span class="product-panel__price-amount" data-product-price><?php echo wp_kses_post( $panel['cart_form']['price_html'] ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $panel['short_description'] ) : ?>
		<div class="product-panel__description"><?php echo wp_kses_post( $panel['short_description'] ); ?></div>
	<?php endif; ?>

	<?php if ( $panel['cart_form']['product_id'] ) : ?>
		<div class="product-panel__divider"></div>

		<?php if ( ! empty( $panel['cart_form']['variation_groups'] ) ) : ?>
			<div class="product-panel__variations">
				<?php foreach ( $panel['cart_form']['variation_groups'] as $variation_group ) : ?>
					<div class="product-panel__variation-group" data-attribute="<?php echo esc_attr( $variation_group['taxonomy'] ); ?>" data-type="<?php echo esc_attr( $variation_group['type'] ); ?>">
						<div class="product-panel__variation-label">
							<?php echo esc_html( $variation_group['label'] ); ?>
							<?php if ( 'color' === $variation_group['type'] ) : ?>
								<span class="product-panel__variation-value" data-selected-label></span>
							<?php endif; ?>
						</div>

						<?php if ( 'color' === $variation_group['type'] ) : ?>
							<div class="product-panel__swatches">
								<?php foreach ( $variation_group['options'] as $option ) : ?>
									<button
										type="button"
										class="product-panel__swatch<?php echo $option['available'] ? '' : ' is-unavailable'; ?>"
										data-value="<?php echo esc_attr( $option['slug'] ); ?>"
										data-label="<?php echo esc_attr( $option['name'] ); ?>"
										style="background-color: <?php echo esc_attr( $option['color'] ); ?>"
										title="<?php echo esc_attr( $option['name'] ); ?>"
										aria-label="<?php echo esc_attr( sprintf( /* translators: %s: color name. */ __( 'Color: %s', 'solar-template' ), $option['name'] ) ); ?>"
										<?php echo $option['available'] ? '' : ' disabled'; ?>
									></button>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<div class="product-panel__size-options">
								<?php foreach ( $variation_group['options'] as $option ) : ?>
									<button
										type="button"
										class="product-panel__size-option<?php echo $option['available'] ? '' : ' is-unavailable'; ?>"
										data-value="<?php echo esc_attr( $option['slug'] ); ?>"
										<?php echo $option['available'] ? '' : ' disabled'; ?>
									><?php echo esc_html( $option['name'] ); ?></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<p class="product-panel__variation-message" data-variation-message hidden></p>
		<?php endif; ?>

		<form class="product-panel__cart-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( get_permalink( $panel['cart_form']['product_id'] ) ); ?>">
			<?php foreach ( $panel['cart_form']['variation_groups'] as $variation_group ) : ?>
				<input type="hidden" name="attribute_<?php echo esc_attr( $variation_group['taxonomy'] ); ?>" value="" />
			<?php endforeach; ?>
			<?php if ( $panel['cart_form']['is_variable'] ) : ?>
				<input type="hidden" class="product-panel__variation-id" name="variation_id" value="0" />
			<?php endif; ?>

			<?php if ( null !== $panel['engraving'] ) : ?>
				<div class="product-panel__engraving">
					<div class="product-panel__engraving-header">
						<div>
							<div class="product-panel__engraving-title"><?php echo esc_html( ProductEngraving::label() ); ?></div>
							<div class="product-panel__engraving-subtitle">
								<?php
								printf(
									/* translators: %s: formatted engraving surcharge price. */
									esc_html__( 'Add your text · +%s', 'solar-template' ),
									wp_kses_post( wc_price( $panel['engraving']['price'] ) )
								);
								?>
							</div>
						</div>
						<label class="product-panel__engraving-switch">
							<input
								type="checkbox"
								name="solar_template_engraving_enabled"
								class="product-panel__engraving-toggle"
								data-surcharge="<?php echo esc_attr( (string) $panel['engraving']['price'] ); ?>"
							/>
							<span class="product-panel__engraving-track" aria-hidden="true"><span class="product-panel__engraving-knob"></span></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Enable custom engraving', 'solar-template' ); ?></span>
						</label>
					</div>
					<div class="product-panel__engraving-field">
						<?php if ( 'textarea' === ProductEngraving::field_type() ) : ?>
							<textarea
								name="solar_template_engraving_text"
								class="product-panel__engraving-input"
								maxlength="<?php echo esc_attr( (string) $panel['engraving']['max_length'] ); ?>"
								placeholder="<?php echo esc_attr( sprintf( /* translators: %d: maximum number of characters. */ __( 'Your engraving text (max. %d characters)', 'solar-template' ), $panel['engraving']['max_length'] ) ); ?>"
							></textarea>
						<?php else : ?>
							<input
								type="text"
								name="solar_template_engraving_text"
								class="product-panel__engraving-input"
								maxlength="<?php echo esc_attr( (string) $panel['engraving']['max_length'] ); ?>"
								placeholder="<?php echo esc_attr( sprintf( /* translators: %d: maximum number of characters. */ __( 'Your engraving text (max. %d characters)', 'solar-template' ), $panel['engraving']['max_length'] ) ); ?>"
							/>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="product-panel__quantity">
				<button type="button" class="product-panel__qty-decrease" aria-label="<?php echo esc_attr__( 'Decrease quantity', 'solar-template' ); ?>">&minus;</button>
				<input type="number" class="product-panel__qty-input" name="quantity" value="1" min="1" inputmode="numeric" aria-label="<?php echo esc_attr__( 'Quantity', 'solar-template' ); ?>" />
				<button type="button" class="product-panel__qty-increase" aria-label="<?php echo esc_attr__( 'Increase quantity', 'solar-template' ); ?>">+</button>
			</div>

			<button
				type="submit"
				name="add-to-cart"
				value="<?php echo esc_attr( $panel['cart_form']['product_id'] ); ?>"
				class="product-panel__add-to-cart"
				<?php echo $panel['cart_form']['can_add_to_cart'] ? '' : ' disabled'; ?>
			>
				<?php esc_html_e( 'Add to cart', 'solar-template' ); ?>
			</button>
		</form>
	<?php endif; ?>

	<?php if ( $panel['wishlist_share']['product_id'] ) : ?>
		<div class="product-panel__actions">
			<button
				type="button"
				class="product-panel__wishlist-button product-card__wishlist<?php echo $panel['wishlist_share']['in_wishlist'] ? ' is-active' : ''; ?>"
				aria-pressed="<?php echo $panel['wishlist_share']['in_wishlist'] ? 'true' : 'false'; ?>"
				data-product-id="<?php echo esc_attr( (string) $panel['wishlist_share']['product_id'] ); ?>"
			>
				<svg viewBox="0 0 24 24" fill="<?php echo $panel['wishlist_share']['in_wishlist'] ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<path d="M12 21s-7.5-4.6-10-9.2C0.3 8.4 1.9 4.8 5.3 4.1c2-.4 3.9.5 5 2.1 1.1-1.6 3-2.5 5-2.1 3.4.7 5 4.3 3.3 7.7C19.5 16.4 12 21 12 21z" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php echo $panel['wishlist_share']['in_wishlist'] ? esc_html__( 'Saved', 'solar-template' ) : esc_html__( 'Save', 'solar-template' ); ?>
			</button>
			<button
				type="button"
				class="product-panel__share-button article-share-button"
				data-share-title="<?php echo esc_attr( $panel['wishlist_share']['share_title'] ); ?>"
				data-share-url="<?php echo esc_url( $panel['wishlist_share']['share_url'] ); ?>"
				data-copied-label="<?php echo esc_attr__( 'Link copied!', 'solar-template' ); ?>"
			>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
					<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
					<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
				</svg>
				<?php esc_html_e( 'Share', 'solar-template' ); ?>
			</button>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $panel['trust_badges'] ) ) : ?>
		<div class="product-panel__trust">
			<?php foreach ( $panel['trust_badges'] as $trust_badge ) : ?>
				<div class="product-panel__trust-item">
					<span class="product-panel__trust-icon" aria-hidden="true"><?php echo esc_html( $trust_badge['icon'] ); ?></span>
					<span class="product-panel__trust-title"><?php echo esc_html( $trust_badge['title'] ); ?></span>
					<span class="product-panel__trust-subtitle"><?php echo esc_html( $trust_badge['subtitle'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $panel['accordion_sections'] ) ) : ?>
		<div class="product-panel__accordion">
			<?php foreach ( $panel['accordion_sections'] as $accordion_section ) : ?>
				<details class="product-panel__accordion-item">
					<summary class="product-panel__accordion-summary">
						<span><?php echo esc_html( $accordion_section['title'] ); ?></span>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
					</summary>
					<div class="product-panel__accordion-body"><?php echo esc_html( $accordion_section['body'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
