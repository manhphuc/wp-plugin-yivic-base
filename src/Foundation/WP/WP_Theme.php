<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\WP;

use Yivic_Base\App\Support\App_Const;
use Yivic_Base\Foundation\Shared\Traits\Config_Trait;
use Exception;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * This is the base class for plugin to be inherited from
 * We consider each plugin a Laravel Service provider
 * @package Yivic_Base\Libs
 * @property \Yivic_Base\App\WP\WP_Application $app
 */
abstract class WP_Theme extends ServiceProvider implements WP_Theme_Interface {
	use Config_Trait;

	/**
	 * @property string The slug of the theme, it should be the folder name, we use it for the instance name
	 */
	protected $theme_slug;
	protected $base_path;
	protected $base_url;
	protected $parent_base_path;
	protected $parent_base_url;

	/**
	 * Get the wp_app instance of the plugin
	 *
	 * @return static
	 */
	public static function wp_app_instance(): self {
		// We return the wp_app instance of the successor's class
		return app( static::class );
	}

	public static function init_with_wp_app( string $slug ): WP_Theme_Interface {
		if ( app()->has( static::class ) ) {
			return app( static::class );
		}

		$theme = new static( app() );
		$theme->init_with_needed_params( $slug );

		// Attach the instance to WP Application
		$theme->attach_to_wp_app();

		// We want to handle the hooks first
		$theme->manipulate_hooks();

		return $theme;
	}

	/**
	 * We want to bind the base params using an array
	 *
	 * @param array $base_params_arr
	 *
	 * @return void
	 * @throws InvalidArgumentException|\Exception
	 */
	public function bind_base_params( array $base_params_arr ): void {
		$this->bind_config( $base_params_arr, true );
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function register() {
		// We need to ensure all needed properties are set
		$this->validate_needed_properties();

		// We want to handle the hooks first
		$this->manipulate_hooks();
	}

	public function get_theme_slug(): string {
		return $this->theme_slug;
	}

	public function get_base_path(): string {
		return $this->base_path;
	}

	public function get_base_url(): string {
		return $this->base_url;
	}

	public function get_parent_base_path(): string {
		return $this->parent_base_path;
	}

	public function get_parent_base_url(): string {
		return $this->parent_base_url;
	}

	public function register_this_to_wp_app( $wp_app ) {
		/** @var \Yivic_Base\App\WP\WP_Application $wp_app */
		$wp_app->register( $this );
	}

	/**
	 * Check if needed properties have correct values
	 * @return void
	 * @throws InvalidArgumentException
	 */
	protected function validate_needed_properties(): void {
		if ( empty( $this->theme_slug ) || ! preg_match( '/^[a-zA-Z0-9_-]+$/i', $this->theme_slug ) ) {
			throw new InvalidArgumentException(
				sprintf(
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped, WordPress.WP.I18n.MissingTranslatorsComment, WordPress.Security.EscapeOutput.ExceptionNotEscaped
					__( 'Property %1$s must be set for %2$s.', 'yivic-base' ) . ' ' . __( 'Value must contain only alphanumeric characters _ -', 'yivic-base' ),
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					'theme_slug',
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					get_class( $this )
				)
			);
		}
	}

	/**
	 * Init needed properties
	 * @param string $theme_slug
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	protected function init_with_needed_params( string $theme_slug ): void {
		$theme_path = get_stylesheet_directory();
		$parent_theme_path = get_template_directory();
		if ( $theme_path !== $parent_theme_path ) {
			$theme_base_path = $theme_path;
			$theme_base_url = get_stylesheet_directory_uri();
			$parent_theme_base_path = $parent_theme_path;
			$parent_theme_base_url = get_template_directory_uri();
		} else {
			$theme_base_path = $theme_path;
			$theme_base_url = get_stylesheet_directory_uri();
			$parent_theme_base_path = '';
			$parent_theme_base_url = '';
		}

		/** @var \Yivic_Base\Foundation\WP\WP_Theme $theme  */
		$this->bind_base_params(
			[
				WP_Theme_Interface::PARAM_KEY_THEME_SLUG => $theme_slug,
				WP_Theme_Interface::PARAM_KEY_THEME_BASE_PATH => $theme_base_path,
				WP_Theme_Interface::PARAM_KEY_THEME_BASE_URL => $theme_base_url,
				WP_Theme_Interface::PARAM_KEY_PARENT_THEME_BASE_PATH => $parent_theme_base_path,
				WP_Theme_Interface::PARAM_KEY_PARENT_THEME_BASE_URL => $parent_theme_base_url,
			]
		);

		// We need to ensure all needed properties are set
		$this->validate_needed_properties();
	}

	/**
	 * We do needed things to attach and register the theme to WP Application
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	protected function attach_to_wp_app(): void {
		app()->instance( static::class, $this );
		app()->alias( static::class, 'theme-' . $this->get_theme_slug() );

		// We want to register the WP_Thee after all needed Service Providers
		add_action(
			App_Const::ACTION_WP_APP_REGISTERED,
			[ $this, 'register_this_to_wp_app' ]
		);
	}
}