<?php
/**
 * Created: 2026-09-26 22:48 CEST
 * Role: Regression test for template-parts/pages/legal-toc.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the legal page's sticky table of contents with plain heading data and assert on
 *          the resulting anchors/numbering, and that nothing renders with no heading.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class LegalTocTest extends TestCase {

	/**
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/pages/legal-toc.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * @return void
	 */
	public function test_renders_nothing_without_any_heading(): void {
		$this->assertSame( '', $this->render( array( 'headings' => array() ) ) );
	}

	/**
	 * @return void
	 */
	public function test_renders_numbered_anchors_for_each_heading(): void {
		$html = $this->render(
			array(
				'headings' => array(
					array(
						'id'    => 'object-and-scope',
						'label' => 'Object and scope',
					),
					array(
						'id'    => 'deliveries',
						'label' => 'Deliveries',
					),
				),
			)
		);

		$this->assertStringContainsString( 'href="#object-and-scope"', $html );
		$this->assertStringContainsString( 'href="#deliveries"', $html );
		$this->assertStringContainsString( '01', $html );
		$this->assertStringContainsString( '02', $html );
	}
}
