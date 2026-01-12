<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Http\Controllers\Api\Main_Controller;
use Yivic_Base\Foundation\Actions\Base_Action;
use Illuminate\Support\Facades\Route;
use Yivic_Base\Foundation\Support\Executable_Trait;

/**
 * @method static function exec(): void
 */
class Register_Base_WP_Api_Routes_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		// For API
		Route::get( '/', [ Main_Controller::class, 'home' ] );
		Route::match( [ 'GET', 'POST' ], 'web-worker', [ Main_Controller::class, 'web_worker' ] )->name( 'web-worker' );
	}
}