<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Login_WP_App_User_Action;
use Yivic_Base\App\Models\User;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Illuminate\Support\Facades\Auth;
use Mockery;

class Login_WP_App_User_Action_Test extends Unit_Test_Case {


	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_handle() {
		// Mock the User model's findOrFail method to return a user instance
		$user_mock = Mockery::mock( 'alias:' . User::class );
		$user_mock->shouldReceive( 'findOrFail' )->with( 1 )->andReturn( $user_mock );

		// Mock the Auth facade to expect login to be called with the user mock
		Auth::shouldReceive( 'login' )->once()->with( $user_mock );

		// Instantiate the action with a test user ID and call handle
		$action = new Login_WP_App_User_Action( 1 );
		$action->handle();

		$this->assertTrue( true );
	}
}
