<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Logout_WP_App_User_Action;
use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Mockery;

class Logout_WP_App_User_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle() {
		/** @var WP_Application $app_mock */
		$app_mock = $this->createMock( WP_Application::class );

		// Mock session to intercept session()->save() call
		$session_mock = Mockery::mock();
		$session_mock->shouldReceive( 'save' )->once()->andReturn( true );

		// Mock the `make` method to always return the same instances
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ 'session', [], $session_mock ],
			]
		);

		// Set the mocked WP_Application instance globally
		WP_Application::setInstance( $app_mock );

		// Spy on the Auth facade to expect logoutCurrentDevice to be called once
		Auth::shouldReceive( 'logoutCurrentDevice' )->once();

		// Run the action
		$action = new Logout_WP_App_User_Action();
		$action->handle();

		$this->assertTrue( true );
	}
}
