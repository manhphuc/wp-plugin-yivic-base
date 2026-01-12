<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;

/**
 * @method static function exec(): void
 */
class Init_WP_App_Kernels_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		$wp_app = app();
		$wp_app['env'] = config( 'app.env' );

		$wp_app->singleton(
			\Illuminate\Contracts\Http\Kernel::class,
			\Yivic_Base\App\Http\Kernel::class
		);

		$wp_app->singleton(
			\Illuminate\Contracts\Console\Kernel::class,
			\Yivic_Base\App\Console\Kernel::class
		);

		$wp_app->singleton(
			\Illuminate\Contracts\Debug\ExceptionHandler::class,
			\Yivic_Base\App\Exceptions\Handler::class
		);
	}
}