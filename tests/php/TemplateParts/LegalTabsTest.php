<?php
/**
 * Created: 2026-09-26 22:45 CEST
 * Role: Regression test for template-parts/pages/legal-tabs.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the legal page tab strip with plain, already-resolved tab data and assert on the
 *          resulting markup: active tab styling/aria-current, and that nothing renders with no tab.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class LegalTabsTest extends TestCase {

	/**
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/pages/legal-tabs.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * @return void
	 */
	public function test_renders_nothing_without_any_tab(): void {
		$this->assertSame( '', $this->render( array( 'tabs' => array() ) ) );
	}

	/**
	 * @return void
	 */
	public function test_marks_the_current_tab_active(): void {
		$html = $this->render(
			array(
				'tabs' => array(
					array(
						'slug'   => 'terms-and-conditions',
						'label'  => 'Terms & Conditions',
						'url'    => '/terms/',
						'active' => true,
					),
					array(
						'slug'   => 'privacy-policy',
						'label'  => 'Privacy Policy',
						'url'    => '/privacy/',
						'active' => false,
					),
				),
			)
		);

		$this->assertStringContainsString( 'legal-tabs__link--active', $html );
		$this->assertStringContainsString( 'aria-current="page"', $html );
		$this->assertSame( 1, substr_count( $html, 'aria-current="page"' ) );
		$this->assertStringContainsString( 'href="/privacy/"', $html );
	}
}
