<?php
/**
 * Solar Template theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function solar_template_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'solar_template_setup' );
