<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Part of the theme's dependency-inversion layer (Solar_Template\Contracts).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Define the contract used by the theme to read its own `.env` configuration, so the
 *          underlying library (currently vlucas/phpdotenv) can be swapped without touching any
 *          code that reads configuration values.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads and exposes key/value configuration from a `.env` file.
 */
interface EnvironmentLoaderInterface {

	/**
	 * Loads the `.env` file found in the given directory, if any.
	 *
	 * Must never throw or fatal when the directory has no `.env` file: the theme has to keep
	 * working with default values on a fresh checkout where no `.env` was created yet.
	 *
	 * @param string $path Absolute path to the directory containing the `.env` file.
	 * @return void
	 */
	public function load( string $path ): void;

	/**
	 * Reads a configuration value previously loaded by load().
	 *
	 * @param string $key     Name of the environment variable.
	 * @param mixed  $default_value Value returned when the key is not set.
	 * @return mixed The value found, or $default_value when absent.
	 */
	public function get( string $key, mixed $default_value = null ): mixed;
}
