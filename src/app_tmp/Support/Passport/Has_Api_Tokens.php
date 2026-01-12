<?php

namespace Yivic_Base\App\Support\Passport;

use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenFactory;

trait Has_Api_Tokens {

	/**
	 * The current access token for the authentication user.
	 *
	 * @var \Laravel\Passport\Token|\Laravel\Passport\TransientToken|null
	 */
	protected $accessToken;

	/**
	 * Get all of the user's registered OAuth clients.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function clients() {
		return $this->hasMany( Passport::clientModel(), 'user_id' );
	}

	/**
	 * Get all of the access tokens for the user.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function tokens() {
		return $this->hasMany( Passport::tokenModel(), 'user_id' )->orderBy( 'created_at', 'desc' );
	}

	/**
	 * Get the current access token being used by the user.
	 *
	 * @return \Laravel\Passport\Token|\Laravel\Passport\TransientToken|null
	 */
	public function token() {
		return $this->accessToken;
	}

	/**
	 * Determine if the current API token has a given scope.
	 *
	 * @param  string  $scope
	 * @return bool
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function tokenCan( $scope ) {
		return $this->accessToken ? $this->accessToken->can( $scope ) : false;
	}

	/**
	 * Create a new personal access token for the user.
	 *
	 * @param  string  $name
	 * @param  array  $scopes
	 * @return \Laravel\Passport\PersonalAccessTokenResult
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function createToken( $name, array $scopes = [] ) {
		return app()->make( PersonalAccessTokenFactory::class )->make(
			$this->getKey(),
			$name,
			$scopes
		);
	}

	/**
	 * Set the current access token for the user.
	 *
	 * @param  \Laravel\Passport\Token|\Laravel\Passport\TransientToken  $accessToken
	 * @return $this
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function withAccessToken( $accessToken ) {
		$this->accessToken = $accessToken;

		return $this;
	}
}