<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support;

class Yivic_Base_Hook_Handlers {
	public static function print_admin_notice_messages( array &$error_messages ): void {
		$error_content = '';
		foreach ( $error_messages as $error_message => $displayed ) {
			if ( ! $displayed && $error_message ) {
				$error_content .= '<p>' . $error_message . '</p>';
				$error_messages[ $error_message ] = true;
			}
		}
		if ( $error_content ) {
			echo '<div class="notice notice-error">' . wp_kses_post( $error_content ) . '</div>';
		}
	}
}
