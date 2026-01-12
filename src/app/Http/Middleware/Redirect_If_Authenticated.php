<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Middleware;

use Closure;
use Yivic_Base\App\Http\Request;
use Yivic_Base\App\Providers\Support\Route_Service_Provider;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Redirect_If_Authenticated {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle( Request $request, Closure $next, string ...$guards ): Response {
		$guards = empty( $guards ) ? [ null ] : $guards;

		foreach ( $guards as $guard ) {
			if ( Auth::guard( $guard )->check() ) {
				return redirect( Route_Service_Provider::HOME );
			}
		}

		return $next( $request );
	}
}