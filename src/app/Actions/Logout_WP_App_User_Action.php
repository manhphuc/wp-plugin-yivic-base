<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Illuminate\Support\Facades\Auth;

/**
 * @method static function exec(): void
 */
class Logout_WP_App_User_Action extends Base_Action {
	use Executable_Trait;

	public function handle() {
		Auth::logoutCurrentDevice();
		session()->save();
	}
}
