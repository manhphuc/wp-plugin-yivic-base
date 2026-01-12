<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Mark_Setup_WP_App_Failed_Action;
use Yivic_Base\App\Support\App_Const;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;
use WP_Mock;


class Mark_Setup_WP_App_Failed_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_handle() {
		$failed_message = 'failed';

		// Arrange: Mock the WordPress functions
		WP_Mock::userFunction(
			'update_option',
			[
				'times' => 1,
				'args' => [ App_Const::OPTION_SETUP_INFO, 'failed', false ],
			]
		);

		WP_Mock::expectAction( App_Const::ACTION_WP_APP_MARK_SETUP_APP_FAILED, $failed_message );

		// Act: Execute the handle method
		$action = new Mark_Setup_WP_App_Failed_Action( $failed_message );
		$action->handle();

		// Assert: Ensure that the functions are called as expected
		WP_Mock::assertActionsCalled();
	}
}