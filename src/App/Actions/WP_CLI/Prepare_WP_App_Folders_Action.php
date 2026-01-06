<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions\WP_CLI;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use WP_CLI;

/**
 * @method static function exec(): void
 */
class Prepare_WP_App_Folders_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		Yivic_Base_Helper::prepare_wp_app_folders();

		WP_CLI::success( 'Preparing needed folders for WP App done!' );
	}
}
