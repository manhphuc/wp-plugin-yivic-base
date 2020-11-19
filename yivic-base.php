<?php
/**
 * Plugin Name: Yivic Base
 * Description: The Base plugin for Theme and Plugin development. It requires ACF pro to work.
 * Version: 0.0.1
 * Author: Yivic
 * Author URI: http://www.yivic.com/wordpress-plugin-yivic-base
 * License: GPLv2 or later
 * Text Domain: yivic
 * Domain Path: /languages/
 */

defined( 'YIVIC_BASE_PLUGIN_VER' ) || define( 'YIVIC_BASE_PLUGIN_VER', 0.3 );
defined( 'YIVIC_BASE_PLUGIN_PATH' ) || define( 'YIVIC_BASE_PLUGIN_PATH', __DIR__ );
defined( 'YIVIC_BASE_PLUGIN_FOLDER_NAME' ) || define( 'YIVIC_BASE_PLUGIN_FOLDER_NAME', 'yivic-base' );
defined( 'YIVIC_BASE_PLUGIN_URL' ) || define( 'YIVIC_BASE_PLUGIN_URL', plugins_url( null, YIVIC_BASE_PLUGIN_PATH ) );

// Use autoload if Laravel not loaded
if ( ! class_exists( \Illuminate\Foundation\Application::class ) ) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}
