<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits;

use Yivic_Base\Foundation\Shared\Traits\Static_Instance_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Static_Instance_Trait_Test\Static_Instance_Trait_Test_Tmp;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Static_Instance_Trait_Test\Static_Instance_Trait_Test_Tmp_Not_Reinitialize;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Static_Instance_Trait_Test\Static_Instance_Trait_Test_Tmp_With_Args;

class Static_Instance_Trait_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_instance_returns_same_object(): void {
		$static_instance_trait_class = new Static_Instance_Trait_Test_Tmp();

		$first_instance = $static_instance_trait_class::instance();
		$second_instance = $static_instance_trait_class::instance();

		// Assert that the two instances are the same object
		$this->assertSame( $first_instance, $second_instance );
	}

	public function test_instance_initialization_with_arguments(): void {
		$static_instance_trait_class = new Static_Instance_Trait_Test_Tmp_With_Args();

		// Initialize with arguments and check if it's correctly set
		$initialized_instance = $static_instance_trait_class::instance( 'test_value' );

		// Check that the value is correctly initialized
		$this->assertEquals( 'test_value', $initialized_instance->value );
	}

	public function test_instance_does_not_reinitialize_on_subsequent_calls(): void {
		$static_instance_trait_class = new Static_Instance_Trait_Test_Tmp_Not_Reinitialize();

		// Initialize with first argument
		$first_instance = $static_instance_trait_class::instance( 'initial_value' );

		// Attempt to initialize again with different value
		$second_instance = $static_instance_trait_class::instance( 'new_value' );

		// Check that the value is still the initial one, showing no reinitialization
		$this->assertEquals( 'initial_value', $second_instance->value );

		// Assert that both calls return the same instance
		$this->assertSame( $first_instance, $second_instance );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Static_Instance_Trait_Test;

use Yivic_Base\Foundation\Shared\Traits\Static_Instance_Trait;

class Static_Instance_Trait_Test_Tmp {
	use Static_Instance_Trait;
}

class Static_Instance_Trait_Test_Tmp_With_Args {
	use Static_Instance_Trait;

	public $value;

	// Constructor to initialize instance with value
	public function __construct( $value = null ) {
		$this->value = $value;
	}
}

class Static_Instance_Trait_Test_Tmp_Not_Reinitialize {
	use Static_Instance_Trait;

	public $value;

	// Constructor to initialize instance with value
	public function __construct( $value = null ) {
		$this->value = $value;
	}
}