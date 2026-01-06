<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\WP_CLI\Process_Artisan_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use InvalidArgumentException;
use Mockery;
use WP_Mock;

class Process_Artisan_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle_throws_exception_if_artisan_not_in_args() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Not an artisan command' );

		$kernelMock = Mockery::mock( \Illuminate\Contracts\Console\Kernel::class );
		app()->instance( \Illuminate\Contracts\Console\Kernel::class, $kernelMock );

		WP_Mock::userFunction( 'wp_unslash' )
		->withAnyArgs()
		->andReturnUsing(
			function ( $text ) {
				return $text;
			}
		);

		WP_Mock::userFunction( 'sanitize_text_field' )
		->withAnyArgs()
		->andReturnUsing(
			function ( $text ) {
				return $text;
			}
		);

		// Simulate $_SERVER['argv'] without 'artisan'
		$_SERVER['argv'] = [ 'some_command', 'another_command' ];

		$action = new Process_Artisan_Action_Test_Tmp();
		$action->handle();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_handle() {
		WP_Mock::userFunction( 'wp_unslash' )
		->withAnyArgs()
		->andReturnUsing(
			function ( $text ) {
				return $text;
			}
		);

		WP_Mock::userFunction( 'sanitize_text_field' )
		->withAnyArgs()
		->andReturnUsing(
			function ( $text ) {
				return $text;
			}
		);

		// Simulate $_SERVER['argv'] with 'artisan'
		$_SERVER['argv'] = [ 'php', 'artisan', 'make:command', 'TestCommand' ];

		// Mock the Kernel
		$kernelMock = Mockery::mock( \Illuminate\Contracts\Console\Kernel::class );
		$kernelMock->shouldReceive( 'handle' )
			->once()
			->andReturn( 0 );

		app()->instance( \Illuminate\Contracts\Console\Kernel::class, $kernelMock );

		$action = new Process_Artisan_Action_Test_Tmp();
		$status = $action->handle();

		$this->assertNull( $status );
	}
}

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\WP_CLI\Process_Artisan_Action;

class Process_Artisan_Action_Test_Tmp extends Process_Artisan_Action {

	protected function exit_with_status( int $status ): void {}
}
