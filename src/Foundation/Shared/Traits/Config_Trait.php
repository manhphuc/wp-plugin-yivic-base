<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Shared\Traits;

use InvalidArgumentException;

/**
 * This trait allow the object to mass assign array values with properties that map array keys
 */
trait Config_Trait {
	/**
	 * Mass assign array values with properties that map array keys
	 *
	 * @param  array  $config
	 * @param  bool  $strict  default = false, that means all existing keys should be assigned
	 *                      if set to true, that means exception thrown if a key doesn't
	 *                      match the object property
	 *
	 * @return self
	 * @throws InvalidArgumentException | \Exception
	 */
	public function bind_config( array $config = [], bool $strict = false ): self {
		if ( is_array( $config ) && ! empty( $config ) ) {
			foreach ( $config as $attr_name => $attr_value ) {
				if ( property_exists( $this, $attr_name ) ) {
					$this->$attr_name = $attr_value;
				} elseif ( $strict ) {
					throw new InvalidArgumentException(
						sprintf(
							'Property "%s" does not exist in class "%s"',
							esc_html( $attr_name ),
							__CLASS__
						)
					);
				}
			}
		}

		return $this;
	}
}