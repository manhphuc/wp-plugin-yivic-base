<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Setup_WP_App_In_Console_Action;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use InvalidArgumentException;
use Mockery;

class Setup_WP_App_In_Console_Action_Test extends Unit_Test_Case {
	protected $console_command;
	protected $filesystem;


	protected function setUp(): void {
		parent::setUp();

		// Mock the Console Command
		$this->console_command = Mockery::mock( Command::class );

		// Mock the Filesystem
		$this->filesystem = Mockery::mock( Filesystem::class );
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_constructor_with_valid_command(): void {
		// Test that the constructor correctly initializes with a valid Command instance
		$action = new Setup_WP_App_In_Console_Action( $this->console_command );
		$console_command = $this->get_protected_property_value( $action, 'console_command' );

		// Assert that the console_command property is correctly assigned
		$this->assertSame( $this->console_command, $console_command );
	}

	public function test_constructor_throws_exception_if_invalid_command(): void {
		$this->expectException( InvalidArgumentException::class );
		new Setup_WP_App_In_Console_Action( 'invalid_command' );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle() {
	}

	public function test_perform_setup_actions(): void {
		// Expect comments and calls for publishing Laravel and Yivic Base migrations and assets
		$this->console_command->shouldReceive( 'comment' )
			->with( 'Publishing Laravel Migrations...' )
			->once();
		$this->console_command->shouldReceive( 'call' )
			->with(
				'vendor:publish',
				[
					'--tag' => 'laravel-migrations',
					'--force' => true,
				]
			)
			->once();

		$this->console_command->shouldReceive( 'comment' )
			->with( 'Publishing Laravel Assets...' )
			->once();
		$this->console_command->shouldReceive( 'call' )
			->with(
				'vendor:publish',
				[
					'--tag' => 'laravel-assets',
					'--force' => true,
				]
			)
			->once();

		$this->console_command->shouldReceive( 'comment' )
			->with( 'Publishing Yivic Base Migrations...' )
			->once();
		$this->console_command->shouldReceive( 'call' )
			->with(
				'vendor:publish',
				[
					'--tag' => 'yivic-base-migrations',
					'--force' => true,
				]
			)
			->once();

		$this->console_command->shouldReceive( 'comment' )
			->with( 'Publishing Yivic Base Assets...' )
			->once();
		$this->console_command->shouldReceive( 'call' )
			->with(
				'vendor:publish',
				[
					'--tag' => 'yivic-base-assets',
					'--force' => true,
				]
			)
			->once();

		$this->console_command->shouldReceive( 'comment' )
			->with( 'Doing Migrations...' )
			->once();
		$this->console_command->shouldReceive( 'call' )
			->with(
				'migrate',
				[
					'--no-interaction' => true,
					'--force' => true,
					'--step' => true,
				]
			)
			->once();

		// Create the action class
		$action = Mockery::mock( Setup_WP_App_In_Console_Action::class )->makePartial();

		// Call perform_setup_actions with the mocked console command
		$action->perform_setup_actions( $this->console_command );

		$this->assertTrue( true );
	}
}
