<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\WP;

use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Foundation\WP\WP_Theme;
use Yivic_Base\Foundation\WP\WP_Theme_Interface;
use Yivic_Base\Tests\Support\Helpers\Test_Utils_Trait;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use InvalidArgumentException;
use WP_Mock;

class WP_Theme_Test extends Unit_Test_Case {
	use Test_Utils_Trait;

	/** @var WP_Theme_Tmp $mock_wp_theme */
	protected $mock_wp_theme;

	protected function setUp(): void {
		parent::setUp();

		$this->mock_wp_theme = $this->build_mock_wp_theme();
	}

	protected function tearDown(): void {
		$this->mock_wp_theme = null;

		parent::tearDown();
	}

	protected function build_mock_wp_theme( array $mocked_methods = [] ) {
		$mock_wp_app = $this->getMockBuilder( WP_Application::class );
		return $this->getMockForAbstractClass(
			WP_Theme_Tmp::class,
			[ $mock_wp_app ],
			'',
			false,
			true,
			true,
			$mocked_methods
		);
	}

	public function test_wp_app_instance(): void {
		$mock_wp_theme = $this->mock_wp_theme;
		WP_Mock::userFunction( 'app' )
			->times( 1 )
			->withAnyArgs()
			->andReturn( $mock_wp_theme );
		$result = $mock_wp_theme->wp_app_instance();

		$this->assertEquals( $mock_wp_theme, $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_init_with_wp_app() {
		$mock_wp_theme = $this->build_mock_wp_theme(
			[
				'init_with_needed_params',
				'attach_to_wp_app',
				'manipulate_hooks',
			]
		);
		// Mock the new instance creation
		$mock_class_name = get_class( $mock_wp_theme );

		$slug = 'theme-slug';

		WP_Mock::userFunction( 'app' )
			->times( 4 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $abstract = null ) use ( $mock_wp_theme ) {
					if ( $abstract ) {
							return $mock_wp_theme;
					} else {
						return new WP_App_Tmp_Has_True_WP_Theme();
					}
				}
			);
		$result = $mock_class_name::init_with_wp_app( $slug );
		$this->assertEquals( $mock_wp_theme, $result );

		$slug = 'theme-slug';

		// Expected method calls
		$mock_wp_theme->expects( $this->any() )->method( 'init_with_needed_params' );
		$mock_wp_theme->expects( $this->any() )->method( 'attach_to_wp_app' );
		$mock_wp_theme->expects( $this->any() )->method( 'manipulate_hooks' );

		$result = $mock_class_name::init_with_wp_app( $slug );
		// Assertions
		$this->assertInstanceOf( WP_Theme_Interface::class, $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_init_with_needed_params_with_parent() {
		$mock_wp_theme = $this->build_mock_wp_theme();

		$slug = 'theme-slug';

		WP_Mock::userFunction( 'get_stylesheet_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'theme-folder';
				}
			);
		WP_Mock::userFunction( 'get_stylesheet_directory_uri' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return '/themes/theme-uri';
				}
			);

		WP_Mock::userFunction( 'get_template_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'parent-theme-folder';
				}
			);
		WP_Mock::userFunction( 'get_template_directory_uri' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return '/themes/parent-theme-uri';
				}
			);

		$mock_wp_theme->call_init_with_needed_params( $slug );

		$this->assertEquals( $slug, $mock_wp_theme->get_theme_slug() );
		$this->assertEquals( 'theme-folder', $mock_wp_theme->get_base_path() );
		$this->assertEquals( '/themes/theme-uri', $mock_wp_theme->get_base_url() );
		$this->assertEquals( 'parent-theme-folder', $mock_wp_theme->get_parent_base_path() );
		$this->assertEquals( '/themes/parent-theme-uri', $mock_wp_theme->get_parent_base_url() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_init_with_needed_params_with_no_parent_and_failed() {
		$mock_wp_theme = $this->build_mock_wp_theme();

		$slug = 'theme-slug$%#%';

		WP_Mock::userFunction( 'get_stylesheet_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'theme-folder';
				}
			);
		WP_Mock::userFunction( 'get_stylesheet_directory_uri' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return '/themes/theme-uri';
				}
			);

