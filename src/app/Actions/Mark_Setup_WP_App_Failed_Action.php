<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Support\App_Const;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;

/**
 * @method static function exec(): void
 */
class Mark_Setup_WP_App_Failed_Action extends Base_Action {
	use Executable_Trait;

	protected $message;

	public function __construct( $message ) {
		$this->message = $message;
	}

	public function handle() {
		// We need to flag issue to the db
		update_option( App_Const::OPTION_SETUP_INFO, 'failed', false );

		do_action( App_Const::ACTION_WP_APP_MARK_SETUP_APP_FAILED, $this->message );
	}
}
