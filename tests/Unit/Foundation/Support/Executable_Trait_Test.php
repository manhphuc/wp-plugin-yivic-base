<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Support;

use Illuminate\Container\Container;

use Yivic_Base\Foundation\Support\Executable_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Support\Executable_Trait_Test\Executable_Trait_Test_Tmp;
use Mockery;
use WP_Mock;

class Executable_Trait_Test extends Unit_Test_Case {
	private $app;
	private $mock_container;

	protected function setUp(): void {
		parent::setUp();

		// Mock the application container and configure it to handle the 'call' method
		$this->mock_container = Mockery::mock( Container::class );
		$this->mock_container->shouldReceive( 'call' )
								->andReturn( 'handled' );

		// Mock the app() function to return the mocked container
		WP_Mock::userFunction( 'app' )
				->once()
				->andReturn( $this->mock_container );
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}

	public function test_execute_now_creates_instance_and_calls_handle(): void {
		// Mock the command class that uses the Executable_Trait
		$mock_command = Mockery::mock( Executable_Trait_Test_Tmp::class )
								->shouldReceive( 'handle' )
								->andReturn( 'handled' )
								->getMock();

		// Configure the mock container to call the 'handle' method on the mock command
		$this->mock_container->shouldReceive( 'call' )
							->withArgs(
								function ( $args ) use ( $mock_command ) {
									return $args[0] === [ $mock_command, 'handle' ];
								}
							)
							->andReturn( 'handled' );

		// Execute the method and assert the expected result
		$result = Executable_Trait_Test_Tmp::execute_now( 'arg1', 'arg2' );

		$this->assertEquals( 'handled', $result );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Support\Executable_Trait_Test;

use Yivic_Base\Foundation\Support\Executable_Trait;

class Executable_Trait_Test_Tmp {
	use Executable_Trait;

	public function __construct( $arg1 = null, $arg2 = null ) {
		// Initialize with arguments if necessary
	}

	public function handle() {
		return 'handled'; // Simulate handling
	}
}
