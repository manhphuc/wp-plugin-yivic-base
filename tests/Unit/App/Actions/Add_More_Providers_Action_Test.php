<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\App\Actions;

use Yivic_Base\App\Actions\Add_More_Providers_Action;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;

class Add_More_Providers_Action_Test extends Unit_Test_Case {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_constructor_sets_providers() {
		// Define an array of providers to be passed to the constructor
		$expected_providers = [
			'Some_Provider_1',
			'Some_Provider_2',
			'Some_Provider_3',
		];

		// Instantiate the Add_More_Providers_Action class with the providers
		$action = new Add_More_Providers_Action( $expected_providers );

		// Use reflection to access the private $providers property
		$actual_providers = $this->get_protected_property_value( $action, 'providers' );

		// Assert that the actual providers match the expected providers
		$this->assertEquals( $expected_providers, $actual_providers );
	}

	public function test_handle_with_tinker_disabled() {
		// Disable the WP_APP_TINKER_ENABLED constant
		if ( ! defined( 'WP_APP_TINKER_ENABLED' ) ) {
			// Define the initial providers array
			$initial_providers = [
				'Some_Provider_1',
				'Some_Provider_2',
			];

			// Instantiate the Add_More_Providers_Action class with initial providers
			$action = new Add_More_Providers_Action( $initial_providers );

			// Call the handle method
			$providers = $action->handle();

			// Assert that the providers remain unchanged when Tinker is disabled
			$this->assertEquals( $initial_providers, $providers );
		}
	}

	public function test_handle_with_tinker_enabled() {
		// Enable the WP_APP_TINKER_ENABLED constant
		if ( ! defined( 'WP_APP_TINKER_ENABLED' ) ) {
			define( 'WP_APP_TINKER_ENABLED', true );

			// Define the initial providers array
			$initial_providers = [
				'Some_Provider_1',
				'Some_Provider_2',
			];

			// Instantiate the Add_More_Providers_Action class with initial providers
			$action = new Add_More_Providers_Action( $initial_providers );

			// Call the handle method
			$providers = $action->handle();

			// Assert that the Tinker provider is added to the list
			$expected_providers = array_merge(
				$initial_providers,
				[ \Yivic_Base\App\Providers\Support\Tinker_Service_Provider::class ]
			);
			$this->assertEquals( $expected_providers, $providers );
		}
	}
}
