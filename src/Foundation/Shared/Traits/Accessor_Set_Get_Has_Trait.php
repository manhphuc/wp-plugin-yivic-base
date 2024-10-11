<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Shared\Traits;

use BadMethodCallException;
use InvalidArgumentException;

/**
 * This trait allow the object to have the default methods (act as fallbacks):
 * - set_${property}($value): set the $value to $object->$property: $object->$property = $value
 * - get_${property}(): get the value $object->$property
 * - has_${property}(): return true if $property is set
 * E.g. the object has the method `set_some_property($value)` but has no method `get_some_property()`
 * - When we use `$obj->set_some_property($value)`, it would use the defined method
 * (not using the fallback method defined in this trait)
 * - When we use `$obj->get_some_property($value)`, it will automatically use the fallback method
 * which is defined in this trait to simple get the value of the $some_property of the $obj
 * then return it
 */
trait Accessor_Set_Get_Has_Trait {
	/**
	 * Magic method to work as a bootstrap for all method calls
	 *
	 * @param  mixed  $method
	 * @param  mixed  $args
	 *
	 * @return mixed
	 * @throws BadMethodCallException | \Exception
	 */
	public function __call( $method, $args ) {
		$parts = preg_split( '/(_[^A-Z]*)/', $method, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

		if ( isset( $parts[1] ) ) {
			$tmp_parts = explode( '_', $parts[1] );
			$type      = array_shift( $parts );
			if ( in_array( $type, [ 'get', 'set', 'has' ] ) ) {
				array_shift( $tmp_parts );
				$property = strtolower( implode( '_', $tmp_parts ) );
				$params   = ( isset( $args[0] ) ) ? [ $property, $args[0] ] : [ $property ];

				if ( ! method_exists( $this, $method ) ) {
					return call_user_func_array( [ $this, $type . '_property' ], $params );
				}
			}
		}

		if ( method_exists( $this, $method ) ) {
			return call_user_func_array( [ $this, $method ], $args );
		}

		throw new BadMethodCallException(
			sprintf(
				"'%s' does not exist in '%s'.",
				esc_html( $method ),
				__CLASS__
			)
		);
	}

	/**
	 * Fallback method for setters, set value to the property
	 *
	 * @param  mixed  $property
	 * @param  mixed  $value
	 *
	 * @return void
	 * @throws InvalidArgumentException | \Exception
	 */
	public function set_property( $property, $value = null ): void {
		if ( ! property_exists( $this, $property ) ) {
			throw new InvalidArgumentException(
				sprintf(
					"Property '%s' does not exist in '%s'.",
					esc_html( $property ),
					__CLASS__
				)
			);
		}

		$this->$property = $value;
	}

	/**
	 * Fallback method for getters, retrieve the value of property and return
	 * @param mixed $property
	 * @return mixed
	 * @throws InvalidArgumentException | \Exception
	 */
	public function get_property( $property ) {
		if ( ! property_exists( $this, $property ) ) {
			throw new InvalidArgumentException(
				sprintf(
					"Property '%s' does not exist in '%s'.",
					esc_html( $property ),
					__CLASS__
				)
			);
		}

		return $this->$property;
	}

	/**
	 * Fallback method for hassers, return true is a property is set
	 *
	 * @param  mixed  $property
	 *
	 * @return bool
	 * @throws InvalidArgumentException | \Exception
	 */
	public function has_property( $property ): bool {
		if ( ! property_exists( $this, $property ) ) {
			throw new InvalidArgumentException(
				sprintf(
					"Property '%s' does not exist in '%s'.",
					esc_html( $property ),
					__CLASS__
				)
			);
		}

		return isset( $this->$property );
	}
}
