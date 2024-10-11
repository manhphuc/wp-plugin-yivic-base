<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers\Support;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class Auth_Service_Provider extends AuthServiceProvider {
	/**
	 * The model to policy mappings for the application.
	 *
	 * @var array<class-string, class-string>
	 */
	protected $policies = [];

	/**
	 * Register any authentication / authorization services.
	 */
	public function boot(): void {
		do_action( App_Const::ACTION_WP_APP_AUTH_BOOT );
	}
}
