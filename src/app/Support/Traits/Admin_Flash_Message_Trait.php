<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support\Traits;

use Illuminate\Support\Facades\Session;

trait Admin_Flash_Message_Trait {
	public function add_admin_warning_message( $message ) {
		Session::put( 'admin-caution', $message );
	}

	public function add_admin_success_message( $message ) {
		Session::put( 'admin-success', $message );
	}

	public function add_admin_info_message( $message ) {
		Session::put( 'admin-info', $message );
	}

	public function add_admin_error_message( $message ) {
		Session::put( 'admin-error', $message );
	}
}
