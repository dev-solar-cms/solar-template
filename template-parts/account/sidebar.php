<?php
/**
 * Created: 2026-09-26 14:30 CEST
 * Role: My Account sidebar template-part (template-parts/account/sidebar.php).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the sidebar's user-info block and navigation, from plain view-model arrays built
 *          by Solar_Template\Account\AccountProfile/AccountNav. Used by
 *          woocommerce/myaccount/navigation.php.
 *
 * Expected `$args` keys:
 * - user (array{name: string, email: string, initial: string})
 * - nav_items (array<int, array{slug: string, label: string, url: string, icon: string, is_active: bool, is_logout: bool}>)
 *
 * @package Solar_Template
 * @var array $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'user'      => array(
		'name'    => '',
		'email'   => '',
		'initial' => '',
	),
	'nav_items' => array(),
);

$data = wp_parse_args( $args ?? array(), $defaults );
?>
<div class="account-sidebar">
	<div class="account-sidebar__user">
		<div class="account-avatar"><?php echo esc_html( $data['user']['initial'] ); ?></div>
		<div class="account-sidebar__name"><?php echo esc_html( $data['user']['name'] ); ?></div>
		<div class="account-sidebar__email"><?php echo esc_html( $data['user']['email'] ); ?></div>
	</div>

	<nav class="account-nav" aria-label="<?php esc_attr_e( 'My account navigation', 'solar-template' ); ?>">
		<?php foreach ( $data['nav_items'] as $item ) : ?>
			<a
				href="<?php echo esc_url( $item['url'] ); ?>"
				class="account-nav__item<?php echo $item['is_active'] ? ' is-active' : ''; ?><?php echo $item['is_logout'] ? ' account-nav__item--logout' : ''; ?>"
				<?php echo $item['is_active'] ? 'aria-current="page"' : ''; ?>
			>
				<span class="account-nav__icon" aria-hidden="true">
				<?php
				echo wp_kses(
					$item['icon'],
					array(
						'svg'      => array(
							'viewbox'        => true,
							'fill'           => true,
							'stroke'         => true,
							'stroke-width'   => true,
							'stroke-linecap' => true,
						),
						'rect'     => array(
							'x'      => true,
							'y'      => true,
							'width'  => true,
							'height' => true,
							'rx'     => true,
						),
						'path'     => array( 'd' => true ),
						'line'     => array(
							'x1' => true,
							'y1' => true,
							'x2' => true,
							'y2' => true,
						),
						'polyline' => array( 'points' => true ),
						'circle'   => array(
							'cx' => true,
							'cy' => true,
							'r'  => true,
						),
					)
				);
				?>
				</span>
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</div>
