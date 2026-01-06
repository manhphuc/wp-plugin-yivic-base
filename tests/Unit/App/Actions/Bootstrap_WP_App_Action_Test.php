<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Bootstrap_WP_App_Action;
use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\App\Actions\Bootstrap_WP_App_Action_Test\Console_Kernel_Tmp;
use Yivic_Base\Tests\Unit\App\Actions\Bootstrap_WP_App_Action_Test\Http_Kernel_Tmp;
use Mockery;
use WP_Mock;

class Bootstrap_WP_App_Action_Test extends Unit_Test_Case {

	protected $config;
	public static $console_kernel_bootstrap;
	public static $http_kernel_bootstrap;
	public static $http_kernel_capture_request;


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
	 * @preserveGlobalState disabled
	 */
	public function test_handle_in_console_mode() {
		// Mock the global config() function
		WP_Mock::userFunction(
			'config',
			[
				'return' => $this->config,
			]
		);

		$config_mock = $this->config;
		$config_mock->method( 'get' )
			->with( 'app.env' )
			->willReturn( 'production' );

		/** @var WP_Application $app_mock */
		$app_mock = $this->createMock( WP_Application::class );
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ \Illuminate\Contracts\Console\Kernel::class, [], new Console_Kernel_Tmp() ],
			]
		);
		$app_mock->expects( $this->once() )
			->method( 'detectEnvironment' )
			->willReturnCallback(
				function ( $callback ) use ( $config_mock ) {
					// Verify the closure works as expected and returns the environment value
					$env = $callback();
					$this->assertEquals( 'production', $env );
				}
			);

		WP_Application::setInstance( $app_mock );

		// Mock static method is_console_mode
		Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' )
			->shouldReceive( 'is_console_mode' )
			->andReturn( true );

		// Initialize and handle action
		$action = new Bootstrap_WP_App_Action();
		$action->handle();

		$this->assertEquals( 'console_kernel_bootstrap', static::$console_kernel_bootstrap );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle_not_in_console_mode() {
		// Mock the global config() function
		WP_Mock::userFunction(
			'config',
			[
				'return' => $this->config,
			]
		);

		$config_mock = $this->config;
		$config_mock->method( 'get' )
			->with( 'app.env' )
			->willReturn( 'production' );

		/** @var WP_Application $app_mock */
		$app_mock = $this->createMock( WP_Application::class );
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ \Illuminate\Contracts\Http\Kernel::class, [], new Http_Kernel_Tmp() ],
			]
		);
		$app_mock->expects( $this->once() )
			->method( 'detectEnvironment' )
			->willReturnCallback(
				function ( $callback ) use ( $config_mock ) {
					// Verify the closure works as expected and returns the environment value
					$env = $callback();
					$this->assertEquals( 'production', $env );
				}
			);

		WP_Application::setInstance( $app_mock );

		// Mock static method is_console_mode
		Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' )
			->shouldReceive( 'is_console_mode' )
			->andReturn( false );

		// Initialize and handle action
		$action = new Bootstrap_WP_App_Action();
		$action->handle();

		$this->assertEquals( 'http_kernel_bootstrap', static::$http_kernel_bootstrap );
		$this->assertEquals( 'http_kernel_capture_request', static::$http_kernel_capture_request );
	}
}

namespace Yivic_Base\Tests\Unit\App\Actions\Bootstrap_WP_App_Action_Test;

use Yivic_Base\Tests\Unit\App\Actions\Bootstrap_WP_App_Action_Test;

class Console_Kernel_Tmp {
	public function bootstrap() {
		Bootstrap_WP_App_Action_Test::$console_kernel_bootstrap = 'console_kernel_bootstrap';
	}
}

class Http_Kernel_Tmp {
	public function bootstrap() {
		Bootstrap_WP_App_Action_Test::$http_kernel_bootstrap = 'http_kernel_bootstrap';
	}
	public function capture_request() {
		Bootstrap_WP_App_Action_Test::$http_kernel_capture_request = 'http_kernel_capture_request';
	}
}
