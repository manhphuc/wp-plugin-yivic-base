<?php

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Illuminate\Foundation\Application;

// Ensure WordPress core is loaded before proceeding
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	return;
}

// Check if the Illuminate\Foundation\Application class exists
if ( class_exists( Application::class ) ) {
	// Use Composer's autoload function to resolve the class file path
	$autoload_functions = spl_autoload_functions();
	if ( is_array( $autoload_functions ) ) {
		foreach ( $autoload_functions as $autoload_function ) {
			// Ensure $autoload_function is an array and can have `findFile`
			if (
				is_array( $autoload_function ) &&
				isset( $autoload_function[0] ) &&
				$autoload_function[0] instanceof Composer\Autoload\ClassLoader
			) {
				/** @var Composer\Autoload\ClassLoader $class_loader */
				$class_loader = $autoload_function[0];

				// Use findFile method to locate the class
				$class_path = $class_loader->findFile( Application::class );

				if ( is_string( $class_path ) ) {
					// Return the directory of the class
					$class_directory = dirname( $class_path );

					// Load the helpers.php file
					$helpers_path = $class_directory . '/helpers.php';
					if ( file_exists( $helpers_path ) ) {
						require_once $helpers_path;
					} else {
						wp_die( 'The Laravel helpers.php file was not found in the expected directory.' );
					}
				}
				break; // Stop searching once the file is resolved
			}
		}
	}
} else {
	return;
}

// Initialize the Yivic Base Helper
Yivic_Base_Helper::initialize( plugin_dir_url( __FILE__ ), __DIR__ );
