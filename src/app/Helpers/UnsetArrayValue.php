<?php


namespace Yivic\Wp\YivicBase\App\Helpers;


class UnsetArrayValue {
	/**
	 * Restores class state after using `var_export()`.
	 *
	 * @param array $state
	 *
	 * @return UnsetArrayValue
	 * @see var_export()
	 * @since 2.0.16
	 */
	public static function __set_state( $state ) {
		return new self();
	}
}
