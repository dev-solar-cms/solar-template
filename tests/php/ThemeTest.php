<?php
/**
 * Created: 2026-09-27 10:20 CEST
 * Role: Unit test for Solar_Template\Theme's pure helpers.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify Solar_Template\Theme::rewrite_stylesheet_tag_to_preload() — the pure string
 *          transform behind the main stylesheet's non-render-blocking preload/swap tag (see
 *          Solar_Template\Theme::defer_main_stylesheet()) — without needing WordPress' own filter
 *          system.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests;

use PHPUnit\Framework\TestCase;
use Solar_Template\Theme;

/**
 * @covers \Solar_Template\Theme
 */
final class ThemeTest extends TestCase {

	/**
	 * A normal WordPress stylesheet tag is rewritten into a preload-then-swap one, with a
	 * <noscript> fallback carrying the original tag.
	 *
	 * @return void
	 */
	public function test_rewrites_a_stylesheet_tag_into_a_preload_with_noscript_fallback(): void {
		$original = "<link rel='stylesheet' id='solar-template-css' href='https://example.test/main.css?ver=123' media='all' />\n"; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- test fixture string, not a real stylesheet reference.

		$result = Theme::rewrite_stylesheet_tag_to_preload( $original );

		$this->assertStringContainsString( "rel='preload'", $result );
		$this->assertStringContainsString( "as='style'", $result );
		$this->assertStringContainsString( "onload=\"this.onload=null;this.rel='stylesheet'\"", $result );
		$this->assertStringContainsString( '<noscript>' . $original . '</noscript>', $result );
	}

	/**
	 * A tag with no `rel='stylesheet'` to rewrite (e.g. an already-transformed one, or an
	 * unrecognized format) is returned unchanged rather than wrapped in a broken <noscript>.
	 *
	 * @return void
	 */
	public function test_returns_the_tag_unchanged_when_it_has_no_stylesheet_rel_attribute(): void {
		$original = "<link rel='preload' as='style' href='https://example.test/main.css' />\n";

		$this->assertSame( $original, Theme::rewrite_stylesheet_tag_to_preload( $original ) );
	}
}
