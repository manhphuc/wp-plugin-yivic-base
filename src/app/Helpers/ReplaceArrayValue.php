<?php


namespace Yivic\Wp\YivicBase\App\Helpers;

use \Exception;

class ReplaceArrayValue {
	/**
	 * @var mixed value used as replacement.
	 */
	public $value;


	/**
	 * Constructor.
	 *
	 * @param mixed $value value used as replacement.
	 */
	public function __construct( $value ) {
		$this->value = $value;
	}

	/**
	 * Restores class state after using `var_export()`.
	 *
	 * @param array $state
	 *
	 * @return ReplaceArrayValue
	 * @throws Exception when $state property does not contain `value` parameter
	 * @see var_export()
	 * @since 2.0.16
	 */
	public static function __set_state( $state ) {
		if ( ! isset( $state['value'] ) ) {
			throw new Exception( 'Failed to instantiate class "ReplaceArrayValue". Required parameter "value" is missing' );
		}

		return new self( $state['value'] );
	}
}
