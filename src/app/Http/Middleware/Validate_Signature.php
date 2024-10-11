<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as Middleware;

class Validate_Signature extends Middleware {

	/**
	 * The names of the query string parameters that should be ignored.
	 *
	 * @var array<int, string>
	 */
	protected $except = [
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// 'fbclid',
		// 'utm_campaign',
		// 'utm_content',
		// 'utm_medium',
		// 'utm_source',
		// 'utm_term',
	];
}
