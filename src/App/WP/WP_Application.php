<?php

declare(strict_types=1);

namespace Yivic_Base\App\WP;

use Yivic_Base\App\Actions\Init_WP_App_Kernels_Action;
use Yivic_Base\App\Support\App_Const;
use Yivic_Base\App\Support\Yivic_Base_Helper;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Mix;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * @package Yivic_Base\App\WP
 */
class WP_Application extends Application {
	/**
	 * Config array needed for the initialization process
	 * @var array
	 */
	protected static $config;

	protected $wp_app_slug = 'wp-app';
	protected $wp_api_slug = 'wp-api';

	/**
	 * Should contains WP headers, we will merge them with Laravel headers to send later
	 * @var mixed
	 */
	protected $wp_headers;

	public static function isset(): bool {
		return ! is_null( static::$instance );
	}

	public static function load_instance() {
		// We only want to run the setup once
		if ( static::isset() ) {
			return;
		}

		/**
		| Create a app() instance to be used in the whole application
		 */
		$wp_app_base_path = Yivic_Base_Helper::get_wp_app_base_path();
		$config = apply_filters(
			App_Const::FILTER_WP_APP_PREPARE_CONFIG,
			[
				'app'         => require_once dirname(
						dirname( dirname( __DIR__ ) )
					) . DIR_SEP . 'wp-app-config' . DIR_SEP . 'app.php',
				'wp_app_slug' => YIVIC_BASE_WP_APP_PREFIX,
				'wp_api_slug' => YIVIC_BASE_WP_API_PREFIX,
			]
		);
		if ( empty( $config['app']['key'] ) ) {
			$auth_key = md5( uniqid() );
			$config['app']['key'] = $auth_key;
			add_option( 'wp_app_auth_key', $auth_key );
		}

		// We initiate the WP Application instance
		static::init_instance_with_config(
			$wp_app_base_path,
			$config
		);

		Init_WP_App_Kernels_Action::exec();
		do_action( App_Const::ACTION_WP_APP_LOADED );
	}

	/**
	 * We don't want to have this class publicly initialized
	 *
	 * @param  string|null  $basePath
	 * @return void
	 */
	protected function __construct( $basePath = null ) {
		parent::__construct( $basePath );

		// We need to add the aliases to the custom classes for
		//  the controller to be able to inject the correct class
		$this->alias( 'request', \Yivic_Base\App\Http\Request::class );
	}

	/**
	 * @inheritedDoc
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function resourcePath( $path = '' ): string {
		// Todo: refactor this using constant
		return $this->basePath() . DIRECTORY_SEPARATOR . 'resources' . ( $path ? DIRECTORY_SEPARATOR . $path : $path );
	}

	/**
	 * @inheritedDoc
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function getNamespace(): string {
		if ( ! is_null( $this->namespace ) ) {
			return $this->namespace;
		}
		$this->namespace = 'Yivic_Base\\';
		return $this->namespace;
	}

	/**
	 * @inheritDoc
	 */
	public function runningInConsole() {
		if ( $this->isRunningInConsole === null ) {
			if (
				( strpos( request()->getPathInfo(), '/setup-app' ) !== false && request()->get( 'force_app_running_in_console' ) ) ||
				( strpos( request()->getPathInfo(), '/admin' ) !== false && request()->get( 'force_app_running_in_console' ) ) ||
				( strpos( request()->getPathInfo(), '/web-worker' ) !== false && request()->get( 'force_app_running_in_console' ) ) ||
				Yivic_Base_Helper::at_setup_app_url()
			) {
				if ( empty( $_SERVER['argv'] ) ) {
					$_SERVER['argv'] = null;
				}
				$this->isRunningInConsole = true;
			}
		}

		return parent::runningInConsole();
	}

