<?php

declare(strict_types=1);

namespace Yivic_Base\App\WP;

use Carbon\Carbon;
use Yivic_Base\App\Actions\Add_More_Providers_Action;
use Yivic_Base\App\Actions\Bootstrap_WP_App_Action;
use Yivic_Base\App\Actions\Login_WP_App_User_Action;
use Yivic_Base\App\Actions\Logout_WP_App_User_Action;
use Yivic_Base\App\Actions\Perform_Setup_WP_App_Action;
use Yivic_Base\App\Actions\Perform_Web_Worker_Action;
use Yivic_Base\App\Actions\Show_Admin_Notice_From_Flash_Messages_Action;
use Yivic_Base\App\Actions\Write_Setup_Client_Script_Action;
use Yivic_Base\App\Actions\Write_Web_Worker_Script_Action;
use Yivic_Base\App\Console\Commands\WP_App_Make_PHPUnit_Command;
use Yivic_Base\App\Http\Response;
use Yivic_Base\App\Actions\Process_WP_Api_Request_Action;
use Yivic_Base\App\Actions\Process_WP_App_Request_Action;
use Yivic_Base\App\Actions\Put_Setup_Error_Message_To_Log_File_Action;
use Yivic_Base\App\Actions\Register_Base_WP_Api_Routes_Action;
use Yivic_Base\App\Actions\Register_Base_WP_App_Routes_Action;
use Yivic_Base\App\Actions\Schedule_Run_Backup_Action;
use Yivic_Base\App\Support\App_Const;
use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\App\Support\Traits\Yivic_Base_Trans_Trait;
use Yivic_Base\Foundation\WP\WP_Plugin;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\ViewException;
use InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use WP_CLI;
use WP_User;

/**
 * @package Yivic_Base\App\WP
 */
final class Yivic_Base_WP_Plugin extends WP_Plugin {
	use Yivic_Base_Trans_Trait;

	public function boot() {
		parent::boot();

		Carbon::now();

		if ( $this->app->runningInConsole() ) {
			// Publish assets
			$this->publishes(
				[
					$this->get_base_path() . '/public-assets/dist' => public_path( 'plugins/' . $this->get_plugin_slug() ),
				],
				[ 'yivic-base-assets', 'laravel-assets' ]
			);

			// Publish stubs
			$this->publishes(
				[
					$this->get_base_path() . '/resources' => resource_path( 'plugins/' . $this->get_plugin_slug() ),
				],
				[ 'yivic-base-assets', 'laravel-assets' ]
			);

			// Register Commands
			$this->commands(
				[
					WP_App_Make_PHPUnit_Command::class,
				]
			);

			$this->loadMigrationsFrom( __DIR__ . '/../../../database/migrations' );
		}
	}

	public function get_name(): string {
		return 'Yivic Base';
	}

	public function get_version(): string {
		return YIVIC_BASE_PLUGIN_VERSION;
	}

