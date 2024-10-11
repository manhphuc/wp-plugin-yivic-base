<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit;

use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use WP_Mock;

class Helpers_Test extends Unit_Test_Case {

	public function skip_test_yivic_base_get_major_version() {
		$version = '1.2.3';
		$result = yivic_base_get_major_version( $version );
		$expected = 1;

		$this->assertEquals( $expected, $result );

		$version = 'ver10.29.3';
		$result = yivic_base_get_major_version( $version );
		$expected = 10;

		$this->assertEquals( $expected, $result );
	}

	public function skip_test_yivic_base_setup_wp_app() {
		// Mock the apply_filters() function
		$mockConfig = $this->get_wp_app_config();

		WP_Mock::userFunction( 'site_url' )
			->once()
			->andReturn( 'http://yivic-dev.local' );

		WP_Mock::userFunction( 'get_locale' )
			->once()
			->andReturn( 'en-us' );

		WP_Mock::userFunction( 'yivic_base_wp_app_prepare_config' )
			->once()
			->with( $mockConfig )
			->andReturn( $mockConfig );

		// Call the function to be tested
		yivic_base_setup_wp_app();

		// Assert that the WP_Application instance has been created
		$this->assertTrue( WP_Application::isset() );
	}
}
