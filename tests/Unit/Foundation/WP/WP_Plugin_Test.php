<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\WP;

use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Foundation\WP\WP_Plugin;
use Yivic_Base\Foundation\WP\WP_Plugin_Interface;
use Yivic_Base\Tests\Support\Helpers\Test_Utils_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Mockery;
use WP_Mock;

class WP_Plugin_Test extends Unit_Test_Case {
	use Test_Utils_Trait;

	/** @var WP_Plugin_Tmp $mock_wp_plugin */
	protected $mock_wp_plugin;

	protected function setUp(): void {
		parent::setUp();

		$this->mock_wp_plugin = $this->build_wp_plugin_mock();
	}

	protected function tearDown(): void {
		$this->mock_wp_plugin = null;

		parent::tearDown();
	}

	protected function build_wp_plugin_mock( array $mocked_methods = [] ) {
		$mock_wp_app = $this->getMockBuilder( WP_Application::class );
		return $this->getMockForAbstractClass(
			WP_Plugin_Tmp::class,
			[ $mock_wp_app ],
			'',
			false,
			true,
			true,
			$mocked_methods
		);
	}

	public function test_wp_app_instance(): void {
		$mock_wp_plugin = $this->mock_wp_plugin;
		WP_Mock::userFunction( 'app' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( $mock_wp_plugin );
		$result = $mock_wp_plugin->wp_app_instance();
		$this->assertEquals( $mock_wp_plugin, $result );
	}

	public function test_init_with_wp_app() {
		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'init_with_needed_params',
				'attach_to_wp_app',
				'manipulate_hooks',
			]
		);
		// Mock the new instance creation
		$mock_class_name = get_class( $mock_wp_plugin );

		$slug = 'plugin-slug';
		$base_path = '/path/to/plugin';
		$base_url = 'https://example.com';

