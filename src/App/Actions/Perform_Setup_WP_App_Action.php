<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Illuminate\Support\Facades\Artisan;

/**
 * @method static function exec(): void
 */
class Perform_Setup_WP_App_Action extends Base_Action {
	use Executable_Trait;

	public function handle() {
		Yivic_Base_Helper::prepare_wp_app_folders();

		Artisan::call(
			'wp-app:setup',
			[]
		);

		$output = Artisan::output();
		echo( esc_html( $output ) );
	}
}
