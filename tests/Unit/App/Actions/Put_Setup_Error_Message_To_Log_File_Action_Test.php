<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Mockery;

class Put_Setup_Error_Message_To_Log_File_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		Mockery::close();
	}
}
