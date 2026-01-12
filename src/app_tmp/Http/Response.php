<?php

declare(strict_types=1);

namespace Yivic_Base\App\Http;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class Response extends HttpResponse {
	protected $request;

	public function set_request( Request $request ) {
		$this->request = $request;
	}

	public function get_request(): Request {
		return $this->request;
	}
}