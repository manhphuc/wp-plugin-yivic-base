<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits;

use Yivic_Base\Foundation\Shared\Traits\Config_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Config_Trait_Test\Config_Trait_Test_Tmp;

class Config_Trait_Test extends Unit_Test_Case {

	private $config_trait_class;

	protected function setUp(): void {
		parent::setUp();

		$this->config_trait_class = new Config_Trait_Test_Tmp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_bind_config(): void {
		$config_trait_class = $this->config_trait_class;

		// Create a test configuration arrays
		$config = [
			'property1' => 'value1',
			'property2' => 'value2',
			'property3' => 'value3',
		];

		// Call the bind_config method
		$config_trait_class->bind_config( $config );

		// Assert that the properties are correctly assigned
		$this->assertEquals( 'value1', $config_trait_class->property1 );
		$this->assertEquals( 'value2', $config_trait_class->property2 );
		$this->assertEquals( 'value3', $config_trait_class->property3 );
	}

	public function test_bind_config_unknown_property(): void {
		$config_trait_class = $this->config_trait_class;

		// Create a test configuration array with an unknown property
		$config = [
			'property1' => 'value1',
			'unknown_property' => 'value2',
			'property3' => 'value3',
		];

		// Call the bind_config method with strict mode enabled
		$this->expectException( \InvalidArgumentException::class );
		$config_trait_class->bind_config( $config, true );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Shared\Traits\Config_Trait_Test;

use Yivic_Base\Foundation\Shared\Traits\Config_Trait;

class Config_Trait_Test_Tmp {
	use Config_Trait;

	public $property1;
	public $property2;
	public $property3;
}