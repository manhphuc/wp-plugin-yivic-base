<?php

namespace Yivic_Base\Tests\Support\Unit\Libs;

use Yivic_Base\App\WP\WP_Application;
use Yivic_Base\Tests\Support\Helpers\Test_Utils_Trait;
use Mockery;
use WP_Mock;

class Unit_Test_Case extends \PHPUnit\Framework\TestCase {
	use Test_Utils_Trait;

	protected $wp_app;
	protected $wp_app_base_path;

	protected function setUp(): void {
	}

	protected function tearDown(): void {
		Mockery::close();
	}

	protected function setup_wp_app() {
		$this->wp_app_base_path = codecept_root_dir();
		$config = $this->get_wp_app_config();
		$this->wp_app = WP_Application::init_instance_with_config(
			$this->wp_app_base_path,
			$config
		);
	}

	protected function get_wp_app_config() {
		return [
			'app' => [],
			'view' => [
				'paths' => [ $this->wp_app_base_path ],
				'compiled' => [ codecept_output_dir() ],
			],
			'env' => 'local',
			'wp_app_slug' => 'wp-app',
			'wp_api_slug' => 'wp-api',
		];
	}
}