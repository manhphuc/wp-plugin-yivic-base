<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Get_WP_App_Info_Action;
use Yivic_Base\App\Actions\WP_CLI\Show_Basic_Info_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use WP_CLI;

class Show_Basic_Info_Action_Test extends Unit_Test_Case {


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
		// Mock the output of Get_WP_App_Info_Action::exec
		$app_info = [
			'name' => 'Test App',
			'version' => '1.0.0',
			'author' => 'Test Author',
		];

		// Mock the static call to Get_WP_App_Info_Action::exec
		$get_wp_app_info_action_mock = Mockery::mock( 'alias:' . Get_WP_App_Info_Action::class );
		$get_wp_app_info_action_mock->shouldReceive( 'exec' )
			->once()
			->andReturn( $app_info );

		// Mock WP_CLI::success to intercept the success messages
		$wp_cli_mock = Mockery::mock( 'alias:' . WP_CLI::class );
		foreach ( $app_info as $key => $value ) {
			$wp_cli_mock->shouldReceive( 'success' )
				->once()
				->with( "Key $key: $value" );
		}

		// Create a partial mock for Show_Basic_Info_Action to mock exit_with_status
		$action_mock = Mockery::mock( Show_Basic_Info_Action::class )
			->makePartial()
			->shouldAllowMockingProtectedMethods();

		$action_mock->shouldReceive( 'exit_with_status' )
			->once()
			->with( 0 );

		// Run the handle method
		$action_mock->handle();

		$this->assertTrue( true );
	}
}
