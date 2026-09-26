<?php
/**
 * Created: 2026-09-26 18:40 CEST
 * Role: Regression test for template-parts/account/download-row.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the download row template-part with plain, already-computed data and assert on
 *          the resulting markup: icon, name, order reference, remaining downloads, download link.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class DownloadRowTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/account/download-row.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A limited-download file renders its icon, name, order reference and remaining count.
	 *
	 * @return void
	 */
	public function test_renders_limited_download(): void {
		$html = $this->render(
			array(
				'name'            => 'Care guide.pdf',
				'product_name'    => 'Premium Watch',
				'order_number'    => '1187',
				'extension'       => 'PDF',
				'icon'            => '📄',
				'remaining_label' => '3 downloads remaining',
				'download_url'    => 'https://example.test/?download_file=1',
			)
		);

		$this->assertStringContainsString( 'Care guide.pdf', $html );
		$this->assertStringContainsString( 'Order #1187', $html );
		$this->assertStringContainsString( 'PDF', $html );
		$this->assertStringContainsString( '3 downloads remaining', $html );
		$this->assertStringContainsString( 'https://example.test/?download_file=1', $html );
	}

	/**
	 * An unlimited download shows the given remaining label as-is (e.g. "Unlimited").
	 *
	 * @return void
	 */
	public function test_renders_unlimited_download(): void {
		$html = $this->render(
			array(
				'name'            => 'Lookbook.zip',
				'order_number'    => '',
				'extension'       => 'ZIP',
				'icon'            => '🗂',
				'remaining_label' => 'Unlimited',
				'download_url'    => 'https://example.test/?download_file=2',
			)
		);

		$this->assertStringContainsString( 'Unlimited', $html );
		$this->assertStringNotContainsString( 'Order #', $html );
	}
}
