<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Schedule_Run_Backup_Action;
use Illuminate\Console\Scheduling\Schedule;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Schedule_Run_Backup_Action_Test extends Unit_Test_Case {

	/**
	 * @var Mockery\MockInterface|Schedule
	 */
	protected $schedule;

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_constructor_initializes_schedule(): void {
		// Test that the schedule passed in the constructor is properly assigned
		/** @var Schedule $schedule_mock */
		$schedule_mock = Mockery::mock( Schedule::class );

		// Instantiate the Schedule_Run_Backup class
		$job = new Schedule_Run_Backup_Action( $schedule_mock );
		$schedule = $this->get_protected_property_value( $job, 'schedule' );

		// Assert that the $schedule property is assigned correctly
		$this->assertSame( $schedule_mock, $schedule );
	}

	public function test_handle_schedules_all_commands(): void {

		// Mock the Schedule object
		$this->schedule = Mockery::mock( Schedule::class );

		// Mocking 'backup:clean' command
		$this->schedule->shouldReceive( 'command' )
			->with( 'backup:clean' )
			->once()
			->andReturnSelf();
		$this->schedule->shouldReceive( 'daily' )
			->once()
			->andReturnSelf();
		$this->schedule->shouldReceive( 'at' )
			->with( '01:00' )
			->once();

		// Mocking 'backup:run' command
		$this->schedule->shouldReceive( 'command' )
			->with( 'backup:run' )
			->once()
			->andReturnSelf();
		$this->schedule->shouldReceive( 'daily' )
			->once()
			->andReturnSelf();
		$this->schedule->shouldReceive( 'at' )
			->with( '02:00' )
			->once();

		// Mocking 'telescope:prune' command
		$this->schedule->shouldReceive( 'command' )
			->with( 'telescope:prune' )
			->once()
			->andReturnSelf();
		$this->schedule->shouldReceive( 'daily' )
			->once()
			->andReturnSelf();
		$this->schedule->shouldReceive( 'at' )
			->with( '03:00' )
			->once();

		// Instantiate the Schedule_Run_Backup class with the mocked schedule
		$job = new Schedule_Run_Backup_Action( $this->schedule );

		// Call the handle method
		$job->handle();

		// Assertions are handled by the Mockery expectations set up in setUp
		// The Mockery framework will automatically check if the commands were called with the expected arguments
		$this->assertTrue( true );
	}
}