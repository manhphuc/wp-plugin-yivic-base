<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class WP_User_Can_Or extends Middleware {

	public function handle( $request, Closure $next, ...$capabilities ) {
		$message = config( 'app.debug' ) ?
			__( 'Access Denied! You need to login with proper account to perform this action!', 'yivic-base' ) . ' :: ' . implode( ', ', (array) $capabilities ) :
			__( 'Access Denied!', 'yivic-base' );

		foreach ( $capabilities as $capability ) {
			if ( current_user_can( $capability ) ) {
				return $next( $request );
			}
		}

		abort( 403, $message );
	}
}
