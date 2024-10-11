<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Illuminate\Support\Facades\Session;

/**
 * @method static function exec(): void
 */
class Show_Admin_Notice_From_Flash_Messages_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * We want to show Admin notice for 4 types of messages: 'error', 'success', 'warning', 'info'
	 *
	 * @return void
	 */
	public function handle() {
		$flash_keys = [ 'admin-error', 'admin-success', 'admin-info', 'admin-caution' ];
		foreach ( $flash_keys as $type ) {
			if ( Session::has( $type ) && ! empty( Session::get( $type ) ) ) {
				$display_type = str_replace( 'admin-', '', $type );
				$admin_message = $this->build_html_messages( (array) Session::get( $type ) );
				add_action(
					'admin_notices',
					function () use ( $admin_message, $display_type ) {
						echo '<div class="notice notice-' . ( $display_type === 'caution' ? 'warning' : esc_attr( $display_type ) ) . ' is-dismissible">
						<p>' . wp_kses_post( $admin_message ) . '</p>
					</div>';
					}
				);
				Session::forget( $type );
			}
		}
		Session::save();
	}

	protected function build_html_messages( array $messages ): string {
		$extracted_messages = [];
		array_walk_recursive(
			$messages,
			function ( $value ) use ( &$extracted_messages ) {
				$extracted_messages[] = $value;
			}
		);

		return implode( '<br />', $extracted_messages );
	}
}
