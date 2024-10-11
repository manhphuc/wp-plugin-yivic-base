<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Perform_Web_Worker_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\App\Actions\Perform_Web_Worker_Action_Test\Perform_Web_Worker_Action_Test_Queue_Tmp;
use Mockery;
use WP_Mock;

class Perform_Web_Worker_Action_Test extends Unit_Test_Case {

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
	public function test_handle_does_not_call_artisan_when_web_worker_is_disabled() {
		// Arrange: Mock Yivic_Base_Helper::disable_web_worker to return true
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'disable_web_worker' )
			->once()
			->andReturn( true );

		$artisan_mock = Mockery::mock( 'overload:Illuminate\Support\Facades\Artisan' );

		// Act: Execute the handle method
		$action = new Perform_Web_Worker_Action();
		$action->handle();

		// Assert: Ensure Artisan::call is never invoked when the web worker is disabled
		$artisan_mock->shouldNotHaveReceived( 'call' );
		$this->assertTrue( true );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle() {
		// Arrange: Mock the WordPress functions
		WP_Mock::userFunction(
			'get_current_blog_id',
			[
				'times' => 2,
			]
		);

		// Arrange: Mock Yivic_Base_Helper::disable_web_worker to return false
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'disable_web_worker' )
			->once()
			->andReturn( false );

		$queue_trait_class = new Perform_Web_Worker_Action_Test_Queue_Tmp();
		$site_database_queue_connection = $this->invoke_protected_method( $queue_trait_class, 'get_site_database_queue_connection', [] );
		$site_default_queue = $this->invoke_protected_method( $queue_trait_class, 'get_site_default_queue', [] );
		$queue_backoff = $this->invoke_protected_method( $queue_trait_class, 'get_queue_backoff', [] );

		$artisan_mock = Mockery::mock( 'overload:Illuminate\Support\Facades\Artisan' );
		$artisan_mock->shouldReceive( 'call' )
			->once()
			->with(
				'queue:work',
				[
					'connection' => $site_database_queue_connection,
					'--queue' => $site_default_queue,
					'--tries' => 1,
					'--backoff' => $queue_backoff,
					'--quiet' => true,
					'--stop-when-empty' => true,
					'--timeout' => 60,
					'--memory' => 256,
				]
			)
			->andReturn( true );

		// Act: Execute the handle method
		$action = new Perform_Web_Worker_Action();
		$action->handle();

		// Assert: Ensure Artisan::call is invoked when the web worker is enabled
		$artisan_mock->shouldHaveReceived( 'call' );
		$this->assertTrue( true );
	}
}


namespace Yivic_Base\Tests\Unit\App\Actions\Perform_Web_Worker_Action_Test;

use Yivic_Base\App\Support\Traits\Queue_Trait;

class Perform_Web_Worker_Action_Test_Queue_Tmp {
	use Queue_Trait;
}
