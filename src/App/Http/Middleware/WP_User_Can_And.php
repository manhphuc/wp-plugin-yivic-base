<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Middleware;

use Closure;
use Yivic_Base\App\Support\Traits\Yivic_Base_Trans_Trait;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class WP_User_Can_And extends Middleware {
	use Yivic_Base_Trans_Trait;

	public function handle( $request, Closure $next, ...$capabilities ) {
		$message = config( 'app.debug' ) ?
			$this->__( 'Access Denied! You need to login with proper account to perform this action!' ) . ' :: ' . implode( ', ', (array) $capabilities ) :
			$this->__( 'Access Denied!' );

		foreach ( $capabilities as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				abort( 403, $message );
			}
		}

		return $next( $request );
	}
}
