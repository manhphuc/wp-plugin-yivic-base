<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\App\Support\Traits\Queue_Trait;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Illuminate\Support\Facades\Artisan;

/**
 * @method static function exec(): void
 */
class Perform_Web_Worker_Action extends Base_Action {
	use Executable_Trait;
	use Queue_Trait;

	public function handle() {
		if ( Yivic_Base_Helper::disable_web_worker() ) {
			return;
		}

		// We want to try a job 1 time
		//  And we only want to retry a job 7 minutes after
		Artisan::call(
			'queue:work',
			[
				'connection'        => $this->get_site_database_queue_connection(),
				'--queue'           => $this->get_site_default_queue(),
				'--tries'           => 1,
				'--backoff'         => $this->get_queue_backoff(),
				'--quiet'           => true,
				'--stop-when-empty' => true,
				'--timeout'         => 60,
				'--memory'          => 256,
			]
		);
	}
}