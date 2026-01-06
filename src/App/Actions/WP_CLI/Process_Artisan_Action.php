<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions\WP_CLI;

use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use InvalidArgumentException;

/**
 * @method static function exec(): void
 */
class Process_Artisan_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		/** @var \Yivic_Base\App\Console\Kernel $kernel */
		$kernel = app()->make(
			\Illuminate\Contracts\Console\Kernel::class
		);

		// We need to remove 2 first items to match the artisan arguments
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$args = $_SERVER['argv'];
		if ( ! in_array( 'artisan', $args ) ) {
			throw new InvalidArgumentException( 'Not an artisan command' );
		}

		$artisan_args = [];
		foreach ( $args as $arg ) {
			if ( $arg === 'artisan' || ! empty( $artisan_args ) ) {
				$artisan_args[] = $arg;
			}
		}

		$input = new \Symfony\Component\Console\Input\ArgvInput( $artisan_args );

		$status = $kernel->handle(
			$input,
			new \Symfony\Component\Console\Output\ConsoleOutput()
		);
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit( $status );
	}
}
