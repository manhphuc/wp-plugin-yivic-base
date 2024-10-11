<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Controllers;

use Yivic_Base\App\Support\App_Const;
use Yivic_Base\Foundation\Http\Base_Controller;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class User_Controller extends Base_Controller {
	public function __construct() {
		$this->middleware( [ 'wp_user_session_validation' ] );
	}

	public function generate_client_app() {
		$wp_user = wp_get_current_user();
		$user_id = get_current_user_id();
		$app_name = 'Client Credentials Grant App for ' . $wp_user->data->display_name;

		// We want to delete all previous Client Credentials Apps
		//  Client Credentials Apps are specified by
		//  personal_access_client = 0 and password_client = 0
		Passport::client()->where(
			[
				'user_id' => $user_id,
				'personal_access_client' => false,
				'password_client' => false,
			]
		)->delete();

		// Then create a new app here with the new secret
		$client = Passport::client()->forceFill(
			[
				'user_id' => $user_id,
				'name' => $app_name,
				'secret' => Str::random( 40 ),
				'provider' => null,
				'redirect' => '',
				'personal_access_client' => false,
				'password_client' => false,
				'revoked' => false,
			]
		);
		$client->save();

		// We update new values to the user meta
		update_user_meta( $user_id, App_Const::USER_META_CLIENT_CREDENTIALS_APP_ID, $client->id );
		update_user_meta( $user_id, App_Const::USER_META_CLIENT_CREDENTIALS_APP_SECRET, $client->plainSecret );

		return response()->json(
			[
				'client_id' => $client->id,
				'client_secret' => $client->plainSecret,
			]
		);
	}
}
