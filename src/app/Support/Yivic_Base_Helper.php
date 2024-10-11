<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support;

class Yivic_Base_Helper {
	public static $version_option;
	public static $setup_info;
	public static $wp_app_check = null;

	public static function initialize( string $plugin_url, string $dirname ) {
		// Check if WordPress core is loaded, if not, exit the method.
		if ( ! static::is_wp_core_loaded() ) {
			return;
		}

		// Register the CLI initialization action.
		static::register_cli_init_action();

		// If not in console mode and the WP app check fails, exit the method.
		if ( ! static::is_console_mode() && ! static::perform_wp_app_check() ) {
			// We do nothing but still keep the plugin enabled
			return;
		}

		// If not in console mode, register the setup app redirect before WP App init.
		if ( ! static::is_console_mode() ) {
			static::register_setup_app_redirect();
		} elseif ( static::is_yivic_base_prepare_command() ) {
			static::prepare_wp_app_folders();
		}

		// Register the WP App setup hooks.
		static::init_wp_app_instance();

		// Register the action to signal that the WP App has fully loaded.
		static::init_yivic_base_wp_plugin_instance( $plugin_url, $dirname );
	}

	public static function get_current_url(): string {
		if ( empty( $_SERVER['SERVER_NAME'] ) && empty( $_SERVER['HTTP_HOST'] ) ) {
			return '';
		}

		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
			$_SERVER['HTTPS'] = 'on';
		}

		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$http_protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		}

		$current_url = $http_protocol ?? '';
		$current_url .= $current_url ? '://' : '//';

		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$current_url .= sanitize_text_field( $_SERVER['HTTP_HOST'] ) . ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '' );

			return $current_url;
		}

		if ( isset( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] != '80' ) {
			$current_url .= sanitize_text_field( $_SERVER['SERVER_NAME'] ) . ':' . sanitize_text_field( $_SERVER['SERVER_PORT'] ) . ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '' );
		} else {
			$current_url .= sanitize_text_field( $_SERVER['SERVER_NAME'] ) . ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '' );
		}

		return $current_url;
	}

	public static function get_setup_app_uri( $full_url = false ): string {
		$uri = 'wp-app/setup-app/?force_app_running_in_console=1';

		return $full_url ? trim( site_url(), '/' ) . '/' . $uri : $uri;
	}

	public static function get_admin_setup_app_uri( $full_url = false ): string {
		$uri = 'wp-app/admin/setup-app/?force_app_running_in_console=1';

		return $full_url ? trim( site_url(), '/' ) . '/' . $uri : $uri;
	}

	public static function get_wp_login_url( $return_url = '', $force_reauth = false ): string {
		return wp_login_url( $return_url, $force_reauth );
	}

	public static function at_setup_app_url(): bool {
		$current_url = static::get_current_url();
		$setup_app_uri = static::get_setup_app_uri();

		return ( strpos( $current_url, $setup_app_uri ) !== false );
	}

	public static function at_admin_setup_app_url(): bool {
		$current_url = static::get_current_url();
		$redirect_uri = static::get_admin_setup_app_uri();

		return ( strpos( $current_url, $redirect_uri ) !== false );
	}

	public static function at_wp_login_url(): bool {
		$current_url = static::get_current_url();
		$login_url = wp_login_url();

		return ( strpos( $current_url, $login_url ) !== false );
	}

	public static function redirect_to_setup_url(): void {
		$redirect_uri = static::get_setup_app_uri();
		if ( ! static::at_setup_app_url() && ! static::at_admin_setup_app_url() ) {
			$redirect_url = add_query_arg(
				[
					'return_url' => urlencode( static::get_current_url() ),
				],
				site_url( $redirect_uri )
			);
			header( 'Location: ' . $redirect_url );
			exit( 0 );
		}
	}

	public static function get_base_url_path(): string {
		$site_url_parts = wp_parse_url( site_url() );

		return empty( $site_url_parts['path'] ) ? '' : $site_url_parts['path'];
	}

	public static function get_current_blog_path() {
		$site_url = site_url();
		$network_site_url = network_site_url();

		if ( $site_url === $network_site_url ) {
			return null;
		}

		$reverse_pos = strpos( strrev( $site_url ), strrev( $network_site_url ) );
		if ( $reverse_pos === false ) {
			return null;
		}

		return trim( substr( $site_url, $reverse_pos * ( -1 ) ), '/' );
	}

	public static function get_version_option() {
		if ( empty( static::$version_option ) ) {
			static::$version_option = (string) get_option( App_Const::OPTION_VERSION, '0.0.0' );
		}

		return static::$version_option;
	}

	public static function get_setup_info() {
		if ( empty( static::$setup_info ) ) {
			static::$setup_info = (string) get_option( App_Const::OPTION_SETUP_INFO );
		}

		return static::$setup_info;
	}

	public static function is_setup_app_completed() {
		// We have migration for session with db from '0.7.0'
		return apply_filters( 'yivic_base_is_setup_app_completed', version_compare( static::get_version_option(), '0.7.0', '>=' ) );
	}

	public static function is_setup_app_failed() {
		return static::get_setup_info() === 'failed';
	}

	/**
	 * We want to check if the wp_app setup has been done correctly
	 *  If the setup process failed, we should return false and raise the notice in the Admin
	 */
	public static function perform_wp_app_check(): bool {
		// We only want to perform the checking once
		if ( static::$wp_app_check !== null ) {
			return (bool) static::$wp_app_check;
		}

		if ( ! static::is_pdo_mysql_loaded() ) {
			$error_message = sprintf(
				// translators: %1$s is replaced by a string, extension name
				__( 'Error with PHP extention %1$s. Please enable PHP extension %1$s via your hosting Control Panel or contact your hosting Admin for that.', 'yivic' ),
				'PDO MySQL'
			);
			static::add_wp_app_setup_errors( $error_message );
		}

		if ( empty( static::get_wp_app_setup_errors() ) && static::is_setup_app_completed() ) {
			static::$wp_app_check = apply_filters( App_Const::FILTER_WP_APP_CHECK, true );

			return static::$wp_app_check;
		}

		// We only want to check if it's not in the setup url
		if ( ! static::at_setup_app_url() && ! static::at_admin_setup_app_url() && static::is_setup_app_failed() ) {
			$error_message = sprintf(
				// translators: %1$s is replaced by a string, url
				__( 'The setup has not been done correctly. Please go to this URL <a href="%1$s">%1$s</a> to complete the setup', 'yivic' ),
				static::get_admin_setup_app_uri( true )
			);
			static::add_wp_app_setup_errors( $error_message );
		}

		if ( ! empty( $GLOBALS['wp_app_setup_errors'] ) ) {
			static::put_messages_to_wp_admin_notice( $GLOBALS['wp_app_setup_errors'] );
			static::$wp_app_check = apply_filters( App_Const::FILTER_WP_APP_CHECK, false );

			return static::$wp_app_check;
		}

		static::$wp_app_check = apply_filters( App_Const::FILTER_WP_APP_CHECK, true );

		return apply_filters( App_Const::FILTER_WP_APP_CHECK, true );
	}

	public static function put_messages_to_wp_admin_notice( array &$error_messages ): void {
		add_action(
			'admin_notices',
			function () use ( $error_messages ) {
				Yivic_Base_Hook_Handlers::print_admin_notice_messages( $error_messages );
			}
		);
	}

	public static function is_console_mode(): bool {
		return ( (string) static::get_php_sapi_name() === 'cli' || (string) static::get_php_sapi_name() === 'phpdbg' || (string) static::get_php_sapi_name() === 'cli-server' );
	}

	public static function add_wp_app_setup_errors( $error_message ): void {
		if ( ! isset( $GLOBALS['wp_app_setup_errors'] ) ) {
			$GLOBALS['wp_app_setup_errors'] = [];
		}

		if ( ! isset( $GLOBALS['wp_app_setup_errors'][ $error_message ] ) ) {
			$GLOBALS['wp_app_setup_errors'][ $error_message ] = false;
		}
	}

	public static function get_wp_app_setup_errors() {
		return isset( $GLOBALS['wp_app_setup_errors'] ) ? (array) $GLOBALS['wp_app_setup_errors'] : [];
	}

	public static function use_yivic_base_error_handler(): bool {
		$use_error_handler = static::get_use_error_handler_setting();

		return apply_filters( 'yivic_base_use_error_handler', $use_error_handler );
	}

	public static function get_use_error_handler_setting(): bool {
		if ( defined( 'YIVIC_BASE_USE_ERROR_HANDLER' ) ) {
			return (bool) YIVIC_BASE_USE_ERROR_HANDLER;
		}
		$env_value = getenv( 'YIVIC_BASE_USE_ERROR_HANDLER' );

		return $env_value !== false ? (bool) $env_value : false;
	}

	public static function use_blade_for_wp_template(): bool {
		$blade_for_template = static::get_blade_for_wp_template_setting();

		return apply_filters( 'yivic_base_use_blade_for_wp_template', $blade_for_template );
	}

	public static function get_blade_for_wp_template_setting(): bool {
		if ( defined( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE' ) ) {
			return (bool) YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE;
		}
		$env_value = getenv( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE' );

		return $env_value !== false ? (bool) $env_value : false;
	}

	public static function disable_web_worker(): bool {
		$disable_web_worker = static::get_disable_web_worker_status();
		return apply_filters( 'yivic_base_disable_web_worker', $disable_web_worker );
	}

	public static function get_disable_web_worker_status(): bool {
		if ( defined( 'YIVIC_BASE_DISABLE_WEB_WORKER' ) ) {
			return (bool) YIVIC_BASE_DISABLE_WEB_WORKER;
		}
		$env_value = getenv( 'YIVIC_BASE_DISABLE_WEB_WORKER' );

		return $env_value !== false ? (bool) $env_value : false;
	}

	public static function get_wp_app_base_path() {
		if ( defined( 'YIVIC_BASE_WP_APP_BASE_PATH' ) && YIVIC_BASE_WP_APP_BASE_PATH ) {
			return YIVIC_BASE_WP_APP_BASE_PATH;
		} else {
			return WP_CONTENT_DIR . DIR_SEP . 'uploads' . DIR_SEP . 'wp-app';
		}
	}

	public static function get_wp_app_base_folders_paths( string $wp_app_base_path ) {
		return [
			'base_path' => $wp_app_base_path,
			'config_path' => $wp_app_base_path . DIR_SEP . 'config',
			'database_path' => $wp_app_base_path . DIR_SEP . 'database',
			'database_migrations_path' => $wp_app_base_path . DIR_SEP . 'database' . DIR_SEP . 'migrations',
			'bootstrap_path' => $wp_app_base_path . DIR_SEP . 'bootstrap',
			'bootstrap_cache_path' => $wp_app_base_path . DIR_SEP . 'bootstrap' . DIR_SEP . 'cache',
			'lang_path' => $wp_app_base_path . DIR_SEP . 'lang',
			'resources_path' => $wp_app_base_path . DIR_SEP . 'resources',
			'storage_path' => $wp_app_base_path . DIR_SEP . 'storage',
			'storage_logs_path' => $wp_app_base_path . DIR_SEP . 'storage' . DIR_SEP . 'logs',
			'storage_framework_path' => $wp_app_base_path . DIR_SEP . 'storage' . DIR_SEP . 'framework',
			'storage_framework_views_path' => $wp_app_base_path . DIR_SEP . 'storage' . DIR_SEP . 'framework' . DIR_SEP . 'views',
			'storage_framework_cache_path' => $wp_app_base_path . DIR_SEP . 'storage' . DIR_SEP . 'framework' . DIR_SEP . 'cache',
			'storage_framework_cache_data_path' => $wp_app_base_path . DIR_SEP . 'storage' . DIR_SEP . 'framework' . DIR_SEP . 'cache' . DIR_SEP . 'data',
			'storage_framework_sessions_path' => $wp_app_base_path . DIR_SEP . 'storage' . DIR_SEP . 'framework' . DIR_SEP . 'sessions',
		];
	}

	/**
	 *
	 * @param string $wp_app_base_path
	 * @param int $chmod We may want to use `0755` if running this function in console
	 * @return void
	 */
	public static function prepare_wp_app_folders( $chmod = 0777, string $wp_app_base_path = '' ): void {
		if ( empty( $wp_app_base_path ) ) {
			$wp_app_base_path = static::get_wp_app_base_path();
		}
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.chmod_chmod, WordPress.PHP.NoSilencedErrors.Discouraged
		@chmod( dirname( $wp_app_base_path ), $chmod );

		$file_system = new \Illuminate\Filesystem\Filesystem();

		foreach ( static::get_wp_app_base_folders_paths( $wp_app_base_path ) as $filepath ) {
			$file_system->ensureDirectoryExists( $filepath, $chmod );
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.chmod_chmod, WordPress.PHP.NoSilencedErrors.Discouraged
			@chmod( $filepath, $chmod );
		}
	}

	public static function wp_cli_init(): void {
		\WP_CLI::add_command(
			'yivic-base prepare',
			[ static::class, 'wp_cli_prepare' ]
		);
	}

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	public static function wp_cli_prepare( $args, $assoc_args ): void {
		static::prepare_wp_app_folders();
	}

	/**
	 * Check the flag in the options to redirect to setup page if needed
	 */
	public static function maybe_redirect_to_setup_app(): void {
		if ( ! static::is_setup_app_completed() ) {
			static::prepare_wp_app_folders();

			// We only want to redirect if the setup did not fail previously
			if ( ! static::is_setup_app_failed() ) {
				static::redirect_to_setup_url();
			}
		}
	}

	/**
	 * Get the correct timezone value for WP App (from WordPress and map to the date_default_timezone_set ids)
	 * @return string
	 */
	public static function wp_app_get_timezone(): string {
		$current_offset = (int) get_option( 'gmt_offset' );
		$timezone_string = get_option( 'timezone_string' );

		// Remove old Etc mappings. Fallback to gmt_offset.
		if ( strpos( $timezone_string, 'Etc/GMT' ) !== false ) {
			$timezone_string = '';
		}

		// Create Etc/GMT time zone id that match date_default_timezone_set function
		//  https://www.php.net/manual/en/timezones.others.php
		if ( empty( $timezone_string ) ) {
			if ( (int) $current_offset === 0 ) {
				$timezone_string = 'Etc/GMT';
			} elseif ( $current_offset < 0 ) {
				$timezone_string = 'Etc/GMT+' . abs( $current_offset );
			} else {
				$timezone_string = 'Etc/GMT-' . abs( $current_offset );
			}
		}

		if ( function_exists( 'wp_timezone' ) ) {
			return strpos( wp_timezone()->getName(), '/' ) !== false ? wp_timezone()->getName() : $timezone_string;
		}

		return defined( 'WP_APP_TIMEZONE' ) ? WP_APP_TIMEZONE : $timezone_string;
	}

	/**
	 * Determine and return the WP App asset URL.
	 *
	 * @param false $full_url
	 * @return string
	 */
	public static function wp_app_get_asset_url( $full_url = false ): string {
		if ( defined( 'YIVIC_BASE_WP_APP_ASSET_URL' ) && YIVIC_BASE_WP_APP_ASSET_URL ) {
			return YIVIC_BASE_WP_APP_ASSET_URL;
		}

		$slug_to_wp_app = str_replace( ABSPATH, '', static::get_wp_app_base_path() );
		$slug_to_public_asset = '/' . $slug_to_wp_app . '/public';

		return $full_url ? trim( get_site_url(), '/' ) . $slug_to_public_asset : $slug_to_public_asset;
	}

	/**
	 * Extract and return the major version number from the version string.
	 *
	 * @param $version
	 * @return int
	 */
	public static function get_major_version( $version ): int {
		$parts = explode( '.', $version );

		return (int) filter_var( $parts[0], FILTER_SANITIZE_NUMBER_INT );
	}

	/**
	 * Return the WP App web page title.
	 *
	 * @return mixed|void|null
	 */
	public static function wp_app_web_page_title() {
		$title = empty( wp_title( '', false ) )
			? get_bloginfo( 'name' ) . ' | ' . ( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : 'WP App' )
			: wp_title( '', false );

		return apply_filters( App_Const::FILTER_WP_APP_WEB_PAGE_TITLE, $title );
	}

	public static function is_wp_core_loaded(): bool {
		return (bool) defined( 'WP_CONTENT_DIR' );
	}

	public static function get_php_sapi_name(): string {
		return php_sapi_name();
	}

	public static function is_pdo_mysql_loaded(): bool {
		return extension_loaded( 'pdo_mysql' );
	}

	public static function register_cli_init_action() {
		add_action( 'cli_init', [ static::class, 'wp_cli_init' ] );
	}

	public static function register_setup_app_redirect() {
		add_action(
			YIVIC_BASE_SETUP_HOOK_NAME,
			[ static::class, 'maybe_redirect_to_setup_app' ],
			-200
		);
	}

	public static function is_yivic_base_prepare_command( array $argv = null ): bool {
		// Default to using $_SERVER['argv'] if not provided
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$argv = $argv ?? $_SERVER['argv'];

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ! empty( $argv ) && array_intersect( (array) $argv, [ 'yivic-base', 'prepare' ] );
	}

	public static function init_wp_app_instance() {
		add_action(
			YIVIC_BASE_SETUP_HOOK_NAME,
			[ \Yivic_Base\App\WP\WP_Application::class, 'load_instance' ],
			-100
		);
	}

	public static function init_yivic_base_wp_plugin_instance( string $plugin_url, string $dirname ) {
		add_action(
			\Yivic_Base\App\Support\App_Const::ACTION_WP_APP_LOADED,
			function () use ( $plugin_url, $dirname ) {
				static::handle_wp_app_loaded_action( $plugin_url, $dirname );
			}
		);
	}

	public static function handle_wp_app_loaded_action( string $plugin_url, string $dirname ): void {
		\Yivic_Base\App\WP\Yivic_Base_WP_Plugin::init_with_wp_app(
			YIVIC_BASE_PLUGIN_SLUG,
			$dirname,
			$plugin_url
		);
	}

	/**
	* Generate the URL of a full WordPress URL with domain name to a named route.
	*
	* @param  array|string  $name
	* @param  mixed  $parameters
	* @param  bool  $absolute
	* @return string
	*/
	public static function route_with_wp_url( $name, $parameters = [] ) {
		return rtrim( site_url(), '/' ) . route( $name, $parameters, false );
	}
}
