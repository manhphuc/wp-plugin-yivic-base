<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\WP;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Support\ServiceProvider;
use Yivic_Base\Foundation\Shared\Traits\Config_Trait;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;

/**
 * This is the base class for plugin to be inherited from
 * We consider each plugin a Laravel Service provider
 * @package \Yivic_Base\App\WP
 * @property \Yivic_Base\App\WP\WP_Application $app
 */
abstract class WP_Plugin extends ServiceProvider implements WP_Plugin_Interface {
	use Config_Trait;

	protected $plugin_slug;
	// phpcs:ignore PHPCompatibility.Classes.NewTypedProperties.Found
	protected $base_path;
	// phpcs:ignore PHPCompatibility.Classes.NewTypedProperties.Found
	protected $base_url;

	/**
	 * Get the wp_app instance of the plugin
	 *
	 * @return static
	 */
	public static function wp_app_instance(): self {
		// We return the wp_app instance of the successor's class
		return app( static::class );
	}

	/**
	 * Initialize a WP Plugin and attach it to WP Application container
	 * @param string $slug
	 * @param string $base_path
	 * @param string $base_url
	 * @return WP_Plugin_Interface
	 * @throws BindingResolutionException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public static function init_with_wp_app( string $slug, string $base_path, string $base_url ): WP_Plugin_Interface {
		if ( app()->has( static::class ) ) {
			return app( static::class );
		}

		$plugin = new static( app() );
		$plugin->init_with_needed_params( $slug, $base_path, $base_url );

		// Attach the plugin to WP Application instance
		$plugin->attach_to_wp_app();

		// We want to handle the hooks first
		$plugin->manipulate_hooks();

		return $plugin;
	}

	/**
	 * We want to bind the the base params using an array
	 *
	 * @param array $base_params_arr
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function bind_base_params( array $base_params_arr ): void {
		$this->bind_config( $base_params_arr, true );
	}

	public function boot() {
		$this->prepare_views_paths( $this->get_plugin_slug() );
	}

	public function get_plugin_slug(): string {
		return $this->plugin_slug;
	}

	public function get_base_path(): string {
		return $this->base_path;
	}

	public function get_base_url(): string {
		return $this->base_url;
	}

	public function get_views_path(): string {
		return $this->get_base_path() . DIR_SEP . 'resources' . DIR_SEP . 'views';
	}

	public function view( $view, $data = [], $merge_data = [] ) {
		return view( $this->get_plugin_slug() . '::' . $view, $data, $merge_data );
	}

	public function get_plugin_basename(): string {
		return plugin_basename( $this->get_base_path() . DIR_SEP . $this->get_plugin_slug() . '.php' );
	}

	public function register_this_to_wp_app( $wp_app ) {
		/** @var \Yivic_Base\App\WP\WP_Application $wp_app */
		$wp_app->register( $this );
	}

	/**
	 * We want to init all needed properties with this method
	 *
	 * @param string $slug
	 * @param string $base_path
	 * @param string $base_url
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	protected function init_with_needed_params( string $slug, string $base_path, string $base_url ): void {
		$this->bind_base_params(
			[
				static::PARAM_KEY_PLUGIN_SLUG => $slug,
				static::PARAM_KEY_PLUGIN_BASE_PATH => $base_path,
				static::PARAM_KEY_PLUGIN_BASE_URL => $base_url,
			]
		);

		// We need to ensure all needed properties are set
		$this->validate_needed_properties();
	}

	/**
	 * We do needed things to attach and register the plugin to WP Application
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	protected function attach_to_wp_app(): void {
		app()->instance( static::class, $this );
		app()->alias( static::class, 'plugin-' . $this->get_plugin_slug() );

		// We want to register the WP_Plugin after all needed Service Providers
		add_action(
			App_Const::ACTION_WP_APP_REGISTERED,
			[ $this, 'register_this_to_wp_app' ]
		);
	}

	/**
	 * @throws \Exception
	 */
	protected function validate_needed_properties(): void {
		foreach ( [ 'plugin_slug', 'base_path', 'base_url' ] as $property ) {
			if ( empty( $this->$property ) ) {
				throw new InvalidArgumentException(
					sprintf(
						'Property %s must be set for %s',
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
						$property,
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
						get_class( $this )
					)
				);
			}
		}

		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/i', $this->plugin_slug ) ) {
			throw new InvalidArgumentException(
				sprintf(
				// translators: %1$s, %2$s are replaced by a string
					esc_textarea( __( 'Property %1$s must be set for %2$s.', 'yivic-base' ) . ' ' . __( 'Value must contain only alphanumeric characters _ -', 'yivic-base' ) ),
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					'plugin_slug',
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					get_class( $this )
				)
			);
		}
	}

	/**
	 * We want to get the views for each plugin by this order: child theme, parent theme, and the plugin it self
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.namespaceFound
	protected function prepare_views_paths( $namespace ): void {
		// We only want to search for the namespace folder in the theme `resources/views/_plugins/$namespace if it exists
		$theme_path = get_stylesheet_directory();
		$theme_namespace_folder = realpath(
			$theme_path . DIR_SEP . 'resources' . DIR_SEP . 'views'
			. DIR_SEP . '_plugins' . DIR_SEP . $namespace
		);
		! $theme_namespace_folder || $this->loadViewsFrom(
			$theme_namespace_folder,
			$namespace
		);

		$parent_theme_path = get_template_directory();
		if ( $parent_theme_path !== $theme_path ) {
			// We only want to search for the namespace folder in parent theme `resources/views/_plugins/$namespace if it exists
			$parent_theme_namespace_folder = realpath(
				$parent_theme_path . DIR_SEP . 'resources' . DIR_SEP . 'views'
				. DIR_SEP . '_plugins' . DIR_SEP . $namespace
			);
			! $parent_theme_namespace_folder || $this->loadViewsFrom(
				$parent_theme_namespace_folder,
				$namespace
			);
		}

		// The folder for the plugin view namespace is `<plugin-path>/resources/views
		$plugin_namespace_folder = $this->get_base_path() . DIR_SEP . 'resources' . DIR_SEP . 'views';
		$this->loadViewsFrom(
			$plugin_namespace_folder,
			$namespace
		);
	}
}
