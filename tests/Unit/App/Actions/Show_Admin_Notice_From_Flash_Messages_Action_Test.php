<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Show_Admin_Notice_From_Flash_Messages_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Illuminate\Support\Facades\Session;
use Mockery;
use WP_Mock;

class Show_Admin_Notice_From_Flash_Messages_Action_Test extends Unit_Test_Case {

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
	public function test_handle_displays_admin_notice_for_flash_messages() {
		// Define the session keys we are testing
		$flash_keys = [ 'admin-error', 'admin-success', 'admin-info', 'admin-caution' ];

		// Mock the session for each flash key
		foreach ( $flash_keys as $key ) {
			if ( $key === 'admin-success' ) {
				Session::shouldReceive( 'has' )
					->with( $key )
					->andReturn( true )
					->once();

				Session::shouldReceive( 'get' )
					->with( $key )
					->andReturn( [ 'Operation successful' ] )
					->times( 2 );

				Session::shouldReceive( 'forget' )
					->with( $key )
					->once();
			} else {
				// All other keys should return false
				Session::shouldReceive( 'has' )
					->with( $key )
					->andReturn( false )
					->once();
			}
		}

		Session::shouldReceive( 'save' )
			->once();

		// Run the action handler
		$action = new Show_Admin_Notice_From_Flash_Messages_Action();
		$action->handle();
		$this->assertTrue( true );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle_does_not_display_notice_if_no_messages() {
		// Define the session keys we are testing
		$flash_keys = [ 'admin-error', 'admin-success', 'admin-info', 'admin-caution' ];

		// Mock the session to return false for all keys
		foreach ( $flash_keys as $key ) {
			Session::shouldReceive( 'has' )
				->with( $key )
				->andReturn( false )
				->once();
		}

		Session::shouldReceive( 'save' )
			->once();

		// Ensure add_action is never called
		WP_Mock::userFunction(
			'add_action',
			[
				'times' => 0,
			]
		);

		// Run the action handler
		$action = new Show_Admin_Notice_From_Flash_Messages_Action();
		$action->handle();

		$this->assertTrue( true );
	}
}