<?php
declare(strict_types=1);

namespace Yivic_Base\App\Routing;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Str;

class Url_Generator_Trailing_Slash extends UrlGenerator {
	/**
	 * Format the given URL segments into a single URL.
	 *  https://github.com/fsasvari/laravel-trailing-slash/blob/v3.0.0/src/UrlGenerator.php
	 *
	 * @param string                         $root
	 * @param string                         $path
	 * @param \Illuminate\Routing\Route|null $route
	 *
	 * @return string
	 */
	public function format( $root, $path, $route = null ) {
		$trailing_slash = ( Str::contains( $path, '#' ) ? '' : '/' );

		return rtrim( parent::format( $root, $path, $route ), '/' ) . $trailing_slash;
	}
}