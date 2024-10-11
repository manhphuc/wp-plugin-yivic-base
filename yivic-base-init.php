<?php
// Only initiate the app() when WordPress is fully loaded
// If the plugin is loaded via Composer before WordPress is ready, we need to ensure proper checks are in place.

use Yivic_Base\App\Support\Yivic_Base_Helper;

require_once(__DIR__ . '/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php');

Yivic_Base_Helper::initialize( plugin_dir_url( __FILE__ ), __DIR__ );
