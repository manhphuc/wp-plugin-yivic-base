<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\App\Auth\Auth_Manager;
use Yivic_Base\App\Models\User;
use Yivic_Base\App\Support\App_Const;
use Illuminate\Auth\AuthServiceProvider;

class Auth_Service_Provider extends AuthServiceProvider {
	public function register() {
		$this->fetch_config();
		parent::register();
	}

	/**
	 * @inheritDoc
	 *
	 * @return void
	 */
	protected function registerAuthenticator() {
		$this->app->singleton(
			'auth',
			function ( $app ) {
				return new Auth_Manager( $app );
			}
		);

		$this->app->singleton(
			'auth.driver',
			function ( $app ) {
				return $app['auth']->guard();
			}
		);
	}

	protected function fetch_config(): void {
		config(
			[
				'auth' => apply_filters(
					App_Const::FILTER_WP_APP_AUTH_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		return [

			/*
			|--------------------------------------------------------------------------
			| Authentication Defaults
			|--------------------------------------------------------------------------
			|
			| This option controls the default authentication "guard" and password
			| reset options for your application. You may change these defaults
			| as required, but they're a perfect start for most applications.
			|
			*/

			'defaults' => [
				'guard' => 'web',
				'passwords' => 'users',
			],

			/*
			|--------------------------------------------------------------------------
			| Authentication Guards
			|--------------------------------------------------------------------------
			|
			| Next, you may define every authentication guard for your application.
			| Of course, a great default configuration has been defined for you
			| here which uses session storage and the Eloquent user provider.
			|
			| All authentication drivers have a user provider. This defines how the
			| users are actually retrieved out of your database or other storage
			| mechanisms used by this application to persist your user's data.
			|
			| Supported: "session"
			|
			*/
			'guards' => [
				'web' => [
					'driver' => 'session',
					'provider' => 'users',
				],
				'web-is-administrator' => [
					'driver' => 'session',
					'provider' => 'users',
				],
				'api' => [
					'driver' => 'passport',
					'provider' => 'users',
				],
			],

			/*
			|--------------------------------------------------------------------------
			| User Providers
			|--------------------------------------------------------------------------
			|
			| All authentication drivers have a user provider. This defines how the
			| users are actually retrieved out of your database or other storage
			| mechanisms used by this application to persist your user's data.
			|
			| If you have multiple user tables or models you may configure multiple
			| sources which represent each model / table. These sources may then
			| be assigned to any extra authentication guards you have defined.
			|
			| Supported: "database", "eloquent"
			|
			*/

			'providers' => [
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				'users' => [
					'driver' => 'eloquent',
					'model' => User::class,
				],
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// 'users' => [
				//  'driver' => 'database',
				//  'table' => 'users',
				// ],
			],

			/*
			|--------------------------------------------------------------------------
			| Resetting Passwords
			|--------------------------------------------------------------------------
			|
			| You may specify multiple password reset configurations if you have more
			| than one user table or model in the application and you want to have
			| separate password reset settings based on the specific user types.
			|
			| The expiry time is the number of minutes that each reset token will be
			| considered valid. This security feature keeps tokens short-lived so
			| they have less time to be guessed. You may change this as needed.
			|
			| The throttle setting is the number of seconds a user must wait before
			| generating more password reset tokens. This prevents the user from
			| quickly generating a very large amount of password reset tokens.
			|
			*/

			'passwords' => [
				'users' => [
					'provider' => 'users',
					'table' => 'password_reset_tokens',
					'expire' => 60,
					'throttle' => 60,
				],
			],

			/*
			|--------------------------------------------------------------------------
			| Password Confirmation Timeout
			|--------------------------------------------------------------------------
			|
			| Here you may define the amount of seconds before a password confirmation
			| times out and the user is prompted to re-enter their password via the
			| confirmation screen. By default, the timeout lasts for three hours.
			|
			*/

			'password_timeout' => 10800,
		];
	}
}