	/**
	 * @inheritDoc
	 */
	public function registerConfiguredProviders() {
		$providers_list = apply_filters(
			App_Const::FILTER_WP_APP_MAIN_SERVICE_PROVIDERS,
			$this->config['app.providers']
		);
		$providers = Collection::make( $providers_list )
			->partition(
				function ( $provider ) {
					return ( strpos( $provider, 'Yivic_Base\\' ) === 0 ) ||
						( strpos( $provider, 'Illuminate\\' ) === 0 );
				}
			);

		$providers->splice( 1, 0, [ $this->make( PackageManifest::class )->providers() ] );

		( new ProviderRepository( $this, new Filesystem(), $this->getCachedServicesPath() ) )
			->load( $providers->collapse()->toArray() );

		// We trigger the action when wp_app (with providers) is registered
		do_action( App_Const::ACTION_WP_APP_REGISTERED, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		parent::boot();

		// We trigger the action when wp_app (with providers) are booted
		do_action( App_Const::ACTION_WP_APP_BOOTED );
	}

	/**
	 * We want to use the array to load the config
	 *
	 * @param  null  $basePath
	 * @param  mixed  $config
	 *
	 * @return WP_Application
	 */
	public static function init_instance_with_config( $basePath = null, $config = null ): self {
		$instance = static::$instance;
		if ( ! empty( $instance ) ) {
			return $instance;
		}

		static::$config = $config;
		$instance = new static( $basePath );
		$instance->wp_app_slug = $config['wp_app_slug'];
		$instance->wp_api_slug = $config['wp_api_slug'];

		static::$instance = $instance;

		return $instance;
	}

	/**
	 * A shortcut to register actions for yivic_base_wp_app_register_routes
	 * @param mixed $callback
	 * @param int $priority
	 * @param int $accepted_args
	 * @return void
	 */
	public function register_routes( $callback, $priority = 10, $accepted_args = 1 ): void {
		add_action( App_Const::ACTION_WP_APP_REGISTER_ROUTES, $callback, $priority, $accepted_args );
	}

	/**
	 * A shortcut to register actions for yivic_base_wp_api_register_routes
	 * @param mixed $callback
	 * @param int $priority
	 * @param int $accepted_args
	 * @return void
	 */
	public function register_api_routes( $callback, $priority = 10, $accepted_args = 1 ): void {
		add_action( App_Const::ACTION_WP_API_REGISTER_ROUTES, $callback, $priority, $accepted_args );
	}

	/**
	 * Get the slug for wp-app mode
	 * @return string
	 */
	public function get_wp_app_slug(): string {
		return $this->wp_app_slug;
	}

	/**
	 * Get the slug for wp-api mode
	 * @return string
	 */
	public function get_wp_api_slug(): string {
		return $this->wp_api_slug;
	}

	public function is_debug_mode(): bool {
		return config( 'app.debug' );
	}

	/**
	 * For checking if the request uri is for 'wp-app'
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function is_wp_app_mode(): bool {
		$wp_app_prefix = $this->wp_app_slug;
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$base_url_path = Yivic_Base_Helper::get_base_url_path();

		return ( strpos( $uri, $base_url_path . '/' . $wp_app_prefix . '/' ) === 0 || $uri === '/' . $wp_app_prefix || $uri === '/' . $wp_app_prefix . '/' );
	}

	/**
	 * For checking if the request uri is for 'wp-api'
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function is_wp_api_mode(): bool {
		$wp_api_prefix = $this->wp_api_slug;
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$base_url_path = Yivic_Base_Helper::get_base_url_path();

		return ( strpos( $uri, $base_url_path . '/' . $wp_api_prefix . '/' ) === 0 || $uri === '/' . $wp_api_prefix || $uri === '/' . $wp_api_prefix . '/' );
	}

	public function get_laravel_major_version(): int {
		return (int) Yivic_Base_Helper::get_major_version( Application::VERSION );
	}

	public function get_composer_path(): string {
		return defined( 'COMPOSER_VENDOR_DIR' ) ? COMPOSER_VENDOR_DIR : dirname( $this->resourcePath() ) . DIR_SEP . 'vendor';
	}

	public function set_wp_headers( $headers ) {
		$this->wp_headers = $headers;
	}

	public function get_wp_headers() {
		return $this->wp_headers;
	}

	public function set_request( Request $request ) {
		$this->instance( 'request', $request );
	}

	public function set_response( Response $response ) {
		$this->bind(
			ResponseFactory::class,
			// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			function ( $app ) use ( $response ) {
				return $response;
			}
		);
	}

	/**
	 * @inheritedDoc
	 *
	 * @return void
	 */
	protected function registerBaseBindings() {
		parent::registerBaseBindings();

		// We want to have the `config` service ready to use later on
		$config = static::$config;
		$this->singleton(
			'config',
			// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			function ( $app ) use ( $config ) {
				return new Repository( $config );
			}
		);

		$this->instance( self::class, $this );
		$this->singleton( Mix::class );
	}

	/**
	 * @inheritedDoc
	 *
	 * @return void
	 */
	protected function registerBaseServiceProviders() {
		$providers = [
			\Yivic_Base\App\Providers\Event_Service_Provider::class,
			\Yivic_Base\App\Providers\Log_Service_Provider::class,
			\Yivic_Base\App\Providers\Routing_Service_Provider::class,
			\Yivic_Base\App\Providers\Bus_Service_Provider::class,

			// We put the DB Service Provider here as it's the base one with WP
			\Yivic_Base\App\Providers\Database_Service_Provider::class,
		];

		foreach ( $providers as $provider_classname ) {
			$this->register( $provider_classname );
		}
	}
}
