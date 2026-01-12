<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Perform_Setup_WP_App_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Perform_Setup_WP_App_Action_Test extends Unit_Test_Case {

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
	public function test_handle_wp_app_setup_correctly() {
		// Mock the static method Yivic_Base_Helper::prepare_wp_app_folders()
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'prepare_wp_app_folders' )
			->once()
			->andReturn( true );

		// Mock Artisan class instead of the facade
		$artisan_mock = Mockery::mock( 'overload:Illuminate\Support\Facades\Artisan' );
		$artisan_mock->shouldReceive( 'call' )
			->once()
			->with( 'wp-app:setup', [] )
			->andReturn( 0 ); // Simulate successful command execution

		$artisan_mock->shouldReceive( 'output' )
			->once()
			->andReturn( 'Setup completed successfully' );

		// Act: Execute the handle method
		$job = new Perform_Setup_WP_App_Action();
		ob_start(); // Start output buffering to capture echoed output
		$job->handle();
		$output = ob_get_clean(); // Get the captured output

		// Assert: Check if the output is what we expect
		$this->assertEquals( 'Setup completed successfully', $output );
	}
}