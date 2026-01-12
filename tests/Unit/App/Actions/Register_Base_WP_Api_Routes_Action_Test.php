<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Register_Base_WP_Api_Routes_Action;
use Yivic_Base\App\Http\Controllers\Api\Main_Controller;
use Illuminate\Support\Facades\Route;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Register_Base_WP_Api_Routes_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();

		// Mock the Route facade and ensure that 'match' returns a valid object for chaining
		$route_mock = Mockery::mock();
		$route_mock->shouldReceive( 'name' )
			->with( 'web-worker' )
			->once()
			->andReturnSelf();

		Route::shouldReceive( 'get' )
			->with( '/', [ Main_Controller::class, 'home' ] )
			->once();

		Route::shouldReceive( 'match' )
			->with( [ 'GET', 'POST' ], 'web-worker', [ Main_Controller::class, 'web_worker' ] )
			->once()
			->andReturn( $route_mock );
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_handle() {
		$action = new Register_Base_WP_Api_Routes_Action();
		$action->handle();

		// Assert that the routes are registered as expected
		Route::shouldHaveReceived( 'get' )->with( '/', [ Main_Controller::class, 'home' ] );
		Route::shouldHaveReceived( 'match' )->with( [ 'GET', 'POST' ], 'web-worker', [ Main_Controller::class, 'web_worker' ] );

		$this->assertTrue( true );
	}
}