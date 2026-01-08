<?php

declare(strict_types=1);

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\App\WP\WP_Application;

if ( ! function_exists( 'yivic_base_wp_app_web_page_title' ) ) {
	function yivic_base_wp_app_web_page_title() {
		return Yivic_Base_Helper::wp_app_web_page_title();
	}
}

if ( ! function_exists( 'wp_app' ) ) {
	/**
	 * Get the available container instance.
	 *
	 * @param  ?string  $abstract
	 * @param  array    $parameters
	 * @return mixed|WP_Application
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.abstractFound
	function wp_app( ?string $abstract = null, array $parameters = [] ): mixed {
		$app = WP_Application::getInstance();

		if ( $abstract === null ) {
			return $app;
		}

		return $app->make( $abstract, $parameters );
	}
}
