<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\Foundation\Database\Wpdb_Connection;
use Illuminate\Support\ServiceProvider;

class Wpdb_Service_Provider extends ServiceProvider {
	public function register() {
		// Add database driver.
		$this->app->resolving(
			'db',
			function ( $db ) {
				$db->extend(
					'wpdb',
					function ( $config, $name ) {
						$config['name'] = $name;

						return new Wpdb_Connection( $config );
					}
				);
			}
		);
	}
}
