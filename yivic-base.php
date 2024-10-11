<?php
/**
 * Plugin Name: Yivic Base
 * Plugin URI:  https://yivic.com/wp-plugin-yivic-base/
 * Description: Base plugin for WP development using Laravel
 * Author:      dev@yivic.com, manhphucofficial@yahoo.com
 * Author URI:  https://yivic.com/yivic-team/
 * Version:     0.7.0
 * Text Domain: yivic
 */

// We want to split all the bootstrapping code to a separate file
//  for putting into composer autoload and
//  for easier including on other section e.g. unit test
require_once __DIR__ . DIRECTORY_SEPARATOR . 'yivic-base-bootstrap.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'yivic-base-init.php';
