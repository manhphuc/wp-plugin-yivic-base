<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Actions;

use Yivic_Base\App\Exceptions\Simple_Exception;

/**
 * The class must have the method handle()
 */
abstract class Base_Action {
	protected $needs_executor_authorization = false;
	protected $executor = null;
	protected static $authorized_executor = null;
	protected $needs_data_validation = false;
	protected $data = null;
	protected static $validated_data = null;

	protected $validation_rules;
	protected $validation_messages;
	protected $validation_custom_attributes;

	final public function execute() {
		// We want to check if the flag `needs_executor_authorization` is on
		//  to perform the authorization
		//  then assign to the static authozied_executor
		// The purpose to use static authozied_executor is to avoid the re-authenticate
		//  if the executor is not updated
		if ( $this->needs_executor_authorization && empty( static::$authorized_executor ) ) {
			if ( empty( $this->executor ) ) {
				throw new Simple_Exception( 'No executor found' );
			}

			if ( ! $this->authorize_executor() ) {
				$this->throw_unauthorized_exception();
			}
		}
		$this->assign_authorized_executor();

		// We want to check if the flag `needs_data_validation` is on
		//  to perform the validation
		//  then assign to the static validated_data
		// The purpose to use static validated_data is to avoid the re-validate
		//  if the data is not updated
		if ( $this->needs_data_validation && empty( static::$validated_data ) ) {
			static::$validated_data = $this->process_validated_data();
		}

		return app()->call( [ $this, 'handle' ] );
	}

	final public function with_data( array $data ) {
		$this->data = $data;
		static::$validated_data = null;

		return $this;
	}

	final public function with_executor( $executor ) {
		$this->executor = $executor;
		static::$authorized_executor = null;

		return $this;
	}

	protected function authorize_executor(): bool {
		return true;
	}

	protected function assign_authorized_executor() {
		static::$authorized_executor = $this->executor;
	}

	protected function before_process_validated_data(): void {
		$this->validation_rules = $this->get_validation_rules();
		$this->validation_messages = $this->get_validation_messages();
		$this->validation_custom_attributes = $this->get_validation_custom_attributes();
	}

	final protected function process_validated_data(): array {
		$this->before_process_validated_data();

		return validator(
			$this->data,
			$this->validation_rules,
			$this->validation_messages,
			$this->validation_custom_attributes,
		)->validate();
	}

	protected function get_validation_rules(): array {
		return [];
	}

	protected function get_validation_messages(): array {
		return [];
	}

	protected function get_validation_custom_attributes(): array {
		return [];
	}

	protected function throw_unauthorized_exception() {
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		throw new Simple_Exception( $this->get_unauthorized_exception_message() );
	}

	protected function get_unauthorized_exception_message() {
		return 'You are not authorized to perform this action';
	}
}
