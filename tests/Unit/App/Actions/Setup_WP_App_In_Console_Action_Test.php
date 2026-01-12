<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Mark_Setup_WP_App_Done_Action;
use Yivic_Base\App\Actions\Mark_Setup_WP_App_Failed_Action;
use Yivic_Base\App\Actions\Setup_WP_App_In_Console_Action;
use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\App\WP\WP_Application;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Exception;
use InvalidArgumentException;
use Mockery;
use WP_Mock;

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
	public function test_handle_executes_successfully_and_marks_done() {
		// Mock Command instance
		$command_mock = Mockery::mock( Command::class );
		$command_mock->shouldReceive( 'comment' )->atLeast()->once();
		$command_mock->shouldReceive( 'call' )->atLeast()->once();

		// Mock Yivic_Base_Helper static methods
		$helper_mock = Mockery::mock( 'alias:Yivic_Base\App\Support\Yivic_Base_Helper' );
		$helper_mock->shouldReceive( 'prepare_wp_app_folders' )->once();
		$helper_mock->shouldReceive( 'is_console_mode' )->andReturn( true );

		// Mock Mark_Setup_WP_App_Done_Action to ensure exec is called once
		$mark_done_mock = Mockery::mock( 'alias:' . Mark_Setup_WP_App_Done_Action::class );
		$mark_done_mock->shouldReceive( 'exec' )->once();

		// Mock Filesystem to intercept cleanDirectory call
		$filesystem_mock = Mockery::mock( Filesystem::class );
		$filesystem_mock->shouldReceive( 'is_dir' )->andReturn( true );

		/** @var WP_Application $app_mock */
		$app_mock = $this->createMock( WP_Application::class );

		// Mock the `make` method to always return the same instances
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ Filesystem::class, [], $filesystem_mock ],
			]
		);

		// Set the mocked WP_Application instance globally
		WP_Application::setInstance( $app_mock );

		// Use the custom path directly when creating the action
		$action = new Setup_WP_App_In_Console_Action( $command_mock, '/fake/path/to/migrations' );

		// Run the handle method
		$action->handle();

		$this->assertTrue( true );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle_handles_exception_and_marks_failed() {
		// Mock Command instance
		$command_mock = Mockery::mock( Command::class );
		$command_mock->shouldReceive( 'comment' )->atLeast()->once();
		$command_mock->shouldReceive( 'call' )->andThrow( new Exception( 'Setup failed' ) );

		// Mock Yivic_Base_Helper static methods
		$helper_mock = Mockery::mock( 'alias:' . Yivic_Base_Helper::class );
		$helper_mock->shouldReceive( 'prepare_wp_app_folders' )->once();
		$helper_mock->shouldReceive( 'is_console_mode' )->andReturn( true );

		// Mock Mark_Setup_WP_App_Done_Action and Mark_Setup_WP_App_Failed_Action
		$mark_done_mock = Mockery::mock( 'alias:' . Mark_Setup_WP_App_Done_Action::class );
		$mark_done_mock->shouldReceive( 'exec' )->never();

		$mark_failed_mock = Mockery::mock( 'alias:' . Mark_Setup_WP_App_Failed_Action::class );
		$mark_failed_mock->shouldReceive( 'exec' )->once()->with( 'Setup failed' );

		// Mock Filesystem to intercept cleanDirectory call
		$filesystem_mock = Mockery::mock( Filesystem::class );
		$filesystem_mock->shouldReceive( 'is_dir' )->andReturn( true );

		/** @var WP_Application $app_mock */
		$app_mock = $this->createMock( WP_Application::class );

		// Mock the `make` method to always return the same instances
		$app_mock->method( 'make' )->willReturnMap(
			[
				[ Filesystem::class, [], $filesystem_mock ],
			]
		);

		// Set the mocked WP_Application instance globally
		WP_Application::setInstance( $app_mock );

		// Run the action
		$action = new Setup_WP_App_In_Console_Action( $command_mock );
		$action->handle();

		$this->assertTrue( true );
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