<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Shared\Traits;

/**
 * This trait allows to set the protected/private propeties
 **/
trait Setter_Trait {
	public function __set( $name, $value ) {
		$method_name = 'set_' . $name;
		if ( method_exists( $this, $method_name ) ) {
			$this->$name = $this->$method_name( $value );
		} else {
			$this->$name = $value;
		}
	}
}
