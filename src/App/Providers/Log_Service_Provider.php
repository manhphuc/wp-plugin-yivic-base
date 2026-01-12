<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Log\LogServiceProvider;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

class Log_Service_Provider extends LogServiceProvider {
	public function register() {
		$this->fetch_config();

		parent::register();
	}

	protected function fetch_config(): void {
		config(
			[
				'logging' => apply_filters(
					App_Const::FILTER_WP_APP_LOGGING_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		return [

			/*
			|--------------------------------------------------------------------------
			| Default Log Channel
			|--------------------------------------------------------------------------
			|
			| This option defines the default log channel that gets used when writing
			| messages to the logs. The name specified in this option should match
			| one of the channels defined in the "channels" configuration array.
			|
			*/

			'default' => env( 'LOG_CHANNEL', 'stack' ),

			/*
			|--------------------------------------------------------------------------
			| Deprecations Log Channel
			|--------------------------------------------------------------------------
			|
			| This option controls the log channel that should be used to log warnings
			| regarding deprecated PHP and library features. This allows you to get
			| your application ready for upcoming major versions of dependencies.
			|
			*/

			'deprecations' => [
				'channel' => env( 'LOG_DEPRECATIONS_CHANNEL', 'deprecations' ),
				'trace' => false,
			],

			/*
			|--------------------------------------------------------------------------
			| Log Channels
			|--------------------------------------------------------------------------
			|
			| Here you may configure the log channels for your application. Out of
			| the box, Laravel uses the Monolog PHP logging library. This gives
			| you a variety of powerful log handlers / formatters to utilize.
			|
			| Available Drivers: "single", "daily", "slack", "syslog",
			|                    "errorlog", "monolog",
			|                    "custom", "stack"
			|
			*/

			'channels' => [
				'stack' => [
					'driver' => 'stack',
					'channels' => [ 'single' ],
					'ignore_exceptions' => false,
				],

				'single' => [
					'driver' => 'single',
					'path' => ini_get( 'error_log' ) ? ini_get( 'error_log' ) : storage_path( 'logs/laravel.log' ),
					'level' => env( 'LOG_LEVEL', 'debug' ),
					'replace_placeholders' => true,
				],

				'daily' => [
					'driver' => 'daily',
					// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'path' => ini_get( 'error_log' ) ? ini_get( 'error_log' ) : storage_path( 'logs/' . date( 'Ymd' ) . '.log' ),
					'level' => env( 'LOG_LEVEL', 'debug' ),
					'days' => 14,
					'replace_placeholders' => true,
				],

				'slack' => [
					'driver' => 'slack',
					'url' => env( 'LOG_SLACK_WEBHOOK_URL' ),
					'username' => 'Laravel Log',
					'emoji' => ':boom:',
					'level' => env( 'LOG_LEVEL', 'critical' ),
					'replace_placeholders' => true,
				],

				'papertrail' => [
					'driver' => 'monolog',
					'level' => env( 'LOG_LEVEL', 'debug' ),
					'handler' => env( 'LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class ),
					'handler_with' => [
						'host' => env( 'PAPERTRAIL_URL' ),
						'port' => env( 'PAPERTRAIL_PORT' ),
						'connectionString' => 'tls://' . env( 'PAPERTRAIL_URL' ) . ':' . env( 'PAPERTRAIL_PORT' ),
					],
					'processors' => [ PsrLogMessageProcessor::class ],
				],

				'stderr' => [
					'driver' => 'monolog',
					'level' => env( 'LOG_LEVEL', 'debug' ),
					'handler' => StreamHandler::class,
					'formatter' => env( 'LOG_STDERR_FORMATTER' ),
					'with' => [
						'stream' => 'php://stderr',
					],
					'processors' => [ PsrLogMessageProcessor::class ],
				],

				'syslog' => [
					'driver' => 'syslog',
					'level' => env( 'LOG_LEVEL', 'debug' ),
					'facility' => LOG_USER,
					'replace_placeholders' => true,
				],

				'errorlog' => [
					'driver' => 'errorlog',
					'level' => env( 'LOG_LEVEL', 'debug' ),
					'replace_placeholders' => true,
				],

				'null' => [
					'driver' => 'monolog',
					'handler' => NullHandler::class,
				],

				'deprecations' => [
					'driver' => 'single',
					'path' => storage_path( 'logs/php-deprecation-warnings.log' ),
				],

				'emergency' => [
					'path' => ini_get( 'error_log' ) ? ini_get( 'error_log' ) : storage_path( 'logs/laravel.log' ),
				],
			],

		];
	}
}