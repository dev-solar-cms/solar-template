<?php
/**
 * Created: 2026-09-26 22:30 CEST
 * Role: Unit test for Solar_Template\Pages\LegalPageController.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the pure heading-annotation logic: id injection on <h2> tags lacking one, respect
 *          for an author-set id, unique slugs for duplicate heading text, accented text
 *          transliteration, and the "no h2 at all" degenerate case.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Pages;

use PHPUnit\Framework\TestCase;
use Solar_Template\Pages\LegalPageController;

final class LegalPageControllerTest extends TestCase {

	/**
	 * An <h2> with no id gets one generated from its text, and that same id is used in the returned
	 * table of contents.
	 *
	 * @return void
	 */
	public function test_adds_id_to_heading_without_one(): void {
		$result = LegalPageController::annotate_headings( '<h2>Object and scope</h2><p>Body</p>' );

		$this->assertSame( '<h2 id="object-and-scope">Object and scope</h2><p>Body</p>', $result['content'] );
		$this->assertSame(
			array(
				array(
					'id'    => 'object-and-scope',
					'label' => 'Object and scope',
				),
			),
			$result['headings']
		);
	}

	/**
	 * An author-set id is kept as-is rather than overwritten.
	 *
	 * @return void
	 */
	public function test_keeps_existing_author_set_id(): void {
		$result = LegalPageController::annotate_headings( '<h2 id="custom-anchor" class="foo">Deliveries</h2>' );

		$this->assertSame( '<h2 id="custom-anchor" class="foo">Deliveries</h2>', $result['content'] );
		$this->assertSame( 'custom-anchor', $result['headings'][0]['id'] );
	}

	/**
	 * Two headings sharing the exact same text get distinct anchors (-2 suffix on the second).
	 *
	 * @return void
	 */
	public function test_deduplicates_identical_heading_text(): void {
		$result = LegalPageController::annotate_headings( '<h2>Returns</h2><h2>Returns</h2>' );

		$this->assertSame( array( 'returns', 'returns-2' ), array_column( $result['headings'], 'id' ) );
	}

	/**
	 * Accented characters are transliterated to a plain ASCII slug.
	 *
	 * @return void
	 */
	public function test_slugifies_accented_text(): void {
		$result = LegalPageController::annotate_headings( '<h2>Données personnelles & Vie privée</h2>' );

		$this->assertSame( 'donnees-personnelles-vie-privee', $result['headings'][0]['id'] );
	}

	/**
	 * Inline markup inside the heading is stripped from the table of contents label, but preserved in
	 * the rendered content.
	 *
	 * @return void
	 */
	public function test_strips_inline_markup_from_toc_label_only(): void {
		$result = LegalPageController::annotate_headings( '<h2>Article <strong>9</strong></h2>' );

		$this->assertStringContainsString( '<strong>9</strong>', $result['content'] );
		$this->assertSame( 'Article 9', $result['headings'][0]['label'] );
	}

	/**
	 * Content with no <h2> at all produces an empty table of contents and untouched content.
	 *
	 * @return void
	 */
	public function test_content_without_headings_yields_empty_toc(): void {
		$result = LegalPageController::annotate_headings( '<p>No headings here.</p>' );

		$this->assertSame( '<p>No headings here.</p>', $result['content'] );
		$this->assertSame( array(), $result['headings'] );
	}

	/**
	 * The tab definitions cover exactly the four legal pages the theme resolves real links for.
	 *
	 * @return void
	 */
	public function test_tab_definitions_cover_the_four_legal_pages(): void {
		$this->assertSame(
			array( 'terms-and-conditions', 'privacy-policy', 'legal-notice', 'cookies' ),
			array_keys( LegalPageController::tab_definitions() )
		);
	}
}
