<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;

/**
 * @method static function exec(): void
 */
class Add_More_Providers_Action extends Base_Action {
	use Executable_Trait;

	private $providers = [];

	public function __construct( array $providers ) {
		$this->providers = $providers;
	}

	public function handle(): array {
		$more_providers = [];

		if ( defined( 'WP_APP_TINKER_ENABLED' ) && WP_APP_TINKER_ENABLED ) {
			$more_providers[] = \Yivic_Base\App\Providers\Support\Tinker_Service_Provider::class;
		}

		$providers = array_merge(
			$this->providers,
			$more_providers
		);

		return $providers;
	}
}
