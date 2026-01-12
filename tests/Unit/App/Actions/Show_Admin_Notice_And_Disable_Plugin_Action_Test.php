<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Show_Admin_Notice_And_Disable_Plugin_Action;
use Yivic_Base\Foundation\WP\WP_Plugin_Interface;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use WP_Mock;
use Illuminate\Support\Facades\Session;

class Show_Admin_Notice_And_Disable_Plugin_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_construct() {
		$plugin_mock = $this->createMock( WP_Plugin_Interface::class );
		$extra_messages = [ 'Message 1', 'Message 2' ];

		// Instantiate the action class
		$action = new Show_Admin_Notice_And_Disable_Plugin_Action( $plugin_mock, $extra_messages );

		$plugin_property = $this->get_protected_property_value( $action, 'plugin' );
		$extra_messages_property = $this->get_protected_property_value( $action, 'extra_messages' );

		// Assert that the plugin and extra_messages properties are set correctly
		$this->assertSame( $plugin_mock, $plugin_property );
		$this->assertSame( $extra_messages, $extra_messages_property );
	}

	public function test_handle() {
		// Create plugin mock
		$plugin_name = 'Test Plugin';
		$plugin_version = '1.0.0';
		$plugin_basename = 'test-plugin/test-plugin.php';

		$plugin_mock = Mockery::mock( WP_Plugin_Interface::class );
		$plugin_mock->shouldReceive( 'get_name' )
			->andReturn( $plugin_name )
			->once();

		$plugin_mock->shouldReceive( 'get_version' )
			->andReturn( $plugin_version )
			->once();

		$plugin_mock->shouldReceive( 'get_plugin_basename' )
			->andReturn( $plugin_basename )
			->once();

		// Mock session behavior
		Session::shouldReceive( 'push' )
			->with( 'caution', 'Custom message' )
			->once();

		// Ensure the dynamic part of the message matches the actual data
		$expected_message = sprintf(
			'Plugin <strong>%s %s</strong> is disabled.',
			$plugin_name,
			$plugin_version
		);

		Session::shouldReceive( 'push' )
			->with( 'caution', $expected_message )
			->once();

		// Mock WordPress function to deactivate plugin
		WP_Mock::userFunction(
			'deactivate_plugins',
			[
				'args' => [ 'test-plugin/test-plugin.php' ],
				'times' => 1,
			]
		);

		// Create partial mock of the action to mock the load_plugin_file method
		$action_mock = Mockery::mock( Show_Admin_Notice_And_Disable_Plugin_Action::class, [ $plugin_mock, [ 'Custom message' ] ] )
			->makePartial()
			->shouldAllowMockingProtectedMethods();
		// Expect the load_plugin_file method to be called
		$action_mock->shouldReceive( 'load_plugin_file' )
			->once()
			->andReturnNull();

		$action_mock->handle();

		$this->assertTrue( true );
	}
}