<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Broadcasting\BroadcastServiceProvider;

class Broadcast_Service_Provider extends BroadcastServiceProvider {
	public function register() {
		$this->fetch_config();
		parent::register();
	}

	protected function fetch_config(): void {
		config(
			[
				'broadcasting' => apply_filters(
					App_Const::FILTER_WP_APP_BROADCASTING_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		return [

			/*
			|--------------------------------------------------------------------------
			| Default Broadcaster
			|--------------------------------------------------------------------------
			|
			| This option controls the default broadcaster that will be used by the
			| framework when an event needs to be broadcast. You may set this to
			| any of the connections defined in the "connections" array below.
			|
			| Supported: "pusher", "ably", "redis", "log", "null"
			|
			*/

			'default' => env( 'BROADCAST_DRIVER', 'null' ),

			/*
			|--------------------------------------------------------------------------
			| Broadcast Connections
			|--------------------------------------------------------------------------
			|
			| Here you may define all of the broadcast connections that will be used
			| to broadcast events to other systems or over websockets. Samples of
			| each available type of connection are provided inside this array.
			|
			*/

			'connections' => [

				'pusher' => [
					'driver' => 'pusher',
					'key' => env( 'PUSHER_APP_KEY' ),
					'secret' => env( 'PUSHER_APP_SECRET' ),
					'app_id' => env( 'PUSHER_APP_ID' ),
					'options' => [
						'cluster' => env( 'PUSHER_APP_CLUSTER' ),
						// phpcs:ignore Universal.Operators.DisallowShortTernary.Found
						'host' => env( 'PUSHER_HOST' ) ?: 'api-' . env( 'PUSHER_APP_CLUSTER', 'mt1' ) . '.pusher.com',
						'port' => env( 'PUSHER_PORT', 443 ),
						'scheme' => env( 'PUSHER_SCHEME', 'https' ),
						'encrypted' => true,
						'useTLS' => env( 'PUSHER_SCHEME', 'https' ) === 'https',
					],
					'client_options' => [
						// Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
					],
				],

				'ably' => [
					'driver' => 'ably',
					'key' => env( 'ABLY_KEY' ),
				],

				'redis' => [
					'driver' => 'redis',
					'connection' => 'default',
				],

				'log' => [
					'driver' => 'log',
				],

				'null' => [
					'driver' => 'null',
				],

			],

		];
	}
}