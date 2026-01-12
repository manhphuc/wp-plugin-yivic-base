<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Get_WP_App_Info_Action;
use Yivic_Base\App\Actions\Put_Setup_Error_Message_To_Log_File_Action;
use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use Monolog\Logger;

class Put_Setup_Error_Message_To_Log_File_Action_Test extends Unit_Test_Case {

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
		// Mock the application
		$app_mock = $this->createMock( WP_Application::class );
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ 'path.config', [], DIRECTORY_SEPARATOR . 'config' ],
				[ 'path.storage', [], DIRECTORY_SEPARATOR . 'storage' ],
			]
		);
		WP_Application::setInstance( $app_mock );

		// Mock Get_WP_App_Info_Action
		$wp_app_info = [ 'key' => 'value' ];
		$app_info_mock = Mockery::mock( 'alias:' . Get_WP_App_Info_Action::class );
		$app_info_mock->shouldReceive( 'exec' )->once()->andReturn( $wp_app_info );

		// Mock Logger
		$logger_mock = Mockery::mock( Logger::class );
		$logger_mock->shouldReceive( 'pushHandler' )->once();
		$logger_mock->shouldReceive( 'warning' )->with( '========= Errors from Setup app ============' )->once();
		$logger_mock->shouldReceive( 'error' )->with( 'Test error message' )->once();
		$logger_mock->shouldReceive( 'info' )->with( devvard( $wp_app_info, 5, false ) )->once();
		$logger_mock->shouldReceive( 'info' )->with( devvard( get_loaded_extensions(), 5, false ) )->once();
		$logger_mock->shouldReceive( 'warning' )->with( '========= /Errors from Setup app ===========' )->once();

		// Inject the mock logger
		$action = new Put_Setup_Error_Message_To_Log_File_Action( 'Test error message', $logger_mock );
		$action->handle();

		// Assert the test passed
		$this->assertTrue( true );
	}
}