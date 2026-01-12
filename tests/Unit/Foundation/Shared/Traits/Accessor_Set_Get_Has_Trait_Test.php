<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits;

use Yivic_Base\Foundation\Shared\Traits\Accessor_Set_Get_Has_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Accessor_Set_Get_Has_Trait_Test\Accessor_Set_Get_Has_Trait_Tmp;
use InvalidArgumentException;

class Accessor_Set_Get_Has_Trait_Test extends Unit_Test_Case {

	private $accessor_trait_class;

	protected function setUp(): void {
		parent::setUp();

		$this->accessor_trait_class = new Accessor_Set_Get_Has_Trait_Tmp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_set_property(): void {
		// Set a property using the set_property method
		$accessor_trait_class = $this->accessor_trait_class;
		$accessor_trait_class->set_property( 'property1', 'value' );

		// Assert that the property value is set correctly
		$this->assertEquals( 'value', $accessor_trait_class->get_property( 'property1' ) );
	}

	public function test_set_property_with_invalid_property(): void {
		$accessor_trait_class = $this->accessor_trait_class;

		// Expect an exception when setting an invalid property
		$this->expectException( InvalidArgumentException::class );

		try {
			// Attempt to set a property that does not exist
			$accessor_trait_class->set_property( 'invalid_property', 'some_value' );
		} catch ( InvalidArgumentException $e ) {
			// Assert that the exception message contains the expected core part
			$expected_message_part = "Property 'invalid_property' does not exist in";
			$this->assertStringContainsString( $expected_message_part, $e->getMessage() );

			// Rethrow the exception to fulfill the expectException requirement
			throw $e;
		}
	}

	public function test_get_property(): void {
		$accessor_trait_class = $this->accessor_trait_class;

		// Set new properties
		$accessor_trait_class->property1 = 'value1';
		$accessor_trait_class->property2 = 'value2';
		$result1 = $accessor_trait_class->get_property( 'property1' );
		$result2 = $accessor_trait_class->get_property( 'property2' );

		// Assert that the property value is get correctly
		$this->assertEquals( 'value1', $result1 );
		$this->assertEquals( 'value2', $result2 );
	}

	public function test_get_property_with_invalid_property(): void {
		$accessor_trait_class = $this->accessor_trait_class;

		// Expect an exception when setting an invalid property
		$this->expectException( InvalidArgumentException::class );

		try {
			// Attempt to get a property that does not exist
			$accessor_trait_class->get_property( 'invalid_property' );
		} catch ( InvalidArgumentException $e ) {
			// Assert that the exception message contains the expected core part
			$expected_message_part = "Property 'invalid_property' does not exist in";
			$this->assertStringContainsString( $expected_message_part, $e->getMessage() );

			// Rethrow the exception to fulfill the expectException requirement
			throw $e;
		}
	}

	public function test_has_property(): void {
		$accessor_trait_class = $this->accessor_trait_class;

		// Set new properties
		$accessor_trait_class->property1 = 'value1';
		$result1 = $accessor_trait_class->has_property( 'property1' );

		// Assert that the property exists
		$this->assertTrue( $result1 );
	}

	public function test_has_property_check_with_invalid_property(): void {
		$accessor_trait_class = $this->accessor_trait_class;

		// Expect an exception when setting an invalid property
		$this->expectException( InvalidArgumentException::class );

		try {
			// Attempt to get a property that does not exist
			$accessor_trait_class->has_property( 'invalid_property' );
		} catch ( InvalidArgumentException $e ) {
			// Assert that the exception message contains the expected core part
			$expected_message_part = "Property 'invalid_property' does not exist in";
			$this->assertStringContainsString( $expected_message_part, $e->getMessage() );

			// Rethrow the exception to fulfill the expectException requirement
			throw $e;
		}
	}

	public function test_magic_method_throws_exception_for_undefined_method() {
		$accessor_trait_class = $this->accessor_trait_class;

		// Expecting an exception when calling an undefined method
		$this->expectException( \BadMethodCallException::class );
		$this->expectExceptionMessage( "'undefined_method' does not exist in 'Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Accessor_Set_Get_Has_Trait_Test\Accessor_Set_Get_Has_Trait_Tmp'." );

		$accessor_trait_class->undefined_method();
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Accessor_Set_Get_Has_Trait_Test;

use Yivic_Base\Foundation\Shared\Traits\Accessor_Set_Get_Has_Trait;

class Accessor_Set_Get_Has_Trait_Tmp {
	use Accessor_Set_Get_Has_Trait;

	public $property1;
	public $property2;
}