<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Default implementation of Solar_Template\Contracts\EnvironmentLoaderInterface.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Read the theme's own `.env` file via the vlucas/phpdotenv library. This is the only
 *          class in the theme allowed to reference that library directly.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Support;

use Dotenv\Dotenv;
use Solar_Template\Contracts\EnvironmentLoaderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the theme's `.env` file with vlucas/phpdotenv, without ever failing when it is missing.
 */
final class DotenvEnvironmentLoader implements EnvironmentLoaderInterface {

	/**
	 * Whether load() successfully found and parsed a `.env` file.
	 *
	 * @var bool
	 */
	private bool $loaded = false;

	/**
	 * Loads the `.env` file found in the given directory, if any.
	 *
	 * @param string $path Absolute path to the directory containing the `.env` file.
	 * @return void
	 */
	public function load( string $path ): void {
		if ( ! is_dir( $path ) ) {
			return;
		}

		Dotenv::createImmutable( $path )->safeLoad();
		$this->loaded = true;
	}

	/**
	 * Reads a configuration value from the environment (populated by load(), or already present
	 * in the process environment, e.g. injected by Docker).
	 *
	 * @param string $key     Name of the environment variable.
	 * @param mixed  $default_value Value returned when the key is not set.
	 * @return mixed The value found, or $default_value when absent.
	 */
	public function get( string $key, mixed $default_value = null ): mixed {
		$value = $_ENV[ $key ] ?? getenv( $key );

		return ( false === $value || null === $value || '' === $value ) ? $default_value : $value;
	}

	/**
	 * Reports whether a `.env` file was actually found and loaded.
	 *
	 * @return bool True once load() has found a `.env` file.
	 */
	public function has_loaded_file(): bool {
		return $this->loaded;
	}
}
