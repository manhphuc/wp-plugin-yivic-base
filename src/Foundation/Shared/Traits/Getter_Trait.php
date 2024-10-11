<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Shared\Traits;

/**
 * This trait allows to get the protected/private propeties
 **/
trait Getter_Trait {
	public function __get( $name ) {
		$method_name = 'get_' . $name;
		if ( method_exists( $this, $method_name ) ) {
			return $this->$method_name();
		}

		return $this->$name;
	}
}
