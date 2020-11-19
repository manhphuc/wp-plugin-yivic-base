<?php


namespace Yivic\Wp\YivicBase\Libs\Traits;


trait ServiceTrait {
	/**
	 * @var string
	 */
	public $text_domain = 'default';

	/**
	 * @param null $config
	 */
	protected function initWithConfig( $config = null ) {
		if ( ! empty( $config ) ) {
			foreach ( (array) $config as $key => $value ) {
				if ( property_exists( get_class( $this ), $key ) ) {
					$this->$key = $value;
				}
			}
		}
	}
}
