<?php
/**
 * Created: 2026-09-25 09:30 CEST
 * Role: Front page template (front-page.php), used by WordPress for the site's home page
 *       regardless of the "reading" setting (a static page or the latest posts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assemble the home page's sections, each a self-contained template-part under
 *          template-parts/front-page/. Order and visibility come from
 *          Solar_Template\Admin\HomeSettings (the administration "Home page" tab) rather than a
 *          fixed list, so reordering/disabling a section there actually changes what renders here.
 *
 * @package Solar_Template
 */

use Solar_Template\Admin\HomeSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="front-page">
	<?php foreach ( HomeSettings::visible_sections() as $section ) : ?>
		<?php get_template_part( $section['template_part'] ); ?>
	<?php endforeach; ?>
</main>

<?php
get_footer();