		WP_Mock::userFunction( 'get_template_directory' )
			->times( 1 )
			->withAnyArgs()
			->andReturnUsing(
				function () {
					return 'theme-folder';
				}
			);
		WP_Mock::userFunction( 'get_template_directory_uri' )
			->times( 0 )
			->withAnyArgs();

		$this->expectException( InvalidArgumentException::class );

		$mock_wp_theme->call_init_with_needed_params( $slug );

		$this->assertEquals( $slug, $mock_wp_theme->get_theme_slug() );
		$this->assertEquals( 'theme-folder', $mock_wp_theme->get_base_path() );
		$this->assertEquals( '/themes/theme-uri', $mock_wp_theme->get_base_url() );
		$this->assertEmpty( $mock_wp_theme->get_parent_base_path() );
		$this->assertEmpty( $mock_wp_theme->get_parent_base_url() );
	}

	public function test_attach_to_wp_app() {
		$mock_wp_theme = $this->build_mock_wp_theme(
			[
				'get_theme_slug',
			]
		);

		WP_Mock::userFunction( 'app' )
			->times( 2 )
			->withAnyArgs()
			->andReturnUsing(
				function ( $abstract = null ) use ( $mock_wp_theme ) {
					if ( $abstract ) {
							return $mock_wp_theme;
					} else {
						return new WP_App_Tmp_Has_True_WP_Theme();
					}
				}
			);
		$mock_class_name = get_class( $mock_wp_theme );

		// Expected method calls
		$mock_wp_theme->expects( $this->once() )->method( 'get_theme_slug' )->willReturn( 'slug-tmp' );

		/** @var WP_Plugin_Tmp $mock_wp_theme */
		$mock_wp_theme->call_attach_to_wp_app();

		$this->assertEquals( $mock_wp_theme, WP_App_Tmp_Has_True_WP_Theme::$instance[ $mock_class_name ] );
		$this->assertEquals( 'theme-slug-tmp', WP_App_Tmp_Has_True_WP_Theme::$alias[ $mock_class_name ] );
	}

	public function test_register_this_to_wp_app() {
		$mock_wp_theme = $this->build_mock_wp_theme();
		$wp_app = new WP_App_Tmp_Has_True_WP_Theme();
		WP_App_Tmp_Has_True_WP_Theme::$registered = [];

		/** @var WP_Plugin_Tmp $mock_wp_theme */
		$mock_wp_theme->register_this_to_wp_app( $wp_app );

		$this->assertTrue( in_array( $mock_wp_theme, WP_App_Tmp_Has_True_WP_Theme::$registered ) );
	}

	public function test_register() {
		$mock_wp_theme = $this->build_mock_wp_theme(
			[
				'validate_needed_properties',
				'manipulate_hooks',
			]
		);

		// Expected method calls
		$mock_wp_theme->expects( $this->once() )->method( 'validate_needed_properties' );
		$mock_wp_theme->expects( $this->once() )->method( 'manipulate_hooks' );

		/** @var WP_Plugin_Tmp $mock_wp_theme */
		$mock_wp_theme->register();
	}
}

class WP_Theme_Tmp extends WP_Theme {
	public function manipulate_hooks(): void {
	}

	public function get_name(): string {
		return 'tmp';
	}

	public function get_version(): string {
		return '1.0.1';
	}

	public function call_init_with_needed_params( string $slug ) {
		return $this->init_with_needed_params( $slug );
	}

	public function call_attach_to_wp_app() {
		return $this->attach_to_wp_app();
	}

	public function call_validate_needed_properties() {
		return $this->validate_needed_properties();
	}
}

class WP_App_Tmp_Has_False_WP_Theme {
	public function has( $abstract ) {
		return false;
	}
}

class WP_App_Tmp_Has_True_WP_Theme {
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
