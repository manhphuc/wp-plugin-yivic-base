<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;

/**
 * @method static function exec(): void
 */
class Bootstrap_WP_App_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		/** @var \Yivic_Base\App\WP\WP_Application $wp_app  */
		$wp_app = app();
		$wp_app['env'] = config( 'app.env' );
		$config = config();

		if ( Yivic_Base_Helper::is_console_mode() ) {
			/** @var \Yivic_Base\App\Console\Kernel $console_kernel */
			$console_kernel = $wp_app->make( \Illuminate\Contracts\Console\Kernel::class );
			$console_kernel->bootstrap();
		} else {
			// As we may not use Contracts\Kernel::handle(), we need to call bootstrap method
			//  to iinitialize all boostrappers
			/** @var \Yivic_Base\App\Http\Kernel $http_kernel */
			$http_kernel = $wp_app->make( \Illuminate\Contracts\Http\Kernel::class );
			$http_kernel->capture_request();
			$http_kernel->bootstrap();
		}

		// As we don't use the LoadConfiguration boostrapper, we need the below snippets
		//  taken from Illuminate\Foundation\Bootstrap\LoadConfiguration
		$wp_app->detectEnvironment(
			function () use ( $config ) {
				return $config->get( 'app.env', 'production' );
			}
		);
	}
}
