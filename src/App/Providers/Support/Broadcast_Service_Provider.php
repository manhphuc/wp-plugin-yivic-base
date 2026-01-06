<?php

namespace Yivic_Base\App\Providers\Support;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class Broadcast_Service_Provider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void {
		Broadcast::routes();

		do_action( App_Const::ACTION_WP_APP_BROADCAST_CHANNELS );
	}
}