	/**
	 * @inheritDoc
	 * @return void
	 * @throws ExpectationFailedException
	 * @throws Exception
	 */
	public function manipulate_hooks(): void {
		// We want to stop some default actions of WordPress
		$this->prevent_defaults();

		// We want to create hooks for this plugin here
		$this->enroll_self_hooks();

		/** WP App hooks */
		// We want to bootstrap the app(). We use the closure here to ensure that
		//  it can't be removed
		add_action(
			App_Const::ACTION_WP_APP_BOOTSTRAP,
			[ $this, 'bootstrap_wp_app' ],
			5
		);

		// If running in WP_CLI, we need to skip this
		if ( ! class_exists( 'WP_CLI' ) ) {
			add_action(
				App_Const::ACTION_WP_APP_INIT,
				[ $this, 'build_wp_app_response_via_middleware' ],
				5
			);
			add_action( App_Const::ACTION_WP_APP_INIT, [ $this, 'sync_wp_user_to_wp_app_user' ] );
		}

		// We need to have app() terminated before shutting down WP
		add_action( App_Const::ACTION_WP_APP_COMPLETE_EXECUTION, [ $this, 'perform_wp_app_termination' ] );


		add_action( App_Const::ACTION_WP_APP_REGISTER_ROUTES, [ $this, 'register_base_wp_app_routes' ] );
		add_action( App_Const::ACTION_WP_API_REGISTER_ROUTES, [ $this, 'register_base_wp_api_routes' ] );

		add_action( App_Const::ACTION_WP_APP_SCHEDULE_RUN, [ $this, 'schedule_run_backup' ] );
		add_action( App_Const::ACTION_WP_APP_WEB_WORKER, [ $this, 'web_worker' ] );

		// We want to use the priority 1000 to let this run at the end for running migration
		add_action( App_Const::ACTION_WP_APP_SETUP_APP, [ $this, 'setup_app' ], 1000 );
		add_action( App_Const::ACTION_WP_APP_MARK_SETUP_APP_FAILED, [ $this, 'put_error_message_to_log_file' ] );

		add_filter( App_Const::FILTER_WP_APP_MAIN_SERVICE_PROVIDERS, [ $this, 'register_more_providers' ] );

		/** Other hooks */
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ], 100 );
		if ( $this->is_blade_for_template_available() ) {
			add_filter( 'template_include', [ $this, 'use_blade_to_compile_template' ], 99999 );
		}

		if ( app()->is_wp_app_mode() ) {
			// We want to let WP App work the soonest after WP is fully loaded
			add_action( 'wp_loaded', [ $this, 'process_wp_app_request' ], -9999 );
		}

		if ( app()->is_wp_api_mode() ) {
			add_action( 'init', [ $this, 'process_wp_api_request' ], 999999 );
		}

		add_action( 'wp_login', [ $this, 'login_wp_app_user' ], 10, 2 );
		add_action( 'wp_logout', [ $this, 'logout_wp_app_user' ] );

		add_action( 'admin_print_footer_scripts', [ $this, 'write_setup_wp_app_client_script' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'write_web_worker_client_script' ], 10000 );

		add_action( 'admin_head', [ $this, 'handle_admin_head' ] );

		if ( ! app()->is_wp_app_mode() && ! app()->is_wp_api_mode() ) {
			// We want to merge the WP and WP App headers and send at once
			if ( is_admin() ) {
				add_action(
					'admin_init',
					[ $this, 'send_wp_app_headers' ]
				);
			} else {
				add_action(
					'send_headers',
					[ $this, 'send_wp_app_headers' ]
				);
			}
		}

		// We use the filter `status_header` to get the status code
		//  to store to the app() instance
		add_filter( 'status_header', [ $this, 'store_status_header' ], 999999, 4 );

		/** WP CLI */
		add_action( 'cli_init', [ $this, 'register_wp_cli_commands' ] );
	}

	public function setup_app(): void {
		Perform_Setup_WP_App_Action::exec();
	}

	public function put_error_message_to_log_file( $message ): void {
		Put_Setup_Error_Message_To_Log_File_Action::exec( $message );
	}

	public function bootstrap_wp_app(): void {
		Bootstrap_WP_App_Action::exec();
	}

	public function write_setup_wp_app_client_script(): void {
		Write_Setup_Client_Script_Action::exec();
	}

	public function write_web_worker_client_script(): void {
		Write_Web_Worker_Script_Action::exec();
	}

	public function register_base_wp_app_routes(): void {
		Register_Base_WP_App_Routes_Action::exec();
	}

	public function register_base_wp_api_routes(): void {
		Register_Base_WP_Api_Routes_Action::exec();
	}

	public function register_wp_cli_commands(): void {
		WP_CLI::add_command(
			'yivic-base info',
			resolve( \Yivic_Base\App\WP_CLI\Yivic_Base_Info_WP_CLI::class )
		);
		WP_CLI::add_command(
			'yivic-base artisan',
			$this->app->make( \Yivic_Base\App\WP_CLI\Yivic_Base_Artisan_WP_CLI::class )
		);
	}

	public function process_wp_app_request(): void {
		Process_WP_App_Request_Action::exec();
	}

	public function process_wp_api_request(): void {
		Process_WP_Api_Request_Action::exec();
	}

	/**
	 * @throws \Exception
	 */
	public function use_blade_to_compile_template( $template ) {
		/** @var \Illuminate\View\Factory $view */
		$view = view();
		// We want to have blade to compile the php file as well
		$view->addExtension( 'php', 'blade' );

		// We catch exception if view is not rendered correctly
		//  exception InvalidArgumentException for view file not found in FileViewFinder
		try {
			$tmp_view = view( basename( $template, '.php' ) );
			/** @var \Illuminate\View\View $tmp_view */
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $tmp_view->render();
			$template = false;

		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( InvalidArgumentException $invalid_argument_exception ) {
			// We simply want to do nothing on the InvalidArgumentException
			//  The reason for it is to let the WP handle the template if
			//  Blade cannot find the template file
		} catch ( ViewException $view_exception ) {
			if ( ! empty( $view_exception->getPrevious() ) ) {
				if ( ! empty( $view_exception->getPrevious()->getPrevious() ) ) {
					throw $view_exception->getPrevious()->getPrevious();
				}

				throw $view_exception->getPrevious();
			}

			throw $view_exception;
		} catch ( Exception $e ) {
			throw $e;
		}

		return $template;
	}

	public function register_more_providers( $providers ) {
		return Add_More_Providers_Action::exec( $providers );
	}

	/**
	 * We want to put all handler for Admin Head here
	 *
	 * @return void
	 * @throws BindingResolutionException
	 */
	public function handle_admin_head() {
		Show_Admin_Notice_From_Flash_Messages_Action::exec();
	}

	/**
	 * Actions to be performed on Queue Work polling Ajax
	 * @return void
	 * @throws BindingResolutionException
	 */
	public function web_worker() {
		Perform_Web_Worker_Action::exec();
	}

	/**
	 * Execution for `schedule:run`
	 * @param Schedule $schedule
	 * @return void
	 * @throws BindingResolutionException
	 * @throws InvalidArgumentException
	 */
	public function schedule_run_backup( Schedule $schedule ) {
		Schedule_Run_Backup_Action::exec( $schedule );
	}

	/**
	 * We want to let the request go through Laravel middleware
	 *  including StartSession to have Laravel session working with WP as well
	 * @return void
	 */
	public function build_wp_app_response_via_middleware() {
		if ( ! app()->is_wp_app_mode() && ! app()->is_wp_api_mode() ) {
			/** @var \Yivic_Base\App\Http\Kernel $kernel */
			$kernel = app()->make( \Illuminate\Contracts\Http\Kernel::class );
			$middleware_group = $kernel->getMiddlewareGroups()['web'];

			// We don't want VerifyCsrfToken and SubstituteBindings as
			//  they need Laravel router to work correctly
			$middleware_group = array_flip( $middleware_group );
			unset( $middleware_group[ \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class ] );
			unset( $middleware_group[ \Illuminate\Routing\Middleware\SubstituteBindings::class ] );
			$middleware_group = array_flip( $middleware_group );

			$wp_app_request = request();
			/** @var Response $wp_app_response */
			$wp_app_response = $kernel->send_request_through_middleware(
				$wp_app_request,
				$middleware_group,
				function ( $request ) {
					$response = new Response();
					$response->set_request( $request );
					return $response;
				}
			);
			app()->set_response( $wp_app_response );
			app()->set_request( $wp_app_response->get_request() );
		}
	}

	/**
	 * We want to merge WordPress header with Laravel headers and send them to the client
	 * @return void
	 * @throws BindingResolutionException
	 * @throws InvalidArgumentException
	 */
	public function send_wp_app_headers(): void {
		$wp_headers = app()->get_wp_headers();

		/** @var Response $wp_app_response */
		$wp_app_response = response();
		foreach ( (array) $wp_headers as $wp_header_key => $wp_header_value ) {
			$wp_app_response->header( $wp_header_key, $wp_header_value );
		}

		// We need to check response status code from WP
		if ( is_404() ) {
			$code = 404;
		}
		! isset( $code ) || $wp_app_response->setStatusCode( $code );

		// We need to check status code sent by `status_header()` to override the status code
		if ( app()->has( 'status_header' ) ) {
			$status_header = app( 'status_header' );
			! isset( $status_header['code'] ) || $wp_app_response->setStatusCode( $status_header['code'] );
		}

		$wp_app_response->sendHeaders();
	}

	/**
	 * We want to sync WP logged in user to Laravel User
	 * @return void
	 */
	public function sync_wp_user_to_wp_app_user() {
		if ( ! empty( get_current_user_id() ) && empty( Auth::user() ) ) {
			Login_WP_App_User_Action::exec( get_current_user_id() );
		}
	}

	public function login_wp_app_user( $user_login, WP_User $wp_user ) {
		Login_WP_App_User_Action::exec( $wp_user->ID );
	}

	public function logout_wp_app_user( $user_id ) {
		Logout_WP_App_User_Action::exec();
	}

	public function load_textdomain() {
		$locale = determine_locale();
		load_textdomain( 'yivic', $this->get_base_path() . '/languages/yivic-' . $locale . '.mo' );
	}

	/**
	 * We want to store status code when using `status_header()` to `app()` instance
	 * @param mixed $status_header
	 * @param mixed $code
	 * @param mixed $description
	 * @param mixed $protocol
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	public function store_status_header( $status_header, $code, $description, $protocol ) {
		app()->instance(
			'status_header',
			[
				'status_header' => $status_header,
				'code' => $code,
				'description' => $description,
				'protocol' => $protocol,
			]
		);

		return $status_header;
	}

	/**
	 * We want to terminate the wp_app on shutdown event
	 * @return void
	 * @throws BindingResolutionException
	 * @throws InvalidArgumentException
	 */
	public function perform_wp_app_termination() {
		/** @var \Yivic_Base\App\Http\Kernel $kernel */
		$kernel = app()->make( \Illuminate\Contracts\Http\Kernel::class );
		$kernel->terminate( request(), response() );
	}

	/**
	 * Prevent some default behaviors from WP
	 * @return void
	 */
	private function prevent_defaults(): void {
		if ( ! app()->is_wp_app_mode() && ! app()->is_wp_api_mode() ) {
			add_filter(
				'wp_headers',
				// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
				function ( $headers ) {
					app()->set_wp_headers( $headers );
					return [];
				},
				999999,
				1
			);
		}
	}

	/**
	 * All hooks created by this plugin should be enrolled here
	 * @return void
	 */
	private function enroll_self_hooks(): void {
		// For `yivic_base_wp_app_bootstrap`
		//  We add this hook to perform the bootstrap actions needed for WP App
		add_action(
			'after_setup_theme',
			function () {
				do_action( App_Const::ACTION_WP_APP_BOOTSTRAP );
			},
			1000
		);

		// For `yivic_base_wp_app_init`
		//  We want this hook works after all the init steps worked on all plugins
		//  for other plugins can hook to this process
		add_action(
			'init',
			function () {
				do_action( App_Const::ACTION_WP_APP_INIT );
			},
			999999
		);

		if ( ! app()->is_wp_app_mode() && ! app()->is_wp_api_mode() ) {
			// We need to have app() terminated before shutting down WP
			add_action(
				'shutdown',
				function () {
					do_action( App_Const::ACTION_WP_APP_COMPLETE_EXECUTION );
				},
				1
			);
		}
	}

	private function is_blade_for_template_available(): bool {
		return Yivic_Base_Helper::use_blade_for_wp_template() && ( ! app()->is_wp_app_mode() );
	}
}
