<?php
/**
 * Created: 2026-09-26 21:30 CEST
 * Template Name: Legal Page
 * Role: Selectable "Legal Page" page template (page-templates/legal-page.php), assignable from the
 *       Page Attributes panel in the block editor.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render a legal page (Terms & Conditions / Privacy Policy / Legal Notice / Cookies) with
 *          a tab strip linking the site's own equivalent pages and a sticky table of contents
 *          auto-generated from the page's own <h2> headings. Per DECISIONS.md's MVC split, all
 *          logic lives in Solar_Template\Pages\LegalPageController — this file only displays what
 *          it returns.
 *
 * @package Solar_Template
 */

use Solar_Template\Pages\LegalPageController;
use Solar_Template\Support\StoreLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	ob_start();
	the_content();
	$raw_content = ob_get_clean();

	$processed  = LegalPageController::annotate_headings( $raw_content );
	$legal_tabs = LegalPageController::tabs( get_post()->post_name );
	?>

	<section class="legal-page-hero">
		<div class="legal-page-hero__inner">
			<div class="legal-page-hero__eyebrow"><?php esc_html_e( 'Legal information', 'solar-template' ); ?></div>
			<h1 class="legal-page-hero__title"><?php the_title(); ?></h1>
			<p class="legal-page-hero__updated">
				<?php
				printf(
					/* translators: %s: last modified date. */
					esc_html__( 'Last updated: %s', 'solar-template' ),
					esc_html( get_the_modified_date() )
				);
				?>
			</p>
		</div>
	</section>

	<?php get_template_part( 'template-parts/pages/legal-tabs', null, array( 'tabs' => $legal_tabs ) ); ?>

	<div class="legal-page">
		<div class="legal-page__inner">
			<div class="legal-page__grid">
				<?php
				get_template_part(
					'template-parts/pages/legal-toc',
					null,
					array(
						'headings'    => $processed['headings'],
						'contact_url' => StoreLinks::page_url_by_slug( 'contact' ),
					)
				);
				?>

				<div class="legal-page__content">
					<?php echo $processed['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress' own the_content pipeline output, already sanitized by wp_kses_post on save. ?>
				</div>
			</div>
		</div>
	</div>

	<?php
endwhile;

get_footer();
