<?php

namespace Yivic_Base\Tests\Support\Helpers;

use ReflectionClass;

trait Test_Utils {

	/**
	 * @description: For testing protected, private method in class
	 * @throws \ReflectionException
	 */
	protected function invoke_method( &$class_object, $method_name, array $method_parameters = [] ) {
		$reflection = new ReflectionClass( get_class( $class_object ) );
		$method     = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( $class_object, $method_parameters );
	}

	/**
	 * Get protected/private property value of a class.
	 *
	 * @param  object  $class_object  Instantiated object that we will get value from.
	 * @param  string  $property_name  Property name to call
	 *
	 * @throws \ReflectionException
	 */
	protected function get_class_property_value( object $class_object, string $property_name ) {
		$reflection = new \ReflectionClass( get_class( $class_object ) );
		$property   = $reflection->getProperty( $property_name );
		$property->setAccessible( true );

		return $property->getValue( $class_object );
	}

	/**
	 * Set value to a property of an object class.
	 *
	 * @param  object  $class_object  Instantiated object that we will get value from.
	 * @param  string  $property  Property name to set value to.
	 * @param  mixed  $value  Value to set to property
	 *
	 * @throws \ReflectionException
	 */
	protected function set_class_property_value( object $class_object, string $property, mixed $value ): void {
		$reflection = new \ReflectionClass( get_class( $class_object ) );
		$property   = $reflection->getProperty( $property );
		$property->setAccessible( true );
		$property->setValue( $class_object, $value );
	}
}
