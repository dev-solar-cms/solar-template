<?php
/**
 * Created: 2026-09-27 12:25 CEST
 * Role: Unit test for Solar_Template\Admin\MediaOptimizationController.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the compression backend resolution behind the real "Optimize images" admin
 *          action (verified end to end in Docker, see RELEASE.md) — the AJAX handlers themselves
 *          depend on a real WordPress request/database and are not exercised outside Docker.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Solar_Template\Admin\MediaOptimizationController;
use Solar_Template\Media\GdImageOptimizer;

/**
 * @covers \Solar_Template\Admin\MediaOptimizationController
 */
final class MediaOptimizationControllerTest extends TestCase {

	/**
	 * With no filter registered (the bootstrap's apply_filters() stand-in returns its value
	 * unchanged), the default optimizer is the theme's own GdImageOptimizer.
	 *
	 * @return void
	 */
	public function test_default_optimizer_is_gd_image_optimizer(): void {
		$this->assertInstanceOf( GdImageOptimizer::class, MediaOptimizationController::optimizer() );
	}
}
