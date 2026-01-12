<?php
$yivic_base_existed = defined( 'YIVIC_BASE_PLUGIN_VERSION' );

// General fixed constants
defined( 'DIR_SEP' ) || define( 'DIR_SEP', DIRECTORY_SEPARATOR );

// Update these constants whenever you bump the version
defined( 'YIVIC_BASE_PLUGIN_VERSION' ) || define( 'YIVIC_BASE_PLUGIN_VERSION', '0.9.1' );

// We set the slug for the plugin here.
// This slug will be used to identify the plugin instance from the WP_Application container
defined( 'YIVIC_BASE_PLUGIN_SLUG' ) || define( 'YIVIC_BASE_PLUGIN_SLUG', 'yivic-base' );

// The prefix for wp_app request
defined( 'YIVIC_BASE_WP_APP_PREFIX' ) || define(
	'YIVIC_BASE_WP_APP_PREFIX',
	! empty( getenv( 'YIVIC_BASE_WP_APP_PREFIX' ) ) ? getenv( 'YIVIC_BASE_WP_APP_PREFIX' ) : 'wp-app'
);

// The prefix for wp_api request
defined( 'YIVIC_BASE_WP_API_PREFIX' ) || define(
	'YIVIC_BASE_WP_API_PREFIX',
	! empty( getenv( 'YIVIC_BASE_WP_API_PREFIX' ) ) ? getenv( 'YIVIC_BASE_WP_API_PREFIX' ) : 'wp-api'
);

defined( 'YIVIC_BASE_SETUP_HOOK_NAME' ) || define(
	'YIVIC_BASE_SETUP_HOOK_NAME',
	! empty( getenv( 'YIVIC_BASE_SETUP_HOOK_NAME' ) ) ? getenv( 'YIVIC_BASE_SETUP_HOOK_NAME' ) : 'plugins_loaded'
);

require_once __DIR__ . DIR_SEP . 'src' . DIR_SEP . 'helpers.php';

$autoload_file = __DIR__ . DIR_SEP . 'vendor' . DIR_SEP . 'autoload.php';

if ( file_exists( $autoload_file ) && ! $yivic_base_existed ) {
	require_once $autoload_file;
}
