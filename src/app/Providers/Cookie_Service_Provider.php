<?php

declare(strict_types=1);

namespace Yivic_Base\App\Providers;

use Illuminate\Cookie\CookieServiceProvider;

class Cookie_Service_Provider extends CookieServiceProvider {
	public function register() {
		// If running in WP_CLI, we need to skip the session
		if ( class_exists( 'WP_CLI' ) && app()->runningInConsole() ) {
			return;
		}

		parent::register();
	}
}
