<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Write_Setup_Client_Script_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use stdClass;
use WP_Mock;

class Write_Setup_Client_Script_Action_Test extends Unit_Test_Case {

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
	public function test_handle_outputs_script_when_conditions_are_met() {
		// Mock the global current_screen object
		global $current_screen;
		$current_screen = new stdClass();
		$current_screen->id = 'plugins';
		$current_screen->parent_file = 'plugins.php';

		// Mock the $_GET variable
		$_GET['activate'] = 'true';

		// Mock is_admin to return true
		WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
				'times' => 1,
			]
		);

		// Mock Yivic_Base_Helper::route_with_wp_url static method
		$setup_url = 'https://example.com/wp-app/setup-app?force_app_running_in_console=1';
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'route_with_wp_url' )
			->with( 'wp-app::setup-app', [ 'force_app_running_in_console' => 1 ] )
			->andReturn( $setup_url )
			->once();

		// Mock esc_js to return the URL as it is
		WP_Mock::userFunction(
			'esc_js',
			[
				'return' => $setup_url,
				'args' => $setup_url,
				'times' => 1,
			]
		);

		// Capture the output
		ob_start();

		// Create the action instance and run the handle method
		$action = new Write_Setup_Client_Script_Action();
		$action->handle();

		$output = ob_get_clean();

		// Assert the expected output contains the correct script
		$script = '<script type="text/javascript">';
		$script .= 'var yivic_base_setup_url = \'' . $setup_url . '\';';
		$script .= 'if (typeof(jQuery) !== "undefined") {';
		$script .= 'jQuery.ajax({ url: yivic_base_setup_url, method: "GET" });';
		$script .= '} else {';
		$script .= 'fetch(yivic_base_setup_url);';
		$script .= '}';
		$script .= '</script>';

		$this->assertEquals( $script, $output );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle_does_not_output_script_when_conditions_are_not_met() {
		// Mock the global current_screen object with different values
		global $current_screen;
		$current_screen = new stdClass();
		$current_screen->id = 'dashboard';
		$current_screen->parent_file = 'index.php';

		// Mock the $_GET variable
		unset( $_GET['activate'] );

		// Mock is_admin to return true
		WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
				'times' => 1,
			]
		);

		// Capture the output
		ob_start();

		// Create the action instance and run the handle method
		$action = new Write_Setup_Client_Script_Action();
		$action->handle();

		$output = ob_get_clean();

		// Assert that no script is outputted
		$this->assertEmpty( $output );
	}
}