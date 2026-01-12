<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Shared\Traits;

/**
 * This trait allow the class to have a singleton static object
 */
trait Static_Instance_Trait {
	protected static $instance = null;

	/**
	 * Initialize the singleton instance then return it or the existing one
	 * @param mixed $args
	 * @return static
	 */
	public static function instance( ...$args ) {
		if ( empty( static::$instance ) ) {
			static::$instance = new static( ...$args );
		}

		return static::$instance;
	}
}