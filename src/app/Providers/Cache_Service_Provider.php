<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Cache\CacheServiceProvider;

class Cache_Service_Provider extends CacheServiceProvider {
	public function register() {
		$this->fetch_config();

		parent::register();
	}

	protected function fetch_config(): void {
		config(
			[
				'cache' => apply_filters(
					App_Const::FILTER_WP_APP_CACHE_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		return [

			/*
			|--------------------------------------------------------------------------
			| Default Cache Store
			|--------------------------------------------------------------------------
			|
			| This option controls the default cache connection that gets used while
			| using this caching library. This connection is used when another is
			| not explicitly specified when executing a given caching function.
			|
			| Supported: "apc", "array", "database", "file", "memcached", "redis"
			|
			*/

			'default' => env( 'CACHE_DRIVER', 'file' ),

			/*
			|--------------------------------------------------------------------------
			| Cache Stores
			|--------------------------------------------------------------------------
			|
			| Here you may define all of the cache "stores" for your application as
			| well as their drivers. You may even define multiple stores for the
			| same cache driver to group types of items stored in your caches.
			|
			*/

			'stores' => [

				'apc' => [
					'driver' => 'apc',
				],

				'array' => [
					'driver' => 'array',
				],

				'database' => [
					'driver' => 'database',
					'table' => 'cache',
					'connection' => null,
				],

				'file' => [
					'driver' => 'file',
					'path' => $this->generate_file_cache_storage_path(),
				],
			],

			/*
			|--------------------------------------------------------------------------
			| Cache Key Prefix
			|--------------------------------------------------------------------------
			|
			| When utilizing a RAM based store such as APC or Memcached, there might
			| be other applications utilizing the same cache. So, we'll specify a
			| value to get prefixed to all our keys so we can avoid collisions.
			|
			*/
			'prefix' => 'wp_app',
		];
	}

	/**
	 * The storage path for file cache driver
	 *
	 * @return string
	 */
	protected function generate_file_cache_storage_path(): string {
		return storage_path( 'framework/cache/data' );
	}
}