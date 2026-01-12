<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;

/**
 * @method static function exec(): void
 */
class Process_WP_Api_Request_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		/** @var \Yivic_Base\App\Http\Kernel $kernel */
		$kernel = app()->make( \Illuminate\Contracts\Http\Kernel::class );

		// We don't want to re-capture the request because we did that on WP_App_Bootstrap
		/** @var \Yivic_Base\App\Http\Request $request */
		$request = request();
		$response = $kernel->handle( $request );
		$response->send();

		$kernel->terminate( $request, $response );
	}
}