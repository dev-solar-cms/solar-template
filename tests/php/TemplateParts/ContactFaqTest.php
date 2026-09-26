<?php
/**
 * Created: 2026-09-26 22:52 CEST
 * Role: Regression test for template-parts/pages/contact-faq.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the FAQ accordion with plain question/answer data and assert on the resulting
 *          native <details>/<summary> markup, and that nothing renders with no FAQ entry.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class ContactFaqTest extends TestCase {

	/**
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/pages/contact-faq.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * @return void
	 */
	public function test_renders_nothing_without_any_item(): void {
		$this->assertSame(
			'',
			$this->render(
				array(
					'heading' => array(
						'eyebrow' => 'FAQ',
						'heading' => 'Frequently asked questions',
					),
					'items'   => array(),
				)
			)
		);
	}

	/**
	 * @return void
	 */
	public function test_renders_one_native_accordion_item_per_entry(): void {
		$html = $this->render(
			array(
				'heading' => array(
					'eyebrow' => 'FAQ',
					'heading' => 'Frequently asked questions',
				),
				'items'   => array(
					array(
						'question' => 'What are the delivery times?',
						'answer'   => '3 to 5 business days.',
					),
					array(
						'question' => 'How do I return an item?',
						'answer'   => 'Within 30 days.',
					),
				),
			)
		);

		$this->assertSame( 2, substr_count( $html, '<details' ) );
		$this->assertStringContainsString( 'What are the delivery times?', $html );
		$this->assertStringContainsString( '3 to 5 business days.', $html );
		$this->assertStringContainsString( 'Frequently asked questions', $html );
	}
}
