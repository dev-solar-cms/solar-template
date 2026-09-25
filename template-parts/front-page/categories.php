<?php
/**
 * Created: 2026-09-25 10:30 CEST
 * Role: Front page categories section template-part (template-parts/front-page/categories.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the asymmetric dark grid of WooCommerce product categories (see
 *          Solar_Template\FrontPage\Categories::categories()). Renders nothing when there is no
 *          category to show (WooCommerce missing/inactive, or no populated category yet).
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Solar_Template\FrontPage\Categories;

$categories = Categories::categories();

if ( empty( $categories ) ) {
	return;
}

$section_heading = Categories::heading();
?>
<section class="home-categories">
	<div class="home-categories__inner">
		<div class="home-categories__header">
			<?php if ( '' !== $section_heading['eyebrow'] ) : ?>
				<p class="home-categories__eyebrow"><?php echo esc_html( $section_heading['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h2 class="home-categories__heading"><?php echo esc_html( $section_heading['heading'] ); ?></h2>
		</div>

		<div class="home-categories__grid">
			<?php foreach ( $categories as $category_index => $category ) : ?>
				<a
					class="home-categories__item<?php echo 0 === $category_index ? ' home-categories__item--primary' : ''; ?>"
					href="<?php echo esc_url( $category['url'] ); ?>"
					<?php if ( $category['image_url'] ) : ?>
						style="background-image: url('<?php echo esc_url( $category['image_url'] ); ?>')"
					<?php endif; ?>
				>
					<span class="home-categories__item-overlay"></span>
					<span class="home-categories__item-content">
						<span class="home-categories__item-index">
							<?php echo esc_html( sprintf( '%02d', $category_index + 1 ) ); ?>
						</span>
						<span class="home-categories__item-name"><?php echo esc_html( $category['name'] ); ?></span>
						<span class="home-categories__item-link">
							<?php esc_html_e( 'Discover', 'solar-template' ); ?> <span aria-hidden="true">→</span>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
