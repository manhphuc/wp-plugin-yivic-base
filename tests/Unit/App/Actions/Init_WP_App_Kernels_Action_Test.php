<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Init_WP_App_Kernels_Action;
use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use WP_Mock;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;

class Init_WP_App_Kernels_Action_Test extends Unit_Test_Case {

	protected $config;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock( \Illuminate\Config\Repository::class );
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_handle() {
		// Mock the global config() function
		WP_Mock::userFunction(
			'config',
			[
				'return' => $this->config,
			]
		);

		/** @var WP_Application $app_mock */
		$app_mock = $this->createMock( WP_Application::class );

		// Create a mock for the Http Kernel class
		$http_kernel_instance = $this->createMock( \Yivic_Base\App\Http\Kernel::class );

		// Create a mock for the Console Kernel class
		$console_kernel_instance = $this->createMock( \Yivic_Base\App\Console\Kernel::class );

		// Create a mock for the Exception Handler class
		$exception_handler_instance = $this->createMock( \Yivic_Base\App\Exceptions\Handler::class );

		// Mock the `make` method to always return the same instances
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ HttpKernel::class, [], $http_kernel_instance ],
				[ ConsoleKernel::class, [], $console_kernel_instance ],
				[ ExceptionHandler::class, [], $exception_handler_instance ],
			]
		);

		// Set the mocked WP_Application instance globally
		WP_Application::setInstance( $app_mock );

		// Initialize and handle action
		$action = new Init_WP_App_Kernels_Action();
		$action->handle();

		// Verify the singleton registrations
		$this->assertSame( $http_kernel_instance, $app_mock->make( HttpKernel::class ) );
		$this->assertSame( $console_kernel_instance, $app_mock->make( ConsoleKernel::class ) );
		$this->assertSame( $exception_handler_instance, $app_mock->make( ExceptionHandler::class ) );
	}
}
