<?php
/**
 * Created: 2026-09-25 16:32 CEST
 * Role: Product panel template-part (template-parts/single-product/panel.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the product page's right-hand, sticky panel: badges, title, rating/stock status,
 *          short description, trust badges and the shipping/size/care accordion — everything fed
 *          as plain, already-computed data (single-product.php calls
 *          Solar_Template\Product\ProductBadges/ProductStock/ProductPanel), same convention as
 *          template-parts/single-product/gallery.php. Grows further (variations/price, engraving)
 *          alongside single-product.php.
 *
 * @package Solar_Template
 * @var array $args {
 *     @type string $title             Product title.
 *     @type array  $badges            See Solar_Template\Product\ProductBadges::for_product().
 *     @type array  $rating            See Solar_Template\Product\ProductPanel::rating_summary().
 *     @type array  $stock             See Solar_Template\Product\ProductStock::for_product().
 *     @type string $short_description Pre-filtered, already-safe HTML.
 *     @type array  $trust_badges      See Solar_Template\Product\ProductPanel::trust_badges().
 *     @type array  $accordion_sections See Solar_Template\Product\ProductPanel::accordion_sections().
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		'trust_badges'       => array(),
		'accordion_sections' => array(),
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

	<?php if ( '' !== $panel['short_description'] ) : ?>
		<div class="product-panel__description"><?php echo wp_kses_post( $panel['short_description'] ); ?></div>
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
