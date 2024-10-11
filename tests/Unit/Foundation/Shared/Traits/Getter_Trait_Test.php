<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits;

use Yivic_Base\Foundation\Shared\Traits\Getter_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Getter_Trait_Test\Getter_Trait_Test_Tmp;

class Getter_Trait_Test extends Unit_Test_Case {

	private $getter_trait_class;

	protected function setUp(): void {
		parent::setUp();

		$this->getter_trait_class = new Getter_Trait_Test_Tmp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_get_protected_property_access() {
		$getter_trait_class = $this->getter_trait_class;

		// Test accessing protected property via __get
		$this->assertEquals( 'Method Protected Name', $getter_trait_class->protected_name );
	}

	public function test_get_private_property_access() {
		$getter_trait_class = $this->getter_trait_class;

		// Test accessing private property via __get
		$this->assertEquals( 'Method Secret', $getter_trait_class->secret );
	}

	public function test_get_non_existent_method() {
		$getter_trait_class = $this->getter_trait_class;

		$this->assertEquals( 'Test Name', $getter_trait_class->name );
	}

	public function test_get_non_existent_property() {
		// Temporarily suppress PHP warnings
		$previous_error_reporting = error_reporting();
		error_reporting( $previous_error_reporting & ~E_WARNING );

		// Accessing an undefined property
		$result = $this->getter_trait_class->undefined_property;

		// Restore previous error reporting level
		error_reporting( $previous_error_reporting );

		// Optionally assert that $result is null or any other expected behavior
		$this->assertNull( $result );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Getter_Trait_Test;

use Yivic_Base\Foundation\Shared\Traits\Getter_Trait;

class Getter_Trait_Test_Tmp {
	use Getter_Trait;

	public $name = 'Test Name';

	protected $protected_name = 'Protected Name';

	protected function get_protected_name() {
		return 'Method Protected Name';
	}

	// Adding a private property to test private property access
	private $secret = 'Secret Value';

	private function get_secret() {
		return 'Method Secret';
	}
}
