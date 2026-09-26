<?php
/**
 * Created: 2026-09-26 10:20 CEST
 * Role: Product page reviews tab data (Solar_Template\Product).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Resolve the product page's "Reviews" tab from WooCommerce's real, native review data —
 *          the product's own rating summary/distribution, its approved reviews (author, rating,
 *          date, verified-owner badge), whether reviews are open at all, and the arguments for a
 *          real, functioning `comment_form()` submission — reimplemented from
 *          `woocommerce/templates/single-product-reviews.php` with the theme's own translation
 *          catalog instead of WooCommerce's core text domain (same convention as ProductStock).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the product page's reviews summary/list and review-form arguments.
 */
final class ProductReviews {

	/**
	 * Whether the "Reviews" tab should render at all for this product.
	 *
	 * @param \WC_Product $product Product to check.
	 * @return bool
	 */
	public static function enabled( \WC_Product $product ): bool {
		return comments_open( $product->get_id() );
	}

	/**
	 * Whether the current visitor is allowed to submit a review — mirrors WooCommerce's own
	 * "verified owners only" store setting.
	 *
	 * @param \WC_Product $product Product to check.
	 * @return bool
	 */
	public static function can_submit( \WC_Product $product ): bool {
		return 'no' === get_option( 'woocommerce_review_rating_verification_required' )
			|| wc_customer_bought_product( '', get_current_user_id(), $product->get_id() );
	}

	/**
	 * @param \WC_Product $product Product to summarize the rating of.
	 * @return array{
	 *     average: float,
	 *     rounded_stars: int,
	 *     count: int,
	 *     distribution: array<int, array{stars: int, count: int, percent: int}>
	 * } `distribution` is ordered 5 stars down to 1.
	 */
	public static function summary( \WC_Product $product ): array {
		$average = (float) $product->get_average_rating();
		$count   = (int) $product->get_review_count();
		$counts  = $product->get_rating_counts();

		$distribution = array();

		for ( $stars = 5; $stars >= 1; $stars-- ) {
			$stars_count    = (int) ( $counts[ $stars ] ?? 0 );
			$distribution[] = array(
				'stars'   => $stars,
				'count'   => $stars_count,
				'percent' => $count > 0 ? (int) round( ( $stars_count / $count ) * 100 ) : 0,
			);
		}

		return array(
			'average'       => $average,
			'rounded_stars' => (int) round( $average ),
			'count'         => $count,
			'distribution'  => $distribution,
		);
	}

	/**
	 * Returns the product's approved reviews, newest first.
	 *
	 * @param \WC_Product $product Product to read reviews for.
	 * @param int         $limit   Maximum number of reviews to return; 0 uses the filterable default.
	 * @return array<int, array{author: string, avatar: string, rating: int, date: string, verified: bool, content: string}>
	 */
	public static function list( \WC_Product $product, int $limit = 0 ): array {
		if ( $limit <= 0 ) {
			/**
			 * Filters how many approved reviews the product page's reviews tab renders.
			 *
			 * @param int $limit Default number of reviews to show.
			 */
			$limit = (int) apply_filters( 'solar_template_product_reviews_limit', 10 );
		}

		$comments = get_comments(
			array(
				'post_id' => $product->get_id(),
				'status'  => 'approve',
				'parent'  => 0,
				'number'  => $limit,
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);

		return array_map( array( self::class, 'map_comment' ), $comments );
	}

	/**
	 * @param \WP_Comment $comment Review comment to map.
	 * @return array{author: string, avatar: string, rating: int, date: string, verified: bool, content: string}
	 */
	private static function map_comment( \WP_Comment $comment ): array {
		return array(
			'author'   => $comment->comment_author,
			'avatar'   => get_avatar( $comment, 44 ),
			'rating'   => (int) get_comment_meta( $comment->comment_ID, 'rating', true ),
			'date'     => get_comment_date( wc_date_format(), $comment ),
			'verified' => 'yes' === get_option( 'woocommerce_review_rating_verification_label' )
				&& wc_review_is_from_verified_owner( $comment->comment_ID ),
			'content'  => get_comment_text( $comment ),
		);
	}

	/**
	 * Builds the arguments for the real, native `comment_form()` call that submits a review —
	 * same shape as WooCommerce's own `woocommerce/templates/single-product-reviews.php`, with the
	 * theme's own translated strings and a title that reflects whether the product has any review
	 * yet.
	 *
	 * @param \WC_Product $product Product being reviewed.
	 * @return array Arguments ready for `comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $args ), $product->get_id() )`.
	 */
	public static function comment_form_args( \WC_Product $product ): array {
		$args = array(
			'title_reply'   => $product->get_review_count() > 0
				? __( 'Add a review', 'solar-template' )
				: sprintf(
					/* translators: %s: product name. */
					__( 'Be the first to review "%s"', 'solar-template' ),
					$product->get_name()
				),
			'label_submit'  => __( 'Submit review', 'solar-template' ),
			'comment_field' => self::rating_field_markup() . self::comment_field_markup(),
		);

		$account_page_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';

		if ( $account_page_url ) {
			$args['must_log_in'] = '<p class="must-log-in">' . sprintf(
				/* translators: 1: opening link tag, 2: closing link tag. */
				esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'solar-template' ),
				'<a href="' . esc_url( $account_page_url ) . '">',
				'</a>'
			) . '</p>';
		}

		return $args;
	}

	/**
	 * Builds the review form's star rating `<select>`, or an empty string when the store has
	 * disabled review ratings.
	 *
	 * @return string
	 */
	private static function rating_field_markup(): string {
		if ( ! wc_review_ratings_enabled() ) {
			return '';
		}

		$required = wc_review_ratings_required();

		return '<p class="comment-form-rating"><label for="rating">'
			. esc_html__( 'Your rating', 'solar-template' )
			. ( $required ? '&nbsp;<span class="required">*</span>' : '' )
			. '</label><select name="rating" id="rating"' . ( $required ? ' required' : '' ) . '>'
			. '<option value="">' . esc_html__( 'Rate…', 'solar-template' ) . '</option>'
			. '<option value="5">' . esc_html__( 'Perfect', 'solar-template' ) . '</option>'
			. '<option value="4">' . esc_html__( 'Good', 'solar-template' ) . '</option>'
			. '<option value="3">' . esc_html__( 'Average', 'solar-template' ) . '</option>'
			. '<option value="2">' . esc_html__( 'Not that bad', 'solar-template' ) . '</option>'
			. '<option value="1">' . esc_html__( 'Very poor', 'solar-template' ) . '</option>'
			. '</select></p>';
	}

	/**
	 * Builds the review form's own textarea field.
	 *
	 * @return string
	 */
	private static function comment_field_markup(): string {
		return '<p class="comment-form-comment"><label for="comment">'
			. esc_html__( 'Your review', 'solar-template' )
			. '&nbsp;<span class="required">*</span></label>'
			. '<textarea id="comment" name="comment" cols="45" rows="6" required></textarea></p>';
	}
}
