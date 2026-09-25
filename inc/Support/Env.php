<?php
/**
 * Created: 2026-09-25 15:02 CEST
 * Role: Theme `.env` reader (Solar_Template\Support).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Read a value from the theme's own `.env` file (see `.env.example`), via
 *          Solar_Template\Contracts\EnvironmentLoaderInterface. Never used for database
 *          credentials: those belong to the parent `wordpress-dev-env` repo's own `.env`, a
 *          separate file this theme never reads from or writes to.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

use Solar_Template\Contracts\EnvironmentLoaderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads configuration values from the theme's `.env` file.
 */
final class Env {

	/**
	 * @var EnvironmentLoaderInterface|null
	 */
	private static ?EnvironmentLoaderInterface $loader = null;

	/**
	 * @param string $key           Name of the environment variable, e.g. `SOLAR_TEMPLATE_CACHE_TTL`.
	 * @param mixed  $default_value Value returned when the key is not set.
	 * @return mixed The value found, or $default_value.
	 */
	public static function get( string $key, mixed $default_value = null ): mixed {
		if ( null === self::$loader ) {
			self::$loader = new DotenvEnvironmentLoader();
			self::$loader->load( get_template_directory() );
		}

		return self::$loader->get( $key, $default_value );
	}
}
