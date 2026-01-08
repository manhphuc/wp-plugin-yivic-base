<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Support;

trait Executable_Trait {
	public static function exec( ...$arguments ) {
		$command    = new static( ...$arguments );
		$method     = method_exists( $command, 'execute' ) ? 'execute' : 'handle';

		return app()->call( [ $command, $method ] );
	}

	public static function execute_now( ...$arguments ) {
		$command    = new static( ...$arguments );
		$method     = method_exists( $command, 'handle' ) ? 'handle' : 'execute';

		return app()->call( [ $command, $method ] );
	}
}
