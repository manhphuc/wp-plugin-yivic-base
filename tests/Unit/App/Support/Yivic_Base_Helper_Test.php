<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Support;

use DateTimeZone;
use Yivic_Base\App\Support\App_Const;
use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_False;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Get_Base_Path;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Initialize_In_Console_Mode_False;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Initialize_In_Console_Mode_True;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Initialize_Is_Yivic_Base_Prepare_Command;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Initialize_Is_Perform_WP_App_Check;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Initialize_Wp_Core_Loaded_False;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_True;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Apache;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Cli;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Cli_Server;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Phpdbg;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Perform_Wp_App_True;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Prepare_Wp_App;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Setup_App_Completed_No_Error;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Setup_App_Not_Completed;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test\Yivic_Base_Helper_Test_Tmp_Setup_App_Url;
use Mockery;
use WP_Mock;

class Yivic_Base_Helper_Test extends Unit_Test_Case {
	private $backup_SERVER = [];

	public $plugin_url;

	public $dirname;

	public static $methods;

	protected function setUp(): void {
		parent::setUp();
		static::$methods = [];

		$this->plugin_url = 'http://example.com/wp-content/plugins/my-plugin/';
		$this->dirname = '/var/www/html/wp-content/plugins/my-plugin';

		global $_SERVER;

		$this->backup_SERVER = $_SERVER;

		WP_Mock::setUp();

		WP_Mock::userFunction( 'wp_unslash' )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);
	}

	protected function tearDown(): void {
		global $_SERVER;

		$_SERVER = $this->backup_SERVER;

		parent::tearDown();
		Mockery::close();
		WP_Mock::tearDown();
	}

	public function test_initialize_is_wp_core_loaded_false() {
		Yivic_Base_Helper_Test_Tmp_Initialize_Wp_Core_Loaded_False::initialize( $this->plugin_url, $this->dirname );

		$this->assertTrue( ! in_array( 'init_wp_app_instance', static::$methods ) );
	}

	public function test_initialize_in_console_mode_false() {
		Yivic_Base_Helper_Test_Tmp_Initialize_In_Console_Mode_False::initialize( $this->plugin_url, $this->dirname );

		$this->assertTrue( in_array( 'register_setup_app_redirect', static::$methods ) );
		$this->assertTrue( in_array( 'register_cli_init_action', static::$methods ) );
		$this->assertTrue( in_array( 'init_wp_app_instance', static::$methods ) );
	}

	public function test_initialize_in_console_mode_true() {
		Yivic_Base_Helper_Test_Tmp_Initialize_In_Console_Mode_True::initialize( $this->plugin_url, $this->dirname );

		$this->assertTrue( in_array( 'register_cli_init_action', static::$methods ) );
		$this->assertTrue( in_array( 'init_wp_app_instance', static::$methods ) );
		$this->assertTrue( in_array( 'init_yivic_base_wp_plugin_instance', static::$methods ) );
	}

	public function test_initialize_in_console_mode_true_is_yivic_base_prepare_command() {
		Yivic_Base_Helper_Test_Tmp_Initialize_Is_Yivic_Base_Prepare_Command::initialize( $this->plugin_url, $this->dirname );

		$this->assertTrue( in_array( 'register_cli_init_action', static::$methods ) );
		$this->assertTrue( in_array( 'prepare_wp_app_folders', static::$methods ) );
		$this->assertTrue( in_array( 'init_wp_app_instance', static::$methods ) );
		$this->assertTrue( in_array( 'init_yivic_base_wp_plugin_instance', static::$methods ) );
		$this->assertTrue( ! in_array( 'register_setup_app_redirect', static::$methods ) );
	}

	public function test_initialize_is_perform_wp_app_check() {
		Yivic_Base_Helper_Test_Tmp_Initialize_Is_Perform_WP_App_Check::initialize( $this->plugin_url, $this->dirname );

		$this->assertTrue( in_array( 'register_cli_init_action', static::$methods ) );
		$this->assertTrue( ! in_array( 'register_setup_app_redirect', static::$methods ) );
		$this->assertTrue( ! in_array( 'init_wp_app_instance', static::$methods ) );
	}

	public function test_get_current_url(): void {
		global $_SERVER;
		$_SERVER['SERVER_NAME'] = '';

		$this->assertEquals( '', Yivic_Base_Helper::get_current_url() );
	}

	public function test_get_current_url_with_http_host(): void {
		global $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';
		$expected_url = 'http://example.com/test';

		WP_Mock::userFunction( 'wp_unslash' )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text === 'example.com' ? 'example.com' : '/test';
				}
			);

		$this->assertEquals( $expected_url, Yivic_Base_Helper::get_current_url() );
	}

	public function test_get_current_url_with_server_port(): void {
		global $_SERVER;
		$_SERVER['SERVER_NAME'] = 'localhost';
		$_SERVER['SERVER_PORT'] = '8080';
		$_SERVER['REQUEST_URI'] = '/page';

		WP_Mock::userFunction( 'sanitize_text_field' )
			->times( 4 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$this->assertEquals( '//localhost:8080/page', Yivic_Base_Helper::get_current_url() );

		$_SERVER['SERVER_PORT'] = null;

		WP_Mock::userFunction( 'sanitize_text_field' )
			->times( 2 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$this->assertEquals( '//localhost/page', Yivic_Base_Helper::get_current_url() );
	}

	public function test_get_current_url_with_https(): void {
		global $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/secure';
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		$expected_url = 'https://example.com/secure';

		WP_Mock::userFunction( 'sanitize_text_field' )
			->times( 4 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$this->assertEquals( $expected_url, Yivic_Base_Helper::get_current_url() );
	}

	public function test_get_setup_app_uri(): void {
		$this->assertEquals( 'wp-app/setup-app/?force_app_running_in_console=1', Yivic_Base_Helper::get_setup_app_uri( false ) );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( 'example.com//' );
		$this->assertEquals( 'example.com/wp-app/setup-app/?force_app_running_in_console=1', Yivic_Base_Helper::get_setup_app_uri( true ) );
	}

	public function test_get_admin_setup_app_uri(): void {
		$this->assertEquals( 'wp-app/admin/setup-app/?force_app_running_in_console=1', Yivic_Base_Helper::get_admin_setup_app_uri( false ) );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( 'example.com' );
		$this->assertEquals( 'example.com/wp-app/admin/setup-app/?force_app_running_in_console=1', Yivic_Base_Helper::get_admin_setup_app_uri( true ) );
	}

	public function test_get_wp_login_url(): void {
		WP_Mock::userFunction( 'wp_login_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( 'wp-login.php' );
		$this->assertEquals( 'wp-login.php', Yivic_Base_Helper::get_wp_login_url() );
	}

	public function test_at_setup_app_url(): void {
		global $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text === 'example.com' ? 'example.com' : '/test';
				}
			);
		$this->assertFalse( Yivic_Base_Helper::at_setup_app_url() );

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/wp-app/setup-app/?force_app_running_in_console=1';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$this->assertTrue( Yivic_Base_Helper::at_setup_app_url() );
	}

	public function test_at_admin_setup_app_url(): void {
		global $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text === 'example.com' ? 'example.com' : '/test';
				}
			);
		$this->assertFalse( Yivic_Base_Helper::at_admin_setup_app_url() );

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/wp-app/admin/setup-app/?force_app_running_in_console=1';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$this->assertTrue( Yivic_Base_Helper::at_admin_setup_app_url() );
	}

	public function test_at_wp_login_url(): void {
		global $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);
		WP_Mock::userFunction( 'wp_login_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( 'wp-login.php' );
		$this->assertFalse( Yivic_Base_Helper::at_wp_login_url() );

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/wp-login.php?abc=2';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->twice()
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);
		WP_Mock::userFunction( 'wp_login_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( 'wp-login.php' );
		$this->assertTrue( Yivic_Base_Helper::at_wp_login_url() );
	}

	public function test_redirect_to_setup_url(): void {
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/wp-app/setup-app/?force_app_running_in_console=1';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		WP_Mock::userFunction( 'wp_unslash' )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$this->expectOutputString( '' );

		$this->assertNull( Yivic_Base_Helper::redirect_to_setup_url() );

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/page';
		WP_Mock::userFunction( 'sanitize_text_field' )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);
		WP_Mock::userFunction( 'add_query_arg' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '&return_url=' . urlencode( Yivic_Base_Helper::get_current_url() );
				}
			);
		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $args ) {
					return 'http://example.com/wp-app/setup-app/?force_app_running_in_console=1';
				}
			);
		// We expect exception here as the method would send a header then exit
		$this->expectException( \PHPUnit\Framework\Exception::class );

		$this->assertNull( Yivic_Base_Helper::redirect_to_setup_url() );
	}

	public function test_get_base_url_path(): void {
		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com';
				}
			);
		WP_Mock::userFunction( 'wp_parse_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $url ) {
					return parse_url( $url );
				}
			);
		$this->assertEmpty( Yivic_Base_Helper::get_base_url_path() );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com/test';
				}
			);
		WP_Mock::userFunction( 'wp_parse_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $url ) {
					return parse_url( $url );
				}
			);
		$this->assertEquals( '/test', Yivic_Base_Helper::get_base_url_path() );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com/test/test2';
				}
			);
		WP_Mock::userFunction( 'wp_parse_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $url ) {
					return parse_url( $url );
				}
			);
		$this->assertEquals( '/test/test2', Yivic_Base_Helper::get_base_url_path() );
	}

	public function test_get_current_blog_path(): void {
		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com';
				}
			);
		WP_Mock::userFunction( 'network_site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com';
				}
			);
		$this->assertEquals( null, Yivic_Base_Helper::get_current_blog_path() );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com/path1';
				}
			);
		WP_Mock::userFunction( 'network_site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com';
				}
			);
		$this->assertEquals( 'path1', Yivic_Base_Helper::get_current_blog_path() );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com/path1/path2';
				}
			);
		WP_Mock::userFunction( 'network_site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com';
				}
			);
		$this->assertEquals( 'path1/path2', Yivic_Base_Helper::get_current_blog_path() );

		WP_Mock::userFunction( 'site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://example.com/path1/path2';
				}
			);
		WP_Mock::userFunction( 'network_site_url' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'http://not-an-example.com';
				}
			);
		$this->assertEquals( null, Yivic_Base_Helper::get_current_blog_path() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_is_setup_app_completed_false() {
		// We need to run this on a separate process to have Yivic_Base_Helper::$version_option reset
		WP_Mock::userFunction( 'get_option' )
			->times( 1 )
			->with( App_Const::OPTION_VERSION, Mockery::any() )
			->andReturnUsing(
				function ( $option_key ) {
					return '0.6.0';
				}
			);
		$this->assertFalse( Yivic_Base_Helper::is_setup_app_completed() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_is_setup_app_completed_true() {
		// We need to run this on a separate process to have Yivic_Base_Helper::$version_option reset
		WP_Mock::userFunction( 'get_option' )
			->times( 1 )
			->with( App_Const::OPTION_VERSION, Mockery::any() )
			->andReturnUsing(
				function ( $option_key ) {
					return '0.7.0';
				}
			);
		$this->assertTrue( Yivic_Base_Helper::is_setup_app_completed() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_is_setup_app_failed() {
		// We need to run this on a separate process to have Yivic_Base_Helper::$setup_info reset
		WP_Mock::userFunction( 'get_option' )
			->times( 1 )
			->with( App_Const::OPTION_SETUP_INFO )
			->andReturnUsing(
				function ( $option_key ) {
					return 'failed';
				}
			);
		$this->assertTrue( Yivic_Base_Helper::is_setup_app_failed() );
	}

	public function test_perform_wp_app_check_true() {
		// We assert this return true as $wp_app_check is set to true in tmp class
		$this->assertTrue( Yivic_Base_Helper_Test_Tmp_True::perform_wp_app_check() );
	}

	public function test_put_messages_to_wp_admin_notice() {
		// Mock the add_action function
		// Create a sample array of error messages
		$error_messages = [ 'Error 1', 'Error 2' ];

		// Mock add_action to ensure it is called with 'admin_notices'
		WP_Mock::expectActionAdded(
			'admin_notices',
			function ( $callback ) {
				// Ensure that the callback is of type Closure
				return true;
			}
		);

		// Call the method that adds the action
		Yivic_Base_Helper::put_messages_to_wp_admin_notice( $error_messages );

		// Verify that the action was added exactly once
		WP_Mock::assertHooksAdded();
	}

	public function test_add_wp_app_setup_errors() {
		// Clear the global variable for the test
		unset( $GLOBALS['wp_app_setup_errors'] );

		// Call the method
		Yivic_Base_Helper::add_wp_app_setup_errors( 'Test Error' );

		// Assert the global variable is initialized
		$this->assertIsArray( $GLOBALS['wp_app_setup_errors'] );
		$this->assertArrayHasKey( 'Test Error', $GLOBALS['wp_app_setup_errors'] );
		$this->assertFalse( $GLOBALS['wp_app_setup_errors']['Test Error'] );
	}

	public function test_get_wp_app_setup_errors_returns_empty_array_when_not_set() {
		// Ensure the global variable is not set
		unset( $GLOBALS['wp_app_setup_errors'] );

		// Call the method
		$result = Yivic_Base_Helper::get_wp_app_setup_errors();

		// Assert it returns an empty array
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	public function test_get_wp_app_setup_errors_returns_global_array_when_set() {
		// Set up the global variable with some errors
		$GLOBALS['wp_app_setup_errors'] = [
			'Error 1' => true,
			'Error 2' => false,
		];

		// Call the method
		$result = Yivic_Base_Helper::get_wp_app_setup_errors();

		// Assert it returns the global array
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertArrayHasKey( 'Error 1', $result );
		$this->assertArrayHasKey( 'Error 2', $result );
		$this->assertTrue( $result['Error 1'] );
		$this->assertFalse( $result['Error 2'] );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_get_wp_app_base_path_returns_defined_constant_value() {
		// Define the constant
		define( 'YIVIC_BASE_WP_APP_BASE_PATH', '/custom/path/to/wp-app' );

		// Call the method
		$result = Yivic_Base_Helper::get_wp_app_base_path();

		// Assert the result matches the defined constant value
		$this->assertEquals( '/custom/path/to/wp-app', $result );
	}

	public function test_get_wp_app_base_folders_paths() {
		// Define the base path and directory separator for the test
		$wp_app_base_path = '/var/www/wp-app';
		$dir_sep = '/'; // Typically '/' on Unix-based systems

		// Call the method
		$result = Yivic_Base_Helper::get_wp_app_base_folders_paths( $wp_app_base_path );

		// Expected paths array
		$expected = [
			'base_path' => $wp_app_base_path,
			'config_path' => $wp_app_base_path . $dir_sep . 'config',
			'database_path' => $wp_app_base_path . $dir_sep . 'database',
			'database_migrations_path' => $wp_app_base_path . $dir_sep . 'database' . $dir_sep . 'migrations',
			'bootstrap_path' => $wp_app_base_path . $dir_sep . 'bootstrap',
			'bootstrap_cache_path' => $wp_app_base_path . $dir_sep . 'bootstrap' . $dir_sep . 'cache',
			'lang_path' => $wp_app_base_path . $dir_sep . 'lang',
			'resources_path' => $wp_app_base_path . $dir_sep . 'resources',
			'storage_path' => $wp_app_base_path . $dir_sep . 'storage',
			'storage_logs_path' => $wp_app_base_path . $dir_sep . 'storage' . $dir_sep . 'logs',
			'storage_framework_path' => $wp_app_base_path . $dir_sep . 'storage' . $dir_sep . 'framework',
			'storage_framework_views_path' => $wp_app_base_path . $dir_sep . 'storage' . $dir_sep . 'framework' . $dir_sep . 'views',
			'storage_framework_cache_path' => $wp_app_base_path . $dir_sep . 'storage' . $dir_sep . 'framework' . $dir_sep . 'cache',
			'storage_framework_cache_data_path' => $wp_app_base_path . $dir_sep . 'storage' . $dir_sep . 'framework' . $dir_sep . 'cache' . $dir_sep . 'data',
			'storage_framework_sessions_path' => $wp_app_base_path . $dir_sep . 'storage' . $dir_sep . 'framework' . $dir_sep . 'sessions',
		];

		// Assert that the result matches the expected paths
		$this->assertEquals( $expected, $result );
	}

	public function test_wp_cli_init() {
		// Mock the WP_CLI class
		$mocked_wp_cli = Mockery::mock( 'alias:\WP_CLI' );

		// Expect that the add_command method will be called once with the expected parameters
		$mocked_wp_cli->shouldReceive( 'add_command' )
			->once()
			->with( 'yivic-base prepare', [ Yivic_Base_Helper::class, 'wp_cli_prepare' ] );

		// Call the method
		Yivic_Base_Helper::wp_cli_init();

		// The method doesn't return anything, so we assert that the mock expectations were met
		$this->assertTrue( true );
	}

	public function test_maybe_redirect_to_setup_app_when_setup_completed() {
		// Execute the method
		Yivic_Base_Helper_Test_Tmp_True::maybe_redirect_to_setup_app();
		$this->assertTrue( true );
	}

	public function test_maybe_redirect_to_setup_app_when_setup_not_completed() {
		// Execute the method
		Yivic_Base_Helper_Test_Tmp_Setup_App_Not_Completed::maybe_redirect_to_setup_app();
		$this->assertTrue( in_array( 'prepare_wp_app_folders', static::$methods ) );
		$this->assertTrue( in_array( 'redirect_to_setup_url', static::$methods ) );
	}

	public function test_wp_cli_prepare() {
		// Execute the method
		Yivic_Base_Helper_Test_Tmp_Setup_App_Not_Completed::wp_cli_prepare( [], [] );
		$this->assertTrue( in_array( 'prepare_wp_app_folders', static::$methods ) );
	}

	public function test_get_major_version_with_valid_version() {
		$version = '1.2.3';
		$expected_major_version = 1;

		$result = Yivic_Base_Helper::get_major_version( $version );

		$this->assertEquals( $expected_major_version, $result );
	}

	public function test_get_major_version_with_major_only_version() {
		$version = '2';
		$expected_major_version = 2;

		$result = Yivic_Base_Helper::get_major_version( $version );

		$this->assertEquals( $expected_major_version, $result );
	}

	public function test_get_major_version_with_extra_characters() {
		$version = 'v3.4.5-beta';
		$expected_major_version = 3;

		$result = Yivic_Base_Helper::get_major_version( $version );

		$this->assertEquals( $expected_major_version, $result );
	}

	public function test_get_major_version_with_non_numeric_major_version() {
		$version = 'v4.x.y';
		$expected_major_version = 4;

		$result = Yivic_Base_Helper::get_major_version( $version );

		$this->assertEquals( $expected_major_version, $result );
	}

	public function test_get_major_version_with_empty_version_string() {
		$version = '';
		$expected_major_version = 0;

		$result = Yivic_Base_Helper::get_major_version( $version );

		$this->assertEquals( $expected_major_version, $result );
	}

	public function test_get_major_version_with_no_major_version() {
		$version = '.2.3';
		$expected_major_version = 0;

		$result = Yivic_Base_Helper::get_major_version( $version );

		$this->assertEquals( $expected_major_version, $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_web_page_title_with_wp_title() {
		// Mock wp_title to return a title
		WP_Mock::userFunction(
			'wp_title',
			[
				'args'   => [ '', false ],
				'return' => 'Test Page Title',
			]
		);

		// Call the method
		$result = Yivic_Base_Helper::wp_app_web_page_title();

		// Assert that the title is correctly returned
		$this->assertEquals( 'Test Page Title', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_web_page_title_with_empty_wp_title() {
		// Mock wp_title to return an empty string
		WP_Mock::userFunction(
			'wp_title',
			[
				'args'   => [ '', false ],
				'return' => '',
			]
		);

		// Mock get_bloginfo to return the site name and description
		WP_Mock::userFunction(
			'get_bloginfo',
			[
				'args'   => 'name',
				'return' => 'My Blog',
			]
		);

		WP_Mock::userFunction(
			'get_bloginfo',
			[
				'args'   => 'description',
				'return' => 'Just another WordPress site',
			]
		);

		// Mock apply_filters to return the expected title
		WP_Mock::onFilter( App_Const::FILTER_WP_APP_WEB_PAGE_TITLE )
			->with( 'My Blog | Just another WordPress site' )
			->reply( 'My Blog | Just another WordPress site' );

		// Call the method
		$result = Yivic_Base_Helper::wp_app_web_page_title();

		// Assert that the title is correctly constructed
		$this->assertEquals( 'My Blog | Just another WordPress site', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_web_page_title_with_empty_wp_title_and_description() {
		// Mock wp_title to return an empty string
		WP_Mock::userFunction(
			'wp_title',
			[
				'args'   => [ '', false ],
				'return' => '',
			]
		);

		// Mock get_bloginfo to return the site name and an empty description
		WP_Mock::userFunction(
			'get_bloginfo',
			[
				'args'   => 'name',
				'return' => 'My Blog',
			]
		);

		WP_Mock::userFunction(
			'get_bloginfo',
			[
				'args'   => 'description',
				'return' => '',
			]
		);

		// Mock apply_filters to return the expected title
		WP_Mock::onFilter( App_Const::FILTER_WP_APP_WEB_PAGE_TITLE )
			->with( 'My Blog | WP App' )
			->reply( 'My Blog | WP App' );

		// Call the method
		$result = Yivic_Base_Helper::wp_app_web_page_title();

		// Assert that the title is correctly constructed
		$this->assertEquals( 'My Blog | WP App', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_asset_url_not_defined_constant_without_full_url() {
		if ( ! defined( 'YIVIC_BASE_WP_APP_ASSET_URL' ) ) {
			$expected_slug = '/wp-content/themes/my-theme/public';

			$this->assertEquals( $expected_slug, Yivic_Base_Helper_Test_Tmp_Get_Base_Path::wp_app_get_asset_url( false ) );
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_asset_url_not_defined_constant_with_full_url() {
		if ( ! defined( 'YIVIC_BASE_WP_APP_ASSET_URL' ) ) {

			// Mock the get_site_url function
			WP_Mock::userFunction(
				'get_site_url',
				[
					'return' => 'https://example.com',
				]
			);


			$expected_url = 'https://example.com/wp-content/themes/my-theme/public';


			$this->assertEquals( $expected_url, Yivic_Base_Helper_Test_Tmp_Get_Base_Path::wp_app_get_asset_url( true ) );
		}
	}

	public function test_wp_app_get_asset_url_defined_constant_full_url() {
		if ( ! defined( 'YIVIC_BASE_WP_APP_ASSET_URL' ) ) {
			define( 'YIVIC_BASE_WP_APP_ASSET_URL', 'http://example.com' );
		}

		// Call the method with full_url = false
		$result = Yivic_Base_Helper_Test_Tmp_True::wp_app_get_asset_url( true );

		// Assert that the method returns the correct slug path
		$this->assertEquals( 'http://example.com', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_is_wp_core_loaded_when_constant_defined() {
		// Ensure the constant WP_CONTENT_DIR is defined
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', '/path/to/wp-content' );
		}

		// Mock the get_site_url function
		WP_Mock::userFunction(
			'get_site_url',
			[
				'return' => 'https://example.com',
			]
		);

		// Call the method and check the result
		$result = Yivic_Base_Helper::is_wp_core_loaded();

		// Assert that the method returns true
		$this->assertTrue( $result );
	}

	public function test_is_console_mode_true_cli() {
		// Call the is_console_mode method statically
		$result = Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Cli::is_console_mode();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_is_console_mode_true_phpdbg() {
		// Call the is_console_mode method statically
		$result = Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Phpdbg::is_console_mode();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_is_console_mode_true_cli_server() {
		// Call the is_console_mode method statically
		$result = Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Cli_Server::is_console_mode();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_is_console_mode_false() {
		// Call the is_console_mode method statically
		$result = Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Apache::is_console_mode();

		// Assert that it returns true
		$this->assertFalse( $result );
	}

	public function test_get_php_sapi_name() {
		// Get the expected SAPI name directly using php_sapi_name
		$expected_sapi_name = php_sapi_name();

		// Call the method
		$result = Yivic_Base_Helper::get_php_sapi_name();

		// Assert that the method returns the correct SAPI name
		$this->assertEquals( $expected_sapi_name, $result );
	}

	public function test_use_yivic_base_error_handler() {
		// Mock the apply_filters function to ensure it processes the value
		WP_Mock::onFilter( 'yivic_base_use_error_handler' )
			->with( true )
			->reply( true );

		// Call the method
		$result = Yivic_Base_Helper_Test_Tmp_True::use_yivic_base_error_handler();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_get_use_error_handler_setting_returns_true_when_constant_not_defined() {
		// Define the constant if not already defined
		if ( ! defined( 'YIVIC_BASE_USE_ERROR_HANDLER' ) ) {
			$result = Yivic_Base_Helper::get_use_error_handler_setting();
			if ( getenv( 'YIVIC_BASE_USE_ERROR_HANDLER' ) !== false ) {
				$this->assertEquals( getenv( 'YIVIC_BASE_USE_ERROR_HANDLER' ), $result );
			} else {
				$this->assertFalse( $result );
			}
		}
	}

	public function test_get_use_error_handler_setting_returns_true_when_constant_defined() {
		// Define the constant if not already defined
		if ( ! defined( 'YIVIC_BASE_USE_ERROR_HANDLER' ) ) {
			define( 'YIVIC_BASE_USE_ERROR_HANDLER', true );
		}

		// Call the method and check the result
		$result = Yivic_Base_Helper::get_use_error_handler_setting();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_use_blade_for_wp_template() {
		// Mock the apply_filters function to ensure it processes the value
		WP_Mock::onFilter( 'yivic_base_use_blade_for_wp_template' )
			->with( true )
			->reply( true );

		// Call the method
		$result = Yivic_Base_Helper_Test_Tmp_True::use_blade_for_wp_template();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_get_blade_for_wp_template_setting_returns_true_when_constant_not_defined() {
		if ( ! defined( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE' ) ) {
			$result = Yivic_Base_Helper::get_blade_for_wp_template_setting();
			if ( getenv( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE' ) !== false ) {
				$this->assertEquals( getenv( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE' ), $result );
			} else {
				$this->assertFalse( $result );
			}
		}
	}

	public function test_get_blade_for_wp_template_setting_returns_true_when_constant_defined() {
		// Define the constant if not already defined
		if ( ! defined( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE' ) ) {
			define( 'YIVIC_BASE_USE_BLADE_FOR_WP_TEMPLATE', true );
		}

		// Call the method and check the result
		$result = Yivic_Base_Helper::get_blade_for_wp_template_setting();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_disable_web_worker() {
		// Mock the apply_filters function to ensure it processes the value
		WP_Mock::onFilter( 'yivic_base_disable_web_worker' )
			->with( true )
			->reply( true );

		// Call the method
		$result = Yivic_Base_Helper_Test_Tmp_True::disable_web_worker();

		// Assert that it returns true
		$this->assertTrue( $result );
	}

	public function test_get_disable_web_worker_status_when_constant_not_defined() {
		// Define the constant if not already defined
		if ( ! defined( 'YIVIC_BASE_DISABLE_WEB_WORKER' ) ) {
			$result = Yivic_Base_Helper::get_disable_web_worker_status();
			if ( getenv( 'YIVIC_BASE_DISABLE_WEB_WORKER' ) !== false ) {
				$this->assertEquals( getenv( 'YIVIC_BASE_DISABLE_WEB_WORKER' ), $result );
			} else {
				$this->assertFalse( $result );
			}
		}
	}

	public function test_get_disable_web_worker_status_when_constant_defined() {
		// Define the constant if not already defined
		if ( ! defined( 'YIVIC_BASE_DISABLE_WEB_WORKER' ) ) {
			define( 'YIVIC_BASE_DISABLE_WEB_WORKER', true );
		}

		// Act
		$result = Yivic_Base_Helper::get_disable_web_worker_status();

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_prepare_wp_app_folders() {
		$chmod = 0777;
		$wp_app_base_path = '/var/www/wp-app';

		// Mock WP_Filesystem to return true instead of failing
		\WP_Mock::userFunction(
			'WP_Filesystem',
			[
				'times'  => 1,
				'return' => true, // Ensure WP_Filesystem initializes properly
			]
		);

		$mock_wp_filesystem = $this->getMockBuilder( \stdClass::class )
			->addMethods( [ 'chmod', 'mkdir' ] )
			->getMock();

		// Expect chmod to be called exactly 3 times
		$mock_wp_filesystem->expects( $this->exactly( 3 ) )
			->method( 'chmod' )
			->withAnyParameters()
			->willReturn( true );

		// Expect mkdir to be called exactly 2 times
		$mock_wp_filesystem->expects( $this->exactly( 2 ) )
			->method( 'mkdir' )
			->withAnyParameters()
			->willReturn( true );

		// Assign the mock to the global $wp_filesystem variable
		global $wp_filesystem;
		$wp_filesystem = $mock_wp_filesystem;

		// Call the method
		Yivic_Base_Helper_Test_Tmp_Prepare_Wp_App::prepare_wp_app_folders( $chmod, $wp_app_base_path );

		// If no exceptions are thrown, the test is successful
		$this->assertTrue( true );
	}


	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_timezone_with_timezone_string() {
		// Arrange
		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'gmt_offset' ],
				'return' => 2,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'timezone_string' ],
				'return' => 'Europe/London',
			]
		);

		WP_Mock::userFunction(
			'wp_timezone',
			[
				'return' => new DateTimeZone( 'Europe/London' ),
			]
		);

		// Act
		$result = Yivic_Base_Helper::wp_app_get_timezone();

		// Assert
		$this->assertEquals( 'Europe/London', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_timezone_with_etc_gmt_offset() {
		// Arrange
		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'gmt_offset' ],
				'return' => -5,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'timezone_string' ],
				'return' => '',
			]
		);

		// Act
		$result = Yivic_Base_Helper::wp_app_get_timezone();

		// Assert
		$this->assertEquals( 'Etc/GMT+5', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_timezone_removes_old_etc_gmt_mapping() {
		// Arrange
		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'gmt_offset' ],
				'return' => -5,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'timezone_string' ],
				'return' => 'Etc/GMT-5',
			]
		);

		// Act
		$result = Yivic_Base_Helper::wp_app_get_timezone();

		// Assert
		$this->assertEquals( 'Etc/GMT+5', $result ); // Should fallback to gmt_offset and return 'Etc/GMT+5'
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_timezone_with_default_gmt() {
		// Arrange
		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'gmt_offset' ],
				'return' => 0,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'timezone_string' ],
				'return' => '',
			]
		);

		// Act
		$result = Yivic_Base_Helper::wp_app_get_timezone();

		// Assert
		$this->assertEquals( 'Etc/GMT', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_timezone_with_positive_gmt_offset() {
		// Arrange
		$gmt_offset = 3;

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'gmt_offset' ],
				'return' => $gmt_offset,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'timezone_string' ],
				'return' => '',
			]
		);

		// Act
		$result = Yivic_Base_Helper::wp_app_get_timezone();

		// Assert
		$this->assertEquals( 'Etc/GMT-3', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_wp_app_get_timezone_with_wp_app_timezone_defined() {
		// Arrange
		define( 'WP_APP_TIMEZONE', 'America/New_York' );

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'gmt_offset' ],
				'return' => -5,
			]
		);

		WP_Mock::userFunction(
			'get_option',
			[
				'args' => [ 'timezone_string' ],
				'return' => '',
			]
		);

		// Act
		$result = Yivic_Base_Helper::wp_app_get_timezone();

		// Assert
		$this->assertEquals( 'America/New_York', $result );
	}

	public function test_perform_wp_app_check_pdo_mysql_extension_not_loaded() {
		WP_Mock::userFunction( 'get_option' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( true );

		// Act
		Yivic_Base_Helper_Test_Tmp_False::perform_wp_app_check();

		// Assert
		$this->assertTrue( in_array( 'perform_wp_app_check_add_wp_app_setup_errors', static::$methods ) );
	}

	public function test_perform_wp_app_is_setup_app_completed_no_error() {
		// Act
		$result = Yivic_Base_Helper_Test_Tmp_Setup_App_Completed_No_Error::perform_wp_app_check();

		// Assert
		$this->assertTrue( $result );
		$this->assertEquals( true, Yivic_Base_Helper_Test_Tmp_Setup_App_Completed_No_Error::$wp_app_check );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_perform_wp_app_at_setup_app_url() {

		$expected_site_url = 'https://example.com';

		// Mock the site_url() function to return the expected site URL
		WP_Mock::userFunction(
			'site_url',
			[
				'return' => $expected_site_url,
			]
		);

		// Act
		Yivic_Base_Helper_Test_Tmp_Setup_App_Url::perform_wp_app_check();

		// Assert
		$this->assertTrue( in_array( 'perform_wp_app_check_add_wp_app_setup_errors_setup_app_url', static::$methods ) );
	}

	public function test_perform_wp_app_return_true() {
		$GLOBALS['wp_app_setup_errors'] = [];

		WP_Mock::onFilter( App_Const::FILTER_WP_APP_CHECK )
			->with( true )
			->reply( true );

		// Act
		$result = Yivic_Base_Helper_Test_Tmp_Perform_Wp_App_True::perform_wp_app_check();

		// Assert
		$this->assertTrue( $result );
		$this->assertTrue( Yivic_Base_Helper_Test_Tmp_Perform_Wp_App_True::$wp_app_check );
	}

	public function test_is_pdo_mysql_loaded() {
		if ( extension_loaded( 'pdo_mysql' ) ) {
			$this->assertTrue( Yivic_Base_Helper::is_pdo_mysql_loaded() );
		} else {
			$this->assertFalse( Yivic_Base_Helper::is_pdo_mysql_loaded() );
		}
	}

	public function test_register_cli_init_action() {
		// Arrange: Mock the add_action function to check it's called with the right parameters
		WP_Mock::expectActionAdded( 'cli_init', [ Yivic_Base_Helper::class, 'wp_cli_init' ] );

		// Act: Call the method we're testing
		Yivic_Base_Helper::register_cli_init_action();

		// Assert: WP_Mock automatically asserts that add_action was called with the correct parameters
		WP_Mock::assertHooksAdded();
	}

	public function test_register_setup_app_redirect() {
		// Arrange: Mock the add_action function to check it's called with the right parameters
		WP_Mock::expectActionAdded(
			YIVIC_BASE_SETUP_HOOK_NAME, // The hook name
			[ Yivic_Base_Helper::class, 'maybe_redirect_to_setup_app' ], // The callback
			-200 // The priority
		);

		// Act: Call the method we're testing
		Yivic_Base_Helper::register_setup_app_redirect();

		// Assert: WP_Mock automatically asserts that add_action was called with the correct parameters
		WP_Mock::assertHooksAdded();
	}

	public function test_is_yivic_base_prepare_command() {
		global $_SERVER;
		// Arrange: Simulate command line arguments
		$_SERVER['argv'] = [ 'yivic-base', 'prepare' ];

		// Act
		$result = Yivic_Base_Helper::is_yivic_base_prepare_command( $_SERVER['argv'] );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_init_wp_app_instance() {
		// Arrange: Expect that add_action is called with specific parameters.
		WP_Mock::expectActionAdded(
			YIVIC_BASE_SETUP_HOOK_NAME, // The hook name
			[ \Yivic_Base\App\WP\WP_Application::class, 'load_instance' ], // The callback
			-100 // The priority
		);

		// Act: Call the method to register the hook
		Yivic_Base_Helper::init_wp_app_instance();

		// Assert: WP_Mock automatically asserts that add_action was called with the correct parameters
		WP_Mock::assertHooksAdded();
	}

	public function test_init_yivic_base_wp_plugin_instance() {
		$action_name = App_Const::ACTION_WP_APP_LOADED;

		// Mock add_action to ensure it is called with 'admin_notices'
		WP_Mock::expectActionAdded(
			$action_name,
			function ( $callback ) {
				// Ensure that the callback is of type Closure
				return true;
			}
		);

		// Execute the method under test
		Yivic_Base_Helper::init_yivic_base_wp_plugin_instance( $this->plugin_url, $this->dirname );

		// Verify that the action was added exactly once
		WP_Mock::assertHooksAdded();
	}

	public function test_handle_wp_app_loaded_action() {
		// Mock the method that should be called within the static method
		$plugin_mock = Mockery::mock( 'alias:' . \Yivic_Base\App\WP\Yivic_Base_WP_Plugin::class );
		$plugin_mock->shouldReceive( 'init_with_wp_app' )
			->once()
			->with(
				'yivic-base',
				$this->dirname, // __DIR__ should be a string
				$this->plugin_url // The mocked return value of plugin_dir_url
			);

		// Act: Directly call the static method that handles the action
		Yivic_Base_Helper::handle_wp_app_loaded_action( $this->plugin_url, $this->dirname );

		$this->assertTrue( true );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_route_with_wp_url() {
		// Arrange: Define the expected site URL and route values
		$expected_site_url = 'https://example.com';
		$expected_route = '/my-route';

		// Mock the site_url() function to return the expected site URL
		WP_Mock::userFunction(
			'site_url',
			[
				'return' => $expected_site_url,
			]
		);

		// Mock the route() function to return the expected route path
		WP_Mock::userFunction(
			'route',
			[
				'args' => [ 'test-route', [], false ],
				'return' => $expected_route,
			]
		);

		// Act: Call the method you are testing
		$result = Yivic_Base_Helper::route_with_wp_url( 'test-route' );

		// Assert: Check that the result matches the expected full URL
		$this->assertEquals( 'https://example.com/my-route', $result );
	}
}


namespace Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Tests\Unit\App\Support\Yivic_Base_Helper_Test;

class Yivic_Base_Helper_Test_Tmp_True extends Yivic_Base_Helper {

	public static $wp_app_check = true;

	public static function add_wp_app_setup_errors( $error_message ): void {
		Yivic_Base_Helper_Test::$methods[] = 'add_wp_app_setup_errors';
	}

	public static function is_setup_app_completed() {
		return true;
	}

	public static function get_use_error_handler_setting(): bool {
		return true;
	}

	public static function get_blade_for_wp_template_setting(): bool {
		return true;
	}

	public static function get_disable_web_worker_status(): bool {
		return true;
	}

	public static function get_wp_app_base_path() {
		return 'wp-app/test';
	}
}

class Yivic_Base_Helper_Test_Tmp_Get_Base_Path extends Yivic_Base_Helper {
	public static function get_wp_app_base_path() {
		return 'wp-content/themes/my-theme';
	}
}

class Yivic_Base_Helper_Test_Tmp_False extends Yivic_Base_Helper {
	public static $wp_app_check = null;

	public static function is_setup_app_completed() {
		return false;
	}

	public static function is_pdo_mysql_loaded(): bool {
		return false;
	}

	public static function add_wp_app_setup_errors( $error_message ): void {
		Yivic_Base_Helper_Test::$methods[] = 'perform_wp_app_check_add_wp_app_setup_errors';
	}
}

class Yivic_Base_Helper_Test_Tmp_Setup_App_Not_Completed extends Yivic_Base_Helper {
	public static function is_setup_app_completed() {
		return false;
	}

	public static function prepare_wp_app_folders( $chmod = 0777, string $wp_app_base_path = '' ): void {
		Yivic_Base_Helper_Test::$methods[] = 'prepare_wp_app_folders';
	}

	public static function is_setup_app_failed() {
		return false;
	}

	public static function redirect_to_setup_url(): void {
		Yivic_Base_Helper_Test::$methods[] = 'redirect_to_setup_url';
	}
}

class Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Cli extends Yivic_Base_Helper {
	public static function get_php_sapi_name(): string {
		return 'cli';
	}
}

class Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Phpdbg extends Yivic_Base_Helper {
	public static function get_php_sapi_name(): string {
		return 'phpdbg';
	}
}

class Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Cli_Server extends Yivic_Base_Helper {
	public static function get_php_sapi_name(): string {
		return 'cli-server';
	}
}

class Yivic_Base_Helper_Test_Tmp_Is_Console_Mode_Apache extends Yivic_Base_Helper {
	public static function get_php_sapi_name(): string {
		return 'apache2handler';
	}
}

class Yivic_Base_Helper_Test_Tmp_Prepare_Wp_App extends Yivic_Base_Helper {
	public static function get_wp_app_base_folders_paths( $wp_app_base_path ) {
		return [
			$wp_app_base_path . '/folder1',
			$wp_app_base_path . '/folder2',
		];
	}
}

class Yivic_Base_Helper_Test_Tmp_Setup_App_Completed_No_Error extends Yivic_Base_Helper {
	public static $wp_app_check = null;

	public static function is_pdo_mysql_loaded(): bool {
		return true;
	}

	public static function get_wp_app_setup_errors(): array {
		return [];
	}

	public static function is_setup_app_completed(): bool {
		return true;
	}
}

class Yivic_Base_Helper_Test_Tmp_Setup_App_Url extends Yivic_Base_Helper {
	public static $wp_app_check = null;

	public static function is_pdo_mysql_loaded(): bool {
		return true;
	}

	public static function is_setup_app_completed(): bool {
		return false;
	}

	public static function at_setup_app_url(): bool {
		return false;
	}

	public static function at_admin_setup_app_url(): bool {
		return false;
	}

	public static function is_setup_app_failed(): bool {
		return true;
	}

	public static function add_wp_app_setup_errors( $error_message ): void {
		Yivic_Base_Helper_Test::$methods[] = 'perform_wp_app_check_add_wp_app_setup_errors_setup_app_url';
	}
}

class Yivic_Base_Helper_Test_Tmp_Perform_Wp_App_True extends Yivic_Base_Helper {
	public static $wp_app_check = null;

	public static function is_pdo_mysql_loaded(): bool {
		return true;
	}

	public static function at_setup_app_url(): bool {
		return true;
	}

	public static function is_setup_app_completed(): bool {
		return false;
	}
}

class Yivic_Base_Helper_Test_Tmp_Initialize extends Yivic_Base_Helper {

	public static function register_cli_init_action() {
		Yivic_Base_Helper_Test::$methods[] = 'register_cli_init_action';
	}

	public static function register_setup_app_redirect() {
		Yivic_Base_Helper_Test::$methods[] = 'register_setup_app_redirect';
	}

	public static function init_wp_app_instance() {
		Yivic_Base_Helper_Test::$methods[] = 'init_wp_app_instance';
	}

	public static function init_yivic_base_wp_plugin_instance( $plugin_url, $dirname ) {
		Yivic_Base_Helper_Test::$methods[] = 'init_yivic_base_wp_plugin_instance';
	}

	public static function is_yivic_base_prepare_command( array $argv = null ): bool {
		return true;
	}

	public static function prepare_wp_app_folders( $chmod = 0777, string $wp_app_base_path = '' ): void {
		Yivic_Base_Helper_Test::$methods[] = 'prepare_wp_app_folders';
	}
}

class Yivic_Base_Helper_Test_Tmp_Initialize_Wp_Core_Loaded_False extends Yivic_Base_Helper {

	public static function is_wp_core_loaded(): bool {
		return false;
	}

	public static function init_wp_app_instance(): void {
		Yivic_Base_Helper_Test::$methods[] = 'init_wp_app_instance';
	}
}

class Yivic_Base_Helper_Test_Tmp_Initialize_In_Console_Mode_True extends Yivic_Base_Helper {

	public static function is_wp_core_loaded(): bool {
		return true;
	}

	public static function is_console_mode(): bool {
		return true;
	}

	public static function perform_wp_app_check(): bool {
		return false;
	}

	public static function is_yivic_base_prepare_command( array $argv = null ): bool {
		return false;
	}

	public static function register_cli_init_action() {
		Yivic_Base_Helper_Test::$methods[] = 'register_cli_init_action';
	}

	public static function init_wp_app_instance() {
		Yivic_Base_Helper_Test::$methods[] = 'init_wp_app_instance';
	}

	public static function init_yivic_base_wp_plugin_instance( string $plugin_url, string $dirname ) {
		Yivic_Base_Helper_Test::$methods[] = 'init_yivic_base_wp_plugin_instance';
	}
}

class Yivic_Base_Helper_Test_Tmp_Initialize_Is_Yivic_Base_Prepare_Command extends Yivic_Base_Helper {

	public static function is_wp_core_loaded(): bool {
		return true;
	}

	public static function is_console_mode(): bool {
		return true;
	}

	public static function perform_wp_app_check(): bool {
		return false;
	}

	public static function is_yivic_base_prepare_command( array $argv = null ): bool {
		return true;
	}

	public static function prepare_wp_app_folders( $chmod = 0777, string $wp_app_base_path = '' ): void {
		Yivic_Base_Helper_Test::$methods[] = 'prepare_wp_app_folders';
	}

	public static function register_setup_app_redirect() {
		Yivic_Base_Helper_Test::$methods[] = 'register_setup_app_redirect';
	}

	public static function register_cli_init_action() {
		Yivic_Base_Helper_Test::$methods[] = 'register_cli_init_action';
	}

	public static function init_wp_app_instance() {
		Yivic_Base_Helper_Test::$methods[] = 'init_wp_app_instance';
	}

	public static function init_yivic_base_wp_plugin_instance( string $plugin_url, string $dirname ) {
		Yivic_Base_Helper_Test::$methods[] = 'init_yivic_base_wp_plugin_instance';
	}
}
class Yivic_Base_Helper_Test_Tmp_Initialize_Is_Perform_WP_App_Check extends Yivic_Base_Helper {

	public static function is_wp_core_loaded(): bool {
		return true;
	}

	public static function is_console_mode(): bool {
		return false;
	}

	public static function perform_wp_app_check(): bool {
		return false;
	}

	public static function register_cli_init_action() {
		Yivic_Base_Helper_Test::$methods[] = 'register_cli_init_action';
	}

	public static function register_setup_app_redirect() {
		Yivic_Base_Helper_Test::$methods[] = 'register_setup_app_redirect';
	}
}

class Yivic_Base_Helper_Test_Tmp_Initialize_In_Console_Mode_False extends Yivic_Base_Helper {
	public static function is_wp_core_loaded(): bool {
		return true;
	}

	public static function is_console_mode(): bool {
		return false;
	}

	public static function perform_wp_app_check(): bool {
		return true;
	}

	public static function register_cli_init_action() {
		Yivic_Base_Helper_Test::$methods[] = 'register_cli_init_action';
	}

	public static function register_setup_app_redirect() {
		Yivic_Base_Helper_Test::$methods[] = 'register_setup_app_redirect';
	}

	public static function init_wp_app_instance() {
		Yivic_Base_Helper_Test::$methods[] = 'init_wp_app_instance';
	}
}