		WP_Mock::userFunction( 'app' )
			->times( 2 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $abstract = null ) use ( $mock_wp_plugin ) {
					if ( $abstract ) {
							return $mock_wp_plugin;
					} else {
						return new WP_App_Tmp_Has_True_WP_Plugin();
					}
				}
			);
		$result = $mock_class_name::init_with_wp_app( $slug, $base_path, $base_url );
		$this->assertEquals( $mock_wp_plugin, $result );

		// Stub the wp_app function
		WP_Mock::userFunction( 'app' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $abstract = null ) use ( $mock_wp_plugin ) {
					if ( $abstract ) {
							return $mock_wp_plugin;
					} else {
						return new WP_App_Tmp_Has_False_WP_Plugin();
					}
				}
			);

		$slug = 'plugin-slug';
		$base_path = '/path/to/plugin';
		$base_url = 'https://example.com';

		// Expected method calls
		$mock_wp_plugin->expects( $this->any() )->method( 'init_with_needed_params' );
		$mock_wp_plugin->expects( $this->any() )->method( 'attach_to_wp_app' );
		$mock_wp_plugin->expects( $this->any() )->method( 'manipulate_hooks' );

		$result = $mock_class_name::init_with_wp_app( $slug, $base_path, $base_url );
		// Assertions
		$this->assertInstanceOf( WP_Plugin_Interface::class, $result );
	}

	public function test_init_with_needed_params() {
		$mock_wp_plugin = $this->mock_wp_plugin;

		$slug = 'plugin-slug';
		$base_path = '/path/to/plugin';
		$base_url = 'https://example.com';

		$mock_wp_plugin->call_init_with_needed_params( $slug, $base_path, $base_url );

		$this->assertEquals( $slug, $mock_wp_plugin->get_plugin_slug() );
		$this->assertEquals( $base_path, $mock_wp_plugin->get_base_path() );
		$this->assertEquals( $base_url, $mock_wp_plugin->get_base_url() );
	}

	public function test_attach_to_wp_app() {
		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_plugin_slug',
			]
		);

		WP_Mock::userFunction( 'app' )
			->times( 2 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $abstract = null ) use ( $mock_wp_plugin ) {
					if ( $abstract ) {
							return $mock_wp_plugin;
					} else {
						return new WP_App_Tmp_Has_True_WP_Plugin();
					}
				}
			);
		$mock_class_name = get_class( $mock_wp_plugin );

		// Expected method calls
		$mock_wp_plugin->expects( $this->once() )->method( 'get_plugin_slug' )->willReturn( 'slug-tmp' );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->call_attach_to_wp_app();

		$this->assertEquals( $mock_wp_plugin, WP_App_Tmp_Has_True_WP_Plugin::$instance[ $mock_class_name ] );
		$this->assertEquals( 'plugin-slug-tmp', WP_App_Tmp_Has_True_WP_Plugin::$alias[ $mock_class_name ] );
	}

	public function test_validate_needed_properties_missing() {
		$mock_wp_plugin = $this->mock_wp_plugin;
		$this->expectException( InvalidArgumentException::class );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->call_validate_needed_properties();
	}

	public function test_validate_needed_properties_invalid_values() {
		$mock_wp_plugin = $this->mock_wp_plugin;
		$mock_wp_plugin->bind_base_params(
			[
				WP_Plugin_Interface::PARAM_KEY_PLUGIN_SLUG => 'plugin-slug#$',
				WP_Plugin_Interface::PARAM_KEY_PLUGIN_BASE_PATH => '/path/to/plugin',
				WP_Plugin_Interface::PARAM_KEY_PLUGIN_BASE_URL => 'https://example.com',
			]
		);

		$this->expectException( InvalidArgumentException::class );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->call_validate_needed_properties();
	}

	public function test_prepare_views_paths_theme_folder() {
		$output_tmp_folder_path = get_output_tmp_folder_path();
		$namespace = 'tatata';

		$theme_folder_path = $output_tmp_folder_path . DIR_SEP . 'theme-folder' . DIR_SEP . 'resources' . DIR_SEP . 'views'
		. DIR_SEP . '_plugins' . DIR_SEP . $namespace;
		$filesystem = new Filesystem();
		$filesystem->ensureDirectoryExists( $theme_folder_path, 0777 );

		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_base_path',
				'loadViewsFrom',
			]
		);

		/*** We want to assert the theme-folder views is loaded */
		WP_Mock::userFunction( 'get_stylesheet_directory' )
			->times()
			->withAnyArgs()
			->andReturnUsing(
				function () use ( $output_tmp_folder_path ) {
					return $output_tmp_folder_path . DIRECTORY_SEPARATOR . 'theme-folder';
				}
			);

		WP_Mock::userFunction( 'get_template_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () use ( $output_tmp_folder_path ) {
					return $output_tmp_folder_path . DIRECTORY_SEPARATOR . 'theme-folder';
				}
			);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 2 ) )->method( 'loadViewsFrom' )->willReturn( null );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->call_prepare_views_paths( $namespace );

		$filesystem->cleanDirectory( $output_tmp_folder_path );
	}

	public function test_prepare_views_paths_parent_theme_folder() {
		$output_tmp_folder_path = get_output_tmp_folder_path();
		$namespace = 'tatata';

		$theme_folder_path = $output_tmp_folder_path . DIR_SEP . 'theme-folder' . DIR_SEP . 'resources' . DIR_SEP . 'views'
		. DIR_SEP . '_plugins' . DIR_SEP . $namespace;
		$filesystem = new Filesystem();
		$filesystem->ensureDirectoryExists( $theme_folder_path, 0777 );

		$parent_theme_folder_path = $output_tmp_folder_path . DIR_SEP . 'parent-theme-folder' . DIR_SEP . 'resources' . DIR_SEP . 'views'
		. DIR_SEP . '_plugins' . DIR_SEP . $namespace;
		$filesystem = new Filesystem();
		$filesystem->ensureDirectoryExists( $parent_theme_folder_path, 0777 );

		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_base_path',
				'loadViewsFrom',
			]
		);

		/*** We want to assert the theme-folder views is loaded */
		WP_Mock::userFunction( 'get_stylesheet_directory' )
			->times()
			->withAnyArgs()
			->andReturnUsing(
				function () use ( $output_tmp_folder_path ) {
					return $output_tmp_folder_path . DIR_SEP . 'theme-folder';
				}
			);

		WP_Mock::userFunction( 'get_template_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () use ( $output_tmp_folder_path ) {
					return $output_tmp_folder_path . DIR_SEP . 'parent-theme-folder';
				}
			);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 3 ) )->method( 'loadViewsFrom' )->willReturn( null );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->call_prepare_views_paths( $namespace );

		$filesystem->cleanDirectory( $output_tmp_folder_path );
	}

	public function test_prepare_views_paths_no_theme() {
		$output_tmp_folder_path = get_output_tmp_folder_path();
		$namespace = 'tatata';

		$filesystem = new Filesystem();
		$filesystem->cleanDirectory( $output_tmp_folder_path );

		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_base_path',
				'loadViewsFrom',
			]
		);

		/*** We want to assert the theme-folder views is loaded */
		WP_Mock::userFunction( 'get_stylesheet_directory' )
			->times()
			->withAnyArgs()
			->andReturnUsing(
				function () use ( $output_tmp_folder_path ) {
					return $output_tmp_folder_path . DIR_SEP . 'theme-folder';
				}
			);

		WP_Mock::userFunction( 'get_template_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () use ( $output_tmp_folder_path ) {
					return $output_tmp_folder_path . DIR_SEP . 'parent-theme-folder';
				}
			);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 1 ) )->method( 'loadViewsFrom' )->willReturn( null );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->call_prepare_views_paths( $namespace );
	}

	public function test_boot() {
		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'prepare_views_paths',
				'get_plugin_slug',
			]
		);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 1 ) )->method( 'prepare_views_paths' );
		$mock_wp_plugin->expects( $this->exactly( 1 ) )->method( 'get_plugin_slug' )->willReturn( 'slug' );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->boot();
	}

	public function test_get_views_path() {
		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_base_path',
			]
		);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 1 ) )->method( 'get_base_path' )->willReturn( '/path01' );

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$this->assertEquals( '/path01' . DIR_SEP . 'resources' . DIR_SEP . 'views', $mock_wp_plugin->get_views_path() );
	}

	public function test_view() {
		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_plugin_slug',
			]
		);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 2 ) )->method( 'get_plugin_slug' )->willReturn( 'namespace' );

		WP_Mock::userFunction( 'view' )
			->times( 2 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $view, $data = [], $merge_data = [] ) {
					return [
						'view' => $view,
						'data' => $data,
						'merge_data' => $merge_data,
					];
				}
			);

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$this->assertEquals(
			[
				'view' => 'namespace' . '::' . 'view',
				'data' => [],
				'merge_data' => [],
			],
			$mock_wp_plugin->view( 'view' )
		);

		$this->assertEquals(
			[
				'view' => 'namespace' . '::' . 'view2',
				'data' => [ 1, 2, 3 ],
				'merge_data' => [ 4, 5, 6 ],
			],
			$mock_wp_plugin->view( 'view2', [ 1, 2, 3 ], [ 4, 5, 6 ] )
		);
	}

	public function test_get_plugin_basename() {
		$mock_wp_plugin = $this->build_wp_plugin_mock(
			[
				'get_base_path',
				'get_plugin_slug',
			]
		);

		// Expected method calls
		$mock_wp_plugin->expects( $this->exactly( 1 ) )->method( 'get_plugin_slug' )->willReturn( 'namespace' );
		$mock_wp_plugin->expects( $this->exactly( 1 ) )->method( 'get_base_path' )->willReturn( '/path' );

		WP_Mock::userFunction( 'plugin_basename' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$this->assertEquals( '/path' . DIR_SEP . 'namespace' . '.php', $mock_wp_plugin->get_plugin_basename() );
	}

	public function test_register_this_to_wp_app() {
		$mock_wp_plugin = $this->build_wp_plugin_mock();
		$wp_app = new WP_App_Tmp_Has_True_WP_Plugin();
		WP_App_Tmp_Has_True_WP_Plugin::$registered = [];

		/** @var WP_Plugin_Tmp $mock_wp_plugin */
		$mock_wp_plugin->register_this_to_wp_app( $wp_app );

		$this->assertTrue( in_array( $mock_wp_plugin, WP_App_Tmp_Has_True_WP_Plugin::$registered ) );
	}
}

class WP_Plugin_Tmp extends WP_Plugin {
	public function manipulate_hooks(): void {
	}

	public function get_name(): string {
		return 'tmp';
	}

	public function get_version(): string {
		return '1.0.1';
	}

	public function call_init_with_needed_params( string $slug, string $base_path, string $base_url ) {
		return $this->init_with_needed_params( $slug, $base_path, $base_url );
	}

	public function call_attach_to_wp_app() {
		return $this->attach_to_wp_app();
	}

	public function call_validate_needed_properties() {
		return $this->validate_needed_properties();
	}

	public function call_prepare_views_paths( $namespace ) {
		return $this->prepare_views_paths( $namespace );
	}
}

class WP_App_Tmp_Has_False_WP_Plugin {
	public function has( $abstract ) {
		return false;
	}
}

class WP_App_Tmp_Has_True_WP_Plugin {
	public static $instance;
	public static $alias;
	public static $registered = [];

	public function has( $abstract ) {
		return true;
	}

	public function instance( $key, $value ) {
		static::$instance[ $key ] = $value;
	}

	public function alias( $key, $value ) {
		static::$alias[ $key ] = $value;
	}

	public function register( $plugin ) {
		static::$registered[] = $plugin;
	}
}
