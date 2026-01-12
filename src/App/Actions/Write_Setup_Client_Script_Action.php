<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use PHPUnit\Framework\ExpectationFailedException;
use Exception;

/**
 * @method static function exec(): void
 */
class Write_Setup_Client_Script_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * We write client script to send ajax request to the setup url on the screen right after
	 *  the plugin is activated
	 *
	 * @return void
	 * @throws ExpectationFailedException
	 * @throws Exception
	 */
	public function handle(): void {
		/** @var \WP_Screen $current_screen */
		global $current_screen;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_admin() && $current_screen->id === 'plugins' && $current_screen->parent_file === 'plugins.php' && ! empty( $_GET['activate'] ) ) {
			$setup_url = esc_js(
				Yivic_Base_Helper::route_with_wp_url(
					'wp-app::setup-app',
					[
						'force_app_running_in_console' => 1,
					]
				)
			);

			$script = '<script type="text/javascript">';
			$script .= 'var yivic_base_setup_url = \'' . $setup_url . '\';';
			$script .= 'if (typeof(jQuery) !== "undefined") {';
			$script .= 'jQuery.ajax({ url: yivic_base_setup_url, method: "GET" });';
			$script .= '} else {';
			$script .= 'fetch(yivic_base_setup_url);';
			$script .= '}';
			$script .= '</script>';

			// We suppress phpcs rule here because we escape the variable already
			//  the rest of the script are static text
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $script;
		}
	}
}