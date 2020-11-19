<?php


namespace Yivic\Wp\YivicBase\App\Services;


use Yivic\Wp\YivicBase\Libs\Traits\ServiceTrait;
use Yivic\Wp\YivicBase\Libs\WpApp;
use WP_CLI;
use Exception;

class WpCli {
	use ServiceTrait;

	/**
	 * @param $args
	 * @param $assoc_args
	 */
	public static function runCommands( $args, $assoc_args ) {
		if ( isset( $args[0] ) ) {
			$command = $args[0];
			unset( $args[0] );
			try {
				static::$command( $args, $assoc_args );
			} catch ( Exception $e ) {
				throw( sprintf( 'Command %s not available', $command ) );
			}
		}
	}

	/**
	 * Try to create a file and make a file writable
	 *
	 * @param $file_path
	 */
	public static function createFileWritable( $file_path ) {
		@chmod( dirname( $file_path ), 0777 );
		if ( ! file_exists( $file_path ) ) {
			wp_mkdir_p( dirname( $file_path ) );
			$fh = fopen( $file_path, 'w+' );
			fclose( $fh );
			WP_CLI::log( "\n" . sprintf( 'Create file or folder successfully: %s', $file_path ) );
		}
		@chmod( $file_path, 0777 );
	}

	/**
	 * Make all cached config files writable
	 *
	 * @throws WP_CLI\ExitException
	 */
	public static function prepareWritableFoldersAndFiles() {
		WP_CLI::log( '--' );
		WP_CLI::log( sprintf( 'Create and make cached Config Files writable' ) );

		$config = yivic_base_init_wp_app_config();
		$wp_app = new WpApp( $config['basePath'] );
		static::createFileWritable( $wp_app->getCachedConfigPath() );
		static::createFileWritable( $wp_app->getCachedEventsPath() );
		static::createFileWritable( $wp_app->getCachedPackagesPath() );
		static::createFileWritable( $wp_app->getCachedRoutesPath() );
		static::createFileWritable( $wp_app->getCachedServicesPath() );

		WP_CLI::success( 'Done' );
		WP_CLI::log( "\n" );
	}

	/**
	 * A test method to check WpCli class working properly
	 */
	public static function foo() {
		WP_CLI::success( 'Foo' );
	}
}
