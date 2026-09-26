<?php
/**
 * Created: 2026-09-26 10:35 CEST
 * Role: Product page tabs template-part (template-parts/single-product/tabs.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the Description/Reviews/Specifications tabs below the product panel, following
 *          the WAI-ARIA tabs pattern (role="tablist"/"tab"/"tabpanel", arrow-key navigation) so
 *          switching tabs works identically at the mouse and the keyboard. Fed plain, already-
 *          computed data (single-product.php calls Solar_Template\Product\ProductDescription/
 *          ProductReviews/ProductSpecifications), same convention as
 *          template-parts/single-product/gallery.php/panel.php. A tab is only rendered when it has
 *          real content (a product with no description, no open reviews and no attribute renders
 *          nothing at all).
 *
 * @package Solar_Template
 * @var array $args {
 *     @type int   $product_id     Product ID (needed by `comment_form()`'s own second argument).
 *     @type array $description    { @type string $content Filtered HTML. @type array|null $image See
 *                                  Solar_Template\Product\ProductGallery::images(). }
 *     @type array $reviews        { @type bool  $enabled           See ProductReviews::enabled().
 *                                  @type bool  $can_submit         See ProductReviews::can_submit().
 *                                  @type array $summary            See ProductReviews::summary().
 *                                  @type array $list               See ProductReviews::list().
 *                                  @type array $comment_form_args  See ProductReviews::comment_form_args(). }
 *     @type array $specifications See Solar_Template\Product\ProductSpecifications::rows().
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_tabs = wp_parse_args(
	$args ?? array(),
	array(
		'product_id'     => 0,
		'description'    => array(
			'content' => '',
			'image'   => null,
		),
		'reviews'        => array(
			'enabled'           => false,
			'can_submit'        => false,
			'summary'           => array(
				'average'       => 0.0,
				'rounded_stars' => 0,
				'count'         => 0,
				'distribution'  => array(),
			),
			'list'              => array(),
			'comment_form_args' => array(),
		),
		'specifications' => array(),
	)
);

$available_tabs = array();

if ( '' !== $product_tabs['description']['content'] ) {
	$available_tabs['description'] = __( 'Description', 'solar-template' );
}

if ( $product_tabs['reviews']['enabled'] ) {
	$review_count              = $product_tabs['reviews']['summary']['count'];
	$available_tabs['reviews'] = sprintf(
		/* translators: %d: number of reviews. */
		_n( 'Review (%d)', 'Reviews (%d)', $review_count, 'solar-template' ),
		$review_count
	);
}

if ( ! empty( $product_tabs['specifications'] ) ) {
	$available_tabs['specifications'] = __( 'Specifications', 'solar-template' );
}

if ( empty( $available_tabs ) ) {
	return;
}

