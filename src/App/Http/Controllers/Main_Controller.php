<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Controllers;

use Yivic_Base\App\Actions\Mark_Setup_WP_App_Done_Action;
use Yivic_Base\App\Actions\Mark_Setup_WP_App_Failed_Action;
use Yivic_Base\App\Http\Request;
use Yivic_Base\App\Support\App_Const;
use Yivic_Base\App\WP\Yivic_Base_WP_Plugin;
use Yivic_Base\Foundation\Http\Base_Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use WP_Query;

class Main_Controller extends Base_Controller {
	public function index() {
		if ( ! config( 'app.debug' ) ) {
			header( 'Location: ' . home_url() );
			exit( 0 );
		}

		return Yivic_Base_WP_Plugin::wp_app_instance()->view(
			'main/index',
			[
				'message' => empty( Auth::user() ) ? 'Hello guest, welcome to WP App home screen' : sprintf( 'Logged-in user is here, username %s, user ID %s', Auth::user()->ID, Auth::user()->user_login ),
			]
		);
	}

	/**
	 * Display the content for the WP Homepage
	 * @return View|Factory
	 * @throws Exception
	 * @throws BindingResolutionException
	 */
	public function home() {
		if ( ! config( 'app.debug' ) ) {
			header( 'Location: ' . home_url() );
			exit( 0 );
		}

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_query'] = new WP_Query(
			[
				'post_type' => 'post',
			]
		);
		return Yivic_Base_WP_Plugin::wp_app_instance()->view(
			'main/home',
		);
	}

	/**
	 * @throws \Exception
	 */
	public function setup_app( Request $request ) {
		try {
			ob_start();
			do_action( App_Const::ACTION_WP_APP_SETUP_APP );
			ob_end_flush();
		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( Exception $e ) {
		}

		if ( empty( $e ) ) {
			// If no exception thrown earlier, we can consider the setup script is done
			Mark_Setup_WP_App_Done_Action::exec();
		} else {
			Mark_Setup_WP_App_Failed_Action::exec( $e->getMessage() );
		}

		/** Then return to the previous URL  */
		$return_url = $request->get( 'return_url', home_url() );

		header( 'Location: ' . $return_url );
		exit( 0 );
	}
}
