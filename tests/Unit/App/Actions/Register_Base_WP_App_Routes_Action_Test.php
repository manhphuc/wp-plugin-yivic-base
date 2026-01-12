<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Register_Base_WP_App_Routes_Action;
use Illuminate\Support\Facades\Route;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Register_Base_WP_App_Routes_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();

		// Mocking Route facade for testing
		Route::shouldReceive( 'get' )
			->with( '/', [ \Yivic_Base\App\Http\Controllers\Main_Controller::class, 'index' ] )
			->once();

		Route::shouldReceive( 'get' )
			->with( 'home', [ \Yivic_Base\App\Http\Controllers\Main_Controller::class, 'home' ] )
			->once();

		Route::shouldReceive( 'get' )
			->with( 'setup-app', [ \Yivic_Base\App\Http\Controllers\Main_Controller::class, 'setup_app' ] )
			->once()
			->andReturnSelf();
		Route::shouldReceive( 'name' )
			->with( 'setup-app' )
			->once();

		// TODO: Adjust test to cover closure function with prefix
		// For dashboard routes
		Route::shouldReceive( 'group' )
			->once();

		// For admin routes
		Route::shouldReceive( 'group' )
			->once();

		// For API routes
		Route::shouldReceive( 'group' )
			->once();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_handle() {
		// Execute the handle method
		$action = new Register_Base_WP_App_Routes_Action();
		$action->handle();

		// Assert that the route methods were called the expected number of times
		$this->assertTrue( true );
	}
}