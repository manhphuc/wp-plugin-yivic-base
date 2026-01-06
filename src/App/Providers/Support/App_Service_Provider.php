<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers\Support;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Support\ServiceProvider;

class App_Service_Provider extends ServiceProvider {

	/**
	 * Register any application services.
	 */
	public function register() {
		$this->fetch_config();
	}

	protected function fetch_config(): void {
		// All other configs should go here
		config(
			apply_filters(
				App_Const::FILTER_WP_APP_APP_CONFIG,
				$this->get_default_config()
			)
		);
	}

	protected function get_default_config(): array {
		return [
			'cors' => [
				/*
				|--------------------------------------------------------------------------
				| Cross-Origin Resource Sharing (CORS) Configuration
				|--------------------------------------------------------------------------
				|
				| Here you may configure your settings for cross-origin resource sharing
				| or "CORS". This determines what cross-origin operations may execute
				| in web browsers. You are free to adjust these settings as needed.
				|
				| To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
				|
				*/
				'paths' => [ 'api/*', 'sanctum/csrf-cookie' ],
				'allowed_methods' => [ '*' ],
				'allowed_origins' => [ '*' ],
				'allowed_origins_patterns' => [],
				'allowed_headers' => [ '*' ],
				'exposed_headers' => [],
				'max_age' => 0,
				'supports_credentials' => false,
			],
			'services' => [
				'mailgun' => [
					'domain' => env( 'MAILGUN_DOMAIN' ),
					'secret' => env( 'MAILGUN_SECRET' ),
					'endpoint' => env( 'MAILGUN_ENDPOINT', 'api.mailgun.net' ),
					'scheme' => 'https',
				],

				'postmark' => [
					'token' => env( 'POSTMARK_TOKEN' ),
				],

				'ses' => [
					'key' => env( 'AWS_ACCESS_KEY_ID' ),
					'secret' => env( 'AWS_SECRET_ACCESS_KEY' ),
					'region' => env( 'AWS_DEFAULT_REGION', 'us-east-1' ),
				],
			],
		];
	}
}
