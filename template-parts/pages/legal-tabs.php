<?php
/**
 * Created: 2026-09-26 21:38 CEST
 * Role: Legal page tab strip template-part (template-parts/pages/legal-tabs.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the C.G.V./Privacy Policy/Legal Notice/Cookies tab strip from the plain,
 *          already-resolved list built by Solar_Template\Pages\LegalPageController::tabs().
 *
 * Expected `$args` keys:
 * - tabs (array<int, array{slug: string, label: string, url: string, active: bool}>)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = wp_parse_args( $args ?? array(), array( 'tabs' => array() ) );

if ( empty( $data['tabs'] ) ) {
	return;
}
?>
<nav class="legal-tabs" aria-label="<?php esc_attr_e( 'Legal pages', 'solar-template' ); ?>">
	<div class="legal-tabs__inner">
		<?php foreach ( $data['tabs'] as $legal_tab ) : ?>
			<a
				href="<?php echo esc_url( $legal_tab['url'] ); ?>"
				class="legal-tabs__link<?php echo $legal_tab['active'] ? ' legal-tabs__link--active' : ''; ?>"
				<?php echo $legal_tab['active'] ? ' aria-current="page"' : ''; ?>
			>
				<?php echo esc_html( $legal_tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</nav>
