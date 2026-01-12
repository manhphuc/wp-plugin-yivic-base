<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Http\Controllers\Admin\Main_Controller as Admin_Main_Controller;
use Yivic_Base\App\Http\Controllers\Api\Main_Controller as Api_Main_Controller;
use Yivic_Base\App\Http\Controllers\Main_Controller;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Illuminate\Support\Facades\Route;

/**
 * @method static function exec(): void
 */
class Register_Base_WP_App_Routes_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		// We need to add the trailing slash to the 'uri' to match the WP rewrite rule
		// For Frontend
		Route::get( '/', [ Main_Controller::class, 'index' ] );
		Route::get( 'home', [ Main_Controller::class, 'home' ] );
		Route::get( 'setup-app', [ Main_Controller::class, 'setup_app' ] )->name( 'setup-app' );

		// For Logged in User and redirect to login if not logged in
		Route::group(
			[
				'prefix' => '/dashboard',
				'middleware' => [
					'auth',
				],
			],
			function () {
				Route::get( '/', [ Admin_Main_Controller::class, 'home' ] );
			}
		);

		// For Admin, if not, throw 403
		Route::group(
			[
				'prefix' => '/admin',
				'middleware' => [
					'wp_user_can_and:administrator',
				],
			],
			function () {
				Route::get( 'setup-app', [ Admin_Main_Controller::class, 'setup_app' ] )->name( 'admin-setup-app' );
			}
		);

		// For API
		Route::group(
			[
				'prefix' => '/api',
			],
			function () {
				Route::get( '/', [ Api_Main_Controller::class, 'home' ] );
			}
		);
	}
}