<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Write_Web_Worker_Script_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Write_Web_Worker_Script_Action_Test extends Unit_Test_Case {

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
	public function test_handle_outputs_web_worker_script_when_enabled() {
		// Mock Yivic_Base_Helper::disable_web_worker to return false
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'disable_web_worker' )
			->once()
			->andReturn( false );

		// Mock the route_with_wp_url to return a known URL
		$web_worker_url = 'https://example.com/wp-api/web-worker/';
		$helper_mock->shouldReceive( 'route_with_wp_url' )
			->with( 'wp-api::web-worker' )
			->andReturn( $web_worker_url )
			->once();

		// Capture the output
		ob_start();

		// Create the action instance and run the handle method
		$action = new Write_Web_Worker_Script_Action();
		$action->handle();

		$output = ob_get_clean();

		// Assert the expected output contains the correct script
		$script = '<script type="text/javascript">';
		$script .= 'var yivic_base_web_worker_url = \'' . $web_worker_url . '\';';
		$script .= 'function ajax_request_to_web_worker() {';
		$script .= 'if (typeof(jQuery) !== "undefined") {';
		$script .= 'jQuery.ajax({ url: yivic_base_web_worker_url, method: "POST" });';
		$script .= '} else {';
		$script .= 'fetch(yivic_base_web_worker_url);';
		$script .= '}';
		$script .= '}';
		$script .= 'var ajax_request_to_web_worker_interval = window.setInterval(function(){';
		$script .= 'ajax_request_to_web_worker();';
		$script .= '}, 7*7*60*1000);';
		$script .= 'window.setTimeout(function() {';
		$script .= 'ajax_request_to_web_worker();';
		$script .= '}, 1000);';
		$script .= '</script>';

		$this->assertEquals( $script, $output );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle_does_not_output_script_when_disabled() {
		// Mock Yivic_Base_Helper::disable_web_worker to return true
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'disable_web_worker' )
			->once()
			->andReturn( true );

		// Capture the output
		ob_start();

		// Create the action instance and run the handle method
		$action = new Write_Web_Worker_Script_Action();
		$action->handle();

		$output = ob_get_clean();

		// Assert that no script is outputted
		$this->assertEmpty( $output );
	}
}