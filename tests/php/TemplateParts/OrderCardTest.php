<?php
/**
 * Created: 2026-09-26 16:35 CEST
 * Role: Regression test for template-parts/account/order-card.php.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Render the order card template-part with plain, already-computed data and assert on
 *          the resulting markup: meta/status/thumbnails, and the "Reorder" action only appearing
 *          when a reorder URL is provided.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\TemplateParts;

use PHPUnit\Framework\TestCase;

final class OrderCardTest extends TestCase {

	/**
	 * Renders the template-part with the given $args and returns the captured output.
	 *
	 * @param array $args Arguments passed to the template-part.
	 * @return string Rendered HTML.
	 */
	private function render( array $args ): string {
		ob_start();
		( static function () use ( $args ): void {
			include dirname( __DIR__, 3 ) . '/template-parts/account/order-card.php';
		} )();

		return ob_get_clean();
	}

	/**
	 * A reorderable (delivered) order renders its meta, status, thumbnails and a "Reorder" link.
	 *
	 * @return void
	 */
	public function test_renders_delivered_order_with_reorder_action(): void {
		$html = $this->render(
			array(
				'number'           => '1187',
				'date'             => '12 nov. 2024',
				'total'            => '<span class="amount">178,00&nbsp;€</span>',
				'status'           => array(
					'badge' => 'delivered',
					'icon'  => '✓',
					'label' => 'Delivered',
				),
				'item_count'       => 5,
				'thumbnails'       => array( 'https://example.test/a.jpg', 'https://example.test/b.jpg' ),
				'extra_item_count' => 3,
				'view_url'         => 'https://example.test/my-account/view-order/1187/',
				'reorder_url'      => 'https://example.test/my-account/orders/?solar_template_reorder=1187',
			)
		);

		$this->assertStringContainsString( '#1187', $html );
		$this->assertStringContainsString( 'order-card__header--delivered', $html );
		$this->assertStringContainsString( 'order-status-badge--delivered', $html );
		$this->assertStringContainsString( 'https://example.test/a.jpg', $html );
		$this->assertStringContainsString( '+3 item', $html );
		$this->assertStringContainsString( 'View detail', $html );
		$this->assertStringContainsString( 'Reorder', $html );
		$this->assertStringContainsString( 'solar_template_reorder=1187', $html );
	}

	/**
	 * A non-reorderable order (still in transit) has no "Reorder" link.
	 *
	 * @return void
	 */
	public function test_skips_reorder_action_when_not_reorderable(): void {
		$html = $this->render(
			array(
				'number'           => '1234',
				'date'             => '28 déc. 2024',
				'total'            => '<span class="amount">411,00&nbsp;€</span>',
				'status'           => array(
					'badge' => 'transit',
					'icon'  => '📦',
					'label' => 'In transit',
				),
				'item_count'       => 1,
				'thumbnails'       => array(),
				'extra_item_count' => 0,
				'view_url'         => 'https://example.test/my-account/view-order/1234/',
				'reorder_url'      => null,
			)
		);

		$this->assertStringContainsString( '#1234', $html );
		$this->assertStringNotContainsString( 'Reorder', $html );
		$this->assertStringNotContainsString( 'order-card__thumbnails', $html );
	}
}
