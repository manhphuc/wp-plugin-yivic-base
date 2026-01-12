<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Get_WP_App_Info_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Illuminate\Foundation\Application;
use WP_Mock;

class Get_WP_App_Info_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_handle() {
		// Create a mock of Get_WP_App_Info_Action
		$action = $this->getMockBuilder( Get_WP_App_Info_Action::class )
			->onlyMethods( [ 'get_php_version', 'get_wp_version' ] )
			->getMock();

		// Set up expectations for the mocked methods
		$action->expects( $this->once() )
			->method( 'get_php_version' )
			->willReturn( '8.0.3' );

		$action->expects( $this->once() )
			->method( 'get_wp_version' )
			->willReturn( '5.7.2' );

		// Define the Laravel version for this test
		$expected_laravel_version = '8.83.28';
		$this->assertSame( Application::VERSION, $expected_laravel_version ); // Mocked value

		// Call the handle method
		$info = $action->handle();

		// Expected info array
		$expected_info = [
			'php_version' => '8.0.3',
			'wp_version' => '5.7.2',
			'laravel_version' => Application::VERSION,
			'yivic_base_version' => YIVIC_BASE_PLUGIN_VERSION,
		];

		// Assert that the returned info matches the expected info
		$this->assertEquals( $expected_info, $info );
	}

	public function test_get_php_version() {
		$mock_class = new Get_WP_App_Info_Action();
		$expected_version = phpversion();
		$php_version_value = $this->invoke_protected_method( $mock_class, 'get_php_version', [] );

		$this->assertSame( $expected_version, $php_version_value );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_get_wp_version() {
		$mock_class = new Get_WP_App_Info_Action();
		// Mock the get_bloginfo() function
		$expected_wp_version = '5.7.2';
		WP_Mock::userFunction(
			'get_bloginfo',
			[
				'args' => [ 'version' ],
				'return' => $expected_wp_version,
			]
		);
		$wp_version = $this->invoke_protected_method( $mock_class, 'get_wp_version', [] );

		$this->assertSame( $expected_wp_version, $wp_version );
	}
}