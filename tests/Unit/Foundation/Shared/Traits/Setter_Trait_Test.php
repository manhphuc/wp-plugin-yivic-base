<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits;

use Yivic_Base\Foundation\Shared\Traits\Setter_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Setter_Trait_Test\Setter_Trait_Test_Tmp;

class Setter_Trait_Test extends Unit_Test_Case {
	private $setter_trait_class;

	protected function setUp(): void {
		parent::setUp();

		$this->setter_trait_class = new Setter_Trait_Test_Tmp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_set_protected_property(): void {
		$setter_trait_class = $this->setter_trait_class;

		$setter_trait_class->property1 = 'value1';
		$this->assertEquals( 'value1', $setter_trait_class->get_property1() );
	}

	public function test_set_private_property_with_method(): void {
		$setter_trait_class = $this->setter_trait_class;

		$setter_trait_class->property2 = 'value2';
		$this->assertEquals( 'VALUE2', $setter_trait_class->get_property2() );
	}

	public function test_set_non_existent_property(): void {
		$setter_trait_class = $this->setter_trait_class;

		$setter_trait_class->undefined_property = 'undefined_property';
		$this->assertTrue( property_exists( $setter_trait_class, 'undefined_property' ) );
		$this->assertFalse( property_exists( $setter_trait_class, 'undefined_property1' ) );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Setter_Trait_Test;

use Yivic_Base\Foundation\Shared\Traits\Setter_Trait;

class Setter_Trait_Test_Tmp {
	use Setter_Trait;

	protected $property1;
	private $property2;

	// Setter method for property2 to test method existence handling
	protected function set_property2( $value ) {
		return strtoupper( $value );
	}

	public function get_property1() {
		return $this->property1;
	}

	public function get_property2() {
		return $this->property2;
	}
}