<?php
/**
 * Created: 2026-09-28 11:20 CEST
 * Role: Test double standing in for WordPress' request-halting `wp_die()`.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Thrown by this test suite's wp_send_json_error()/wp_send_json_success()/
 *          check_ajax_referer() stand-ins (tests/php/wp-ajax-stubs.php) to simulate the real
 *          functions' own request-halting behaviour (`wp_die()`, which exits the script) — a test
 *          asserts this exception is thrown and that no code after the halting call ever ran.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Support;

/**
 * Simulates WordPress' `wp_die()` halting a request.
 */
final class WpDieException extends \RuntimeException {}
