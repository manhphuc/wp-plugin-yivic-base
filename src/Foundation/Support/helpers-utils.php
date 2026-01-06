<?php

/**
 * Below functions are for development debugging
 */

declare(strict_types=1);

use Yivic_Base\App\Support\Yivic_Base_Helper;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

if ( ! function_exists( 'devd' ) ) {
	/**
	 * @throws \Exception
	 */
	function devd( ...$vars ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$dev_trace = debug_backtrace();

		echo "=== start of dev dump ===\n";
		dump( ...$vars );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ( ! empty( $dev_trace[1] ) ) ? $dev_trace[1]['file'] . ':' . $dev_trace[1]['line'] . ': ' . "\n" : '';
		// We want to put the file name and the 7 steps trace to know where
		//  where the dump is produced
		if ( ! Yivic_Base_Helper::is_console_mode() && defined( 'DEV_LOG_TRACE' ) ) {
			echo 'Traceback: ';
			dump( $dev_trace );
		}
		echo "\n=== end of dev dump === ";
	}
}

if ( ! function_exists( 'devdd' ) ) {
	/**
	 * @throws \Exception
	 */
	function devdd( ...$vars ): void {
		devd( ...$vars );
		die( 1 );
	}
}

if ( ! function_exists( 'devvard' ) ) {
	function devvard( $var_to_be_dumped, int $max_depth = 5, bool $is_dump_content = true ) {
		$dumper = new CliDumper();
		$cloner = new VarCloner();
		$cloner->addCasters( ReflectionCaster::UNSET_CLOSURE_FILE_INFO );

		// Clone the variable and set the maximum depth
		$cloned_var = $cloner->cloneVar( $var_to_be_dumped )->withMaxDepth( $max_depth );

		// Dump the variable
		$dump = $dumper->dump( $cloned_var, true );
		// Output or return the dump based on the $is_dump_content flag
		if ( $is_dump_content ) {
			echo "=== start of dev var dump ===\n";
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $dump;
			echo "=== end of dev var dump ===\n";
		} else {
			return $dump;
		}
	}
}

if ( ! function_exists( 'develog' ) ) {
	function develog( ...$vars ): void {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$dev_trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 0 );

		$log_message = '';
		$log_message .= ! empty( $dev_trace[1] ) ? 'Debugging dev_error_log, url (' . Yivic_Base_Helper::get_current_url() . ") \n======= Dev logging start here \n" . $dev_trace[1]['file'] . ':' . $dev_trace[1]['line'] . " \n" : '';
		unset( $dev_trace[0] );
		unset( $dev_trace[0] );

		foreach ( $vars as $index => $var ) {
			$dump_content = null;
			if ( $var === null ) {
				$type = 'NULL';
			} else {
				$type = is_object( $var ) ? get_class( $var ) : gettype( $var );

				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
				$dump_content = devvard( $var, 5, false );
			}
			$log_message .= "Var no $index: type " . $type . ' - ' . $dump_content . " \n";
		}

		if ( defined( 'DEV_LOG_TRACE' ) ) {
			$log_message .= 'Trace :' . devvard( $dev_trace, 10, false ) . " \n";
			$log_message .= "\n======= Dev logging ends here =======\n";
			$log_message .= "\n=====================================\n\n\n\n";
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $log_message );
	}
}

if ( ! function_exists( 'devlogger' ) ) {
	function devlogger( ...$vars ): void {
		if ( ! Yivic_Base_Helper::is_app_loaded() ) {
			dump( '======= Laravel application is not loaded. Logger is not available. =======' );
			die( 1 );
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$dev_trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 0 );

		$logger = logger()->channel( 'single' );

		$log_message = '';
		$log_message .= ! empty( $dev_trace[1] ) ? 'Debugging dev_error_log, url (' . Yivic_Base_Helper::get_current_url() . ") \n======= Dev logging start here \n" . $dev_trace[1]['file'] . ':' . $dev_trace[1]['line'] . " \n" : '';
		unset( $dev_trace[0] );
		unset( $dev_trace[0] );
		foreach ( $vars as $index => $var ) {
			$dump_content = null;
			if ( $var === false ) {
				$type = 'NULL';
			} else {
				$type = is_object( $var ) ? get_class( $var ) : gettype( $var );

				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
				$dump_content = devvard( $var, 5, false );
			}
			$log_message .= "Var no $index: type " . $type . ' - ' . $dump_content . " \n";
		}

		if ( defined( 'DEV_LOG_TRACE' ) ) {
			$log_message .= 'Trace :' . devvard( $var, 10, false ) . " \n";
			$log_message .= "\n======= Dev logging ends here =======\n";
			$log_message .= "\n=====================================\n\n\n\n";
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
		$logger->debug( $log_message );
	}
}

if ( ! function_exists( 'devlog' ) ) {
	function devlog( ...$vars ): void {
		develog( ...$vars );
		devlogger( ...$vars );
	}
}

if ( ! function_exists( 'devdlog' ) ) {
	/**
	 * @throws \Exception
	 */
	function devdlog( ...$vars ): void {
		devd( ...$vars );
		develog( ...$vars );
		devlogger( ...$vars );
	}
}
