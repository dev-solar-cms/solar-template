<?php
/**
 * Created: 2026-09-26 15:05 CEST
 * Role: Regression test for template-parts/account/sidebar.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the account sidebar template-part with plain, already-computed data and assert
 *          on the resulting markup: user info block, nav item order/active state/icons.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class AccountSidebarTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/account/sidebar.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * The user info block and every nav item render, the active one flagged accordingly.
	 *
	 * @return void
	 */
	public function test_renders_user_info_and_nav_items(): void {
		$html = $this->render(
			array(
				'user'      => array(
					'name'    => 'Marie Dupont',
					'email'   => 'marie@example.test',
					'initial' => 'M',
				),
				'nav_items' => array(
					array(
						'slug'      => 'dashboard',
						'label'     => 'Dashboard',
						'url'       => 'https://example.test/my-account/',
						'icon'      => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/></svg>',
						'is_active' => true,
						'is_logout' => false,
					),
					array(
						'slug'      => 'orders',
						'label'     => 'Orders',
						'url'       => 'https://example.test/my-account/orders/',
						'icon'      => '<svg viewBox="0 0 24 24"><path d="M6 2 3 6"/></svg>',
						'is_active' => false,
						'is_logout' => false,
					),
					array(
						'slug'      => 'customer-logout',
						'label'     => 'Logout',
						'url'       => 'https://example.test/my-account/logout/?_wpnonce=abc',
						'icon'      => '<svg viewBox="0 0 24 24"><path d="M9 21H5"/></svg>',
						'is_active' => false,
						'is_logout' => true,
					),
				),
			)
		);

		$this->assertStringContainsString( 'Marie Dupont', $html );
		$this->assertStringContainsString( 'marie@example.test', $html );
		$this->assertStringContainsString( '>M<', $html );
		$this->assertStringContainsString( 'account-nav__item is-active', $html );
		$this->assertStringContainsString( 'aria-current="page"', $html );
		$this->assertStringContainsString( 'account-nav__item--logout', $html );
		$this->assertStringContainsString( '<rect x="3" y="3" width="7" height="7"', $html );
		$this->assertStringContainsString( 'Orders', $html );
	}
}
