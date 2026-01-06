<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Illuminate\Contracts\Container\BindingResolutionException;
use PHPUnit\Framework\ExpectationFailedException;
use Exception;

/**
 * @method static function exec(): void
 */
class Write_Web_Worker_Script_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Write a js to have periodly ajax request to handle the queue
	 *
	 * @return void
	 * @throws BindingResolutionException
	 * @throws ExpectationFailedException
	 * @throws Exception
	 */
	public function handle(): void {
		if ( Yivic_Base_Helper::disable_web_worker() ) {
			return;
		}

		// We want to add the trailing slash to avoid the redirect in WP webserver rule
		$web_worker_url = esc_js( Yivic_Base_Helper::route_with_wp_url( 'wp-api::web-worker' ) );

		// We want to have an interval that works every 5 mins (300 000 miliseconds)
		//  to perform the web worker (queue, scheduler worker) execution
		$script = '<script type="text/javascript">';
		$script .= 'var yivic_base_web_worker_url = \'' . $web_worker_url . '\';';
		$script .= 'function ajax_request_to_web_worker() {';
		$script .= 'if (typeof(jQuery) !== "undefined") {';
		$script .= 'jQuery.ajax({ url: yivic_base_web_worker_url, method: "POST" });';
		$script .= '} else {';
		$script .= 'fetch(yivic_base_web_worker_url);';
		$script .= '}';
		$script .= '}';
		$script .= 'var ajax_request_to_web_worker_interval = window.setInterval(function(){';
		$script .= 'ajax_request_to_web_worker();';
		$script .= '}, 7*7*60*1000);';
		$script .= 'window.setTimeout(function() {';
		$script .= 'ajax_request_to_web_worker();';
		$script .= '}, 1000);';
		$script .= '</script>';

		// We suppress phpcs rule here because we escape the variable already
		//  the rest of the script are static text
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $script;
	}
}
