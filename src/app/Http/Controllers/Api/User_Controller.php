<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Yivic_Base\Foundation\Http\Base_Controller;
use Illuminate\Http\Request;

class User_Controller extends Base_Controller {
	public function info( Request $request ): JsonResponse {
		$user = $request->user();
		wp_set_current_user( $user->ID );
		$wp_user = wp_get_current_user();
		unset( $wp_user->data->user_pass, $wp_user->data->user_activation_key );

		return response()->json(
			[
				'data' => [
					'user' => $user,
					'wp_user' => wp_get_current_user(),
				],
			]
		);
	}
}
