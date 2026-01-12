<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers\Support;

use Yivic_Base\App\Console\Commands\Tinker\Tinker_Command;
use Yivic_Base\App\Support\App_Const;
use Laravel\Tinker\TinkerServiceProvider;

class Tinker_Service_Provider extends TinkerServiceProvider {
	public function register() {
		$this->fetch_config();

		$this->app->singleton(
			'command.tinker',
			function () {
				return new Tinker_Command();
			}
		);

		$this->commands( [ 'command.tinker' ] );
	}

	protected function fetch_config(): void {
		config(
			[
				'tinker' => apply_filters(
					App_Const::FILTER_WP_APP_TINKER_CONFIG,
					$this->get_default_config()
				),
			]
		);
	}

	protected function get_default_config(): array {
		$config = [
			/*
			|--------------------------------------------------------------------------
			| Console Commands
			|--------------------------------------------------------------------------
			|
			| This option allows you to add additional Artisan commands that should
			| be available within the Tinker environment. Once the command is in
			| this array you may execute the command in Tinker using its name.
			|
			*/

			'commands' => [
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// App\Console\Commands\ExampleCommand::class,
			],

			/*
			|--------------------------------------------------------------------------
			| Auto Aliased Classes
			|--------------------------------------------------------------------------
			|
			| Tinker will not automatically alias classes in your vendor namespaces
			| but you may explicitly allow a subset of classes to get aliased by
			| adding the names of each of those classes to the following list.
			|
			*/

			'alias' => [
				//
			],

			/*
			|--------------------------------------------------------------------------
			| Classes That Should Not Be Aliased
			|--------------------------------------------------------------------------
			|
			| Typically, Tinker automatically aliases classes as you require them in
			| Tinker. However, you may wish to never alias certain classes, which
			| you may accomplish by listing the classes in the following array.
			|
			*/

			'dont_alias' => [
				'App\Nova',
			],

		];

		return $config;
	}
}