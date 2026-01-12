<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Process_WP_Api_Request_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use WP_Mock;

class Process_WP_Api_Request_Action_Test extends Unit_Test_Case {

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
		$kernel_mock = Mockery::mock( \Illuminate\Contracts\Http\Kernel::class );
		$request_mock = Mockery::mock( \Yivic_Base\App\Http\Request::class );
		$response_mock = Mockery::mock();

		// Expect app()->make() to return the mocked kernel
		WP_Mock::userFunction(
			'app',
			[
				'return' => Mockery::mock()->shouldReceive( 'make' )->with( \Illuminate\Contracts\Http\Kernel::class )->andReturn( $kernel_mock )->getMock(),
			]
		);

		// Expect request() to return the mocked request
		WP_Mock::userFunction(
			'request',
			[
				'return' => $request_mock,
			]
		);

		// Expect kernel handle() and terminate() to be called
		$kernel_mock->shouldReceive( 'handle' )->with( $request_mock )->andReturn( $response_mock )->once();
		$kernel_mock->shouldReceive( 'terminate' )->with( $request_mock, $response_mock )->once();

		// Expect response send() to be called
		$response_mock->shouldReceive( 'send' )->once();

		// Execute the action
		$action = new Process_WP_Api_Request_Action();
		$action->handle();

		$this->assertTrue( true );
	}
}