<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Shared;

use Yivic_Base\Foundation\Shared\Base_Job;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use WP_Mock;

class Base_Job_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_constructor_sets_site_id_correctly(): void {
		// Mock the get_current_blog_id() function
		WP_Mock::userFunction(
			'get_current_blog_id',
			[
				'return' => 123,
			]
		);

		// Create an instance of the Base_Job class
		$base_job = $this->getMockForAbstractClass( Base_Job::class );

		// Assert that the site_id property is set correctly
		$this->assertSame( 123, $this->get_protected_property_value( $base_job, 'site_id' ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_before_handle_releases_job_if_site_id_is_different(): void {
		// Mock the get_current_blog_id() function to return a different ID
		WP_Mock::userFunction(
			'get_current_blog_id',
			[
				'return' => 2,
			]
		);

		// Create a mock of the Base_Job class and mock the release method
		$base_job_mock = $this->getMockBuilder( Base_Job::class )
			->addMethods( [ 'release' ] )
			->getMockForAbstractClass();

		$base_job_mock->expects( $this->once() )
			->method( 'release' )
			->with( 490 );

		// Set the protected site_id property to a different value
		$this->set_property_value( $base_job_mock, 'site_id', 1 );

		// Call the before_handle method
		$base_job_mock->before_handle();
	}
}