<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Process_WP_App_Request_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use WP_Mock;

class Process_WP_App_Request_Action_Test extends Unit_Test_Case {

	public $http_kernel_handle;
	public $http_kernel_terminate;

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
		// Mocking app and request helper functions
		$kernel_mock = Mockery::mock( \Illuminate\Contracts\Http\Kernel::class );
		$request_mock = Mockery::mock( \Yivic_Base\App\Http\Request::class );
		$response_mock = Mockery::mock();

		// Mock the app()->make() call to return the kernel mock
		WP_Mock::userFunction(
			'app',
			[
				'return' => Mockery::mock()->shouldReceive( 'make' )->with( \Illuminate\Contracts\Http\Kernel::class )->andReturn( $kernel_mock )->getMock(),
			]
		);

		// Mock the request() call to return the request mock
		WP_Mock::userFunction(
			'request',
			[
				'return' => $request_mock,
			]
		);

		// Expect the kernel to handle the request and return the response
		$kernel_mock->shouldReceive( 'handle' )->with( $request_mock )->andReturn( $response_mock )->once();
		$kernel_mock->shouldReceive( 'terminate' )->with( $request_mock, $response_mock )->once();

		// Expect the response to send the output
		$response_mock->shouldReceive( 'send' )->once();

		// Mock the terminate_request method to prevent actual exit
		$action = Mockery::mock( Process_WP_App_Request_Action::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$action->shouldReceive( 'terminate_request' )->once();

		// Execute the action
		$action->handle();

		$this->assertTrue( true );
	}
}