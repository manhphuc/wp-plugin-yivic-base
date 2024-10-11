<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Hashing\HashServiceProvider;

class Hash_Service_Provider extends HashServiceProvider {
	public function register() {
		$this->fetch_config();
		parent::register();
	}

	protected function fetch_config(): void {
		config(
			[
				'hashing' => apply_filters(
					App_Const::FILTER_WP_APP_HASHING_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		return [

			/*
			|--------------------------------------------------------------------------
			| Default Hash Driver
			|--------------------------------------------------------------------------
			|
			| This option controls the default hash driver that will be used to hash
			| passwords for your application. By default, the bcrypt algorithm is
			| used; however, you remain free to modify this option if you wish.
			|
			| Supported: "bcrypt", "argon", "argon2id"
			|
			*/

			'driver' => 'bcrypt',

			/*
			|--------------------------------------------------------------------------
			| Bcrypt Options
			|--------------------------------------------------------------------------
			|
			| Here you may specify the configuration options that should be used when
			| passwords are hashed using the Bcrypt algorithm. This will allow you
			| to control the amount of time it takes to hash the given password.
			|
			*/

			'bcrypt' => [
				'rounds' => env( 'WP_APP_BCRYPT_ROUNDS', 12 ),
				'verify' => true,
			],

			/*
			|--------------------------------------------------------------------------
			| Argon Options
			|--------------------------------------------------------------------------
			|
			| Here you may specify the configuration options that should be used when
			| passwords are hashed using the Argon algorithm. These will allow you
			| to control the amount of time it takes to hash the given password.
			|
			*/

			'argon' => [
				'memory' => 65536,
				'threads' => 1,
				'time' => 4,
				'verify' => true,
			],

		];
	}
}
