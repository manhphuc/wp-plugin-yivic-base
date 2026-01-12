<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit;

use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Sample_Test extends Unit_Test_Case {

	protected function setUp(): void {
	}

	protected function tearDown(): void {
		Mockery::close();
	}

	public function test_something() {
		// Assertions go here
		$this->assertTrue( true );
	}
}