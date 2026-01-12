<?php

declare(strict_types=1);

namespace Yivic_Base\App\Exceptions;

use Exception;
use Throwable;

class Simple_Exception extends Exception {
	protected $error_message;
	protected $http_code;
	protected $error_code;
	protected $errors;
	protected $previous;
	protected $extra_data;

	/**
	 *
	 * @param string $http_code
	 * @param int $error_message
	 * @param null|Throwable $error_code
	 * @param array $errors Key - value pair array
	 * @param Exception|null $previous
	 * @param array $extra_data
	 * @return void
	 */
	public function __construct(
		$error_message = null,
		$http_code = 400,
		$error_code = 400,
		array $errors = [],
		Exception $previous = null,
		array $extra_data = []
	) {
		$this->http_code = $http_code;
		$this->error_message = $error_message;
		$this->error_code = ! empty( $error_code ) ? $error_code : $http_code;
		$this->errors = $errors;
		$this->previous = $previous;
		$this->extra_data = $extra_data;

		parent::__construct( (string) $error_message, (int) $error_code, $previous );
	}

	public function __toString() {
		return sprintf( 'Exception thrown, code: [%s], message: %s at class %s', $this->error_code, $this->error_message, __CLASS__ ) . "\n";
	}
}