<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\App\Support\App_Const;
use Yivic_Base\Foundation\Database\Connectors\Connection_Factory;
use Illuminate\Database\DatabaseServiceProvider;

class Database_Service_Provider extends DatabaseServiceProvider {
	public function register() {
		$this->fetch_config();

		parent::register();

		$this->app->extend(
			'db.factory',
			function ( $instance, $app ) {
				return new Connection_Factory( $app );
			}
		);

		// Add database driver.
		$this->app->resolving(
			'db',
			function ( $db ) {
				$db->extend(
					'wpdb',
					function ( $config, $name ) {
						$config['name'] = $name;

						/** @var \Yivic_Base\Foundation\Database\Connectors\Connection_Factory $db_factory */
						$db_factory = app( 'db.factory' );
						return $db_factory->make( $config, $name );
					}
				);
			}
		);
	}

	protected function fetch_config(): void {
		config(
			[
				'database' => apply_filters(
					App_Const::FILTER_WP_APP_DATABASE_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		/** @var \wpdb $wpdb */
		$wpdb = $GLOBALS['wpdb'];
		$default_mysql_config = [
			'driver'   => 'mysql',
			'host'     => $wpdb->dbhost,
			'database' => $wpdb->dbname,
			'username' => $wpdb->dbuser,
			'password' => $wpdb->dbpassword,
		];

		if ( ! empty( $wpdb->base_prefix ) ) {
			$default_mysql_config['prefix'] = $wpdb->base_prefix;
			$default_mysql_config['prefix_indexes'] = $wpdb->base_prefix;
		}

		if ( ! empty( $wpdb->charset ) ) {
			$default_mysql_config['charset'] = $wpdb->charset;
		}

		if ( ! empty( $wpdb->collate ) ) {
			$default_mysql_config['collate'] = $wpdb->collate;
		}

		$config = [

			/*
			|--------------------------------------------------------------------------
			| Default Database Connection Name
			|--------------------------------------------------------------------------
			|
			| Here you may specify which of the database connections below you wish
			| to use as your default connection for all database work. Of course
			| you may use many connections at once using the Database library.
			|
			*/

			'default'     => env( 'DB_CONNECTION', 'mysql' ),

			/*
			|--------------------------------------------------------------------------
			| Database Connections
			|--------------------------------------------------------------------------
			|
			| Here are each of the database connections setup for your application.
			| Of course, examples of configuring each database platform that is
			| supported by Laravel is shown below to make development simple.
			|
			|
			| All database work in Laravel is done through the PHP PDO facilities
			| so make sure you have the driver for your particular database of
			| choice installed on your machine before you begin development.
			|
			*/

			'connections' => [
				'mysql'        => $default_mysql_config,
				'mysql_logs'   => $default_mysql_config,
				'mysql_queues' => $default_mysql_config,
				'wpdb'         => array_merge(
					$default_mysql_config,
					[
						'driver' => 'wpdb',
						'wpdb'   => $wpdb,
					]
				),
			],

			/*
			|--------------------------------------------------------------------------
			| Migration Repository Table
			|--------------------------------------------------------------------------
			|
			| This table keeps track of all the migrations that have already run for
			| your application. Using this information, we can determine which of
			| the migrations on disk haven't actually been run in the database.
			|
			*/
			'migrations'  => 'wp_app_migrations',
		];

		return $config;
	}
}
