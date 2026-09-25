<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Main bootstrap file of the Solar Template theme (loaded by WordPress on every request).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Load the Composer autoloader and hand off to Solar_Template\Theme::boot(), which
 *          registers every WordPress hook the theme depends on. Kept procedural as required by
 *          WordPress' own conventions for this file; all actual behaviour lives in classes under
 *          `inc/` (namespace `Solar_Template\*`). A theme release always ships with `vendor/`
 *          already installed, so no fallback is needed here for a missing dependency.
 *
 * @package Solar_Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/vendor/autoload.php';

\Solar_Template\Theme::boot();