$active_tab = array_key_first( $available_tabs );
?>
<div class="product-tabs" id="reviews">
	<div class="product-tabs__inner">
		<div class="product-tabs__list" role="tablist" aria-label="<?php echo esc_attr__( 'Product information', 'solar-template' ); ?>">
			<?php foreach ( $available_tabs as $tab_key => $tab_label ) : ?>
				<button
					type="button"
					role="tab"
					id="product-tab-<?php echo esc_attr( $tab_key ); ?>"
					aria-controls="product-tabpanel-<?php echo esc_attr( $tab_key ); ?>"
					aria-selected="<?php echo $tab_key === $active_tab ? 'true' : 'false'; ?>"
					tabindex="<?php echo $tab_key === $active_tab ? '0' : '-1'; ?>"
					class="product-tabs__tab<?php echo $tab_key === $active_tab ? ' is-active' : ''; ?>"
					data-tab="<?php echo esc_attr( $tab_key ); ?>"
				><?php echo esc_html( $tab_label ); ?></button>
			<?php endforeach; ?>
		</div>

		<?php if ( isset( $available_tabs['description'] ) ) : ?>
			<div
				role="tabpanel"
				id="product-tabpanel-description"
				aria-labelledby="product-tab-description"
				class="product-tabs__panel"
				<?php echo 'description' === $active_tab ? '' : 'hidden'; ?>
			>
				<div class="product-tabs__description">
					<div class="product-tabs__description-content"><?php echo wp_kses_post( $product_tabs['description']['content'] ); ?></div>
					<?php if ( null !== $product_tabs['description']['image'] ) : ?>
						<div class="product-tabs__description-media">
							<img
								src="<?php echo esc_url( $product_tabs['description']['image']['full'] ); ?>"
								alt="<?php echo esc_attr( $product_tabs['description']['image']['alt'] ); ?>"
								loading="lazy"
							/>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( isset( $available_tabs['reviews'] ) ) : ?>
			<?php $reviews = $product_tabs['reviews']; ?>
			<div
				role="tabpanel"
				id="product-tabpanel-reviews"
				aria-labelledby="product-tab-reviews"
				class="product-tabs__panel"
				<?php echo 'reviews' === $active_tab ? '' : 'hidden'; ?>
			>
				<div class="product-tabs__reviews">
					<div class="product-tabs__reviews-summary">
						<div class="product-tabs__reviews-score">
							<div class="product-tabs__reviews-average"><?php echo esc_html( number_format_i18n( $reviews['summary']['average'], 1 ) ); ?></div>
							<div class="product-tabs__reviews-stars" aria-hidden="true">
								<?php for ( $star_position = 1; $star_position <= 5; $star_position++ ) : ?>
									<?php echo $star_position <= $reviews['summary']['rounded_stars'] ? '&#9733;' : '&#9734;'; ?>
								<?php endfor; ?>
							</div>
							<div class="product-tabs__reviews-count">
								<?php
								printf(
									/* translators: %d: number of verified reviews. */
									esc_html( _n( 'Based on %d verified review', 'Based on %d verified reviews', $reviews['summary']['count'], 'solar-template' ) ),
									(int) $reviews['summary']['count']
								);
								?>
							</div>
						</div>

						<?php if ( $reviews['summary']['count'] > 0 ) : ?>
							<div class="product-tabs__reviews-distribution">
								<?php foreach ( $reviews['summary']['distribution'] as $distribution_row ) : ?>
									<div class="product-tabs__reviews-bar-row">
										<span class="product-tabs__reviews-bar-label">
											<?php
											printf(
												/* translators: %d: star count, from 1 to 5. */
												esc_html__( '%d★', 'solar-template' ),
												(int) $distribution_row['stars']
											);
											?>
										</span>
										<span class="product-tabs__reviews-bar-track">
											<span class="product-tabs__reviews-bar-fill" style="width: <?php echo esc_attr( (string) $distribution_row['percent'] ); ?>%;"></span>
										</span>
										<span class="product-tabs__reviews-bar-percent"><?php echo esc_html( $distribution_row['percent'] ); ?>%</span>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $reviews['list'] ) ) : ?>
						<ol class="product-tabs__reviews-list">
							<?php foreach ( $reviews['list'] as $review ) : ?>
								<li class="product-tabs__review">
									<div class="product-tabs__review-header">
										<?php if ( '' !== $review['avatar'] ) : ?>
											<div class="product-tabs__review-avatar"><?php echo wp_kses_post( $review['avatar'] ); ?></div>
										<?php endif; ?>
										<div>
											<div class="product-tabs__review-author"><?php echo esc_html( $review['author'] ); ?></div>
											<div class="product-tabs__review-meta">
												<?php if ( $review['rating'] > 0 ) : ?>
													<span
														class="product-tabs__review-rating"
														aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating out of 5 stars. */ __( '%d out of 5 stars', 'solar-template' ), $review['rating'] ) ); ?>"
													>
														<?php for ( $star_position = 1; $star_position <= 5; $star_position++ ) : ?>
															<?php echo $star_position <= $review['rating'] ? '&#9733;' : '&#9734;'; ?>
														<?php endfor; ?>
													</span>
												<?php endif; ?>
												<span class="product-tabs__review-date">· <?php echo esc_html( $review['date'] ); ?></span>
												<?php if ( $review['verified'] ) : ?>
													<span class="product-tabs__review-badge"><?php esc_html_e( 'Verified purchase', 'solar-template' ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</div>
									<p class="product-tabs__review-content"><?php echo esc_html( $review['content'] ); ?></p>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php else : ?>
						<p class="product-tabs__reviews-empty"><?php esc_html_e( 'There are no reviews yet.', 'solar-template' ); ?></p>
					<?php endif; ?>

					<?php if ( $reviews['can_submit'] ) : ?>
						<details class="product-tabs__review-form">
							<summary class="product-tabs__review-form-toggle"><?php esc_html_e( 'Leave a review', 'solar-template' ); ?></summary>
							<div class="product-tabs__review-form-body">
								<?php
								comment_form(
									apply_filters( 'woocommerce_product_review_comment_form_args', $reviews['comment_form_args'] ),
									$product_tabs['product_id']
								);
								?>
							</div>
						</details>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( isset( $available_tabs['specifications'] ) ) : ?>
			<div
				role="tabpanel"
				id="product-tabpanel-specifications"
				aria-labelledby="product-tab-specifications"
				class="product-tabs__panel"
				<?php echo 'specifications' === $active_tab ? '' : 'hidden'; ?>
			>
				<div class="product-tabs__specs">
					<?php foreach ( $product_tabs['specifications'] as $specification_row ) : ?>
						<div class="product-tabs__specs-row">
							<div class="product-tabs__specs-label"><?php echo esc_html( $specification_row['label'] ); ?></div>
							<div class="product-tabs__specs-value"><?php echo esc_html( $specification_row['value'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
