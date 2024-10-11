<?php

declare(strict_types=1);

namespace Yivic_Base\App\Console\Commands\Tinker;

use Laravel\Tinker\ClassAliasAutoloader;
use Laravel\Tinker\Console\TinkerCommand;
use Psy\Configuration;
use Psy\Shell;
use Psy\VersionUpdater\Checker;

class Tinker_Command extends TinkerCommand {
	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle() {
		$this->getApplication()->setCatchExceptions( false );

		$config = Configuration::fromInput( $this->input );
		$config->setUpdateCheck( Checker::NEVER );

		$config->getPresenter()->addCasters(
			$this->getCasters()
		);

		if ( $this->option( 'execute' ) ) {
			$config->setRawOutput( true );
		}

		$shell = new Shell( $config );
		$shell->addCommands( $this->getCommands() );
		$shell->setIncludes( $this->argument( 'include' ) );

		$path = app()->get_composer_path();
		$path .= '/composer/autoload_classmap.php';

		$config = $this->getLaravel()->make( 'config' );

		$loader = ClassAliasAutoloader::register(
			$shell,
			$path,
			$config->get( 'tinker.alias', [] ),
			$config->get( 'tinker.dont_alias', [] )
		);
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $code = $this->option( 'execute' ) ) {
			try {
				$shell->setOutput( $this->output );
				$shell->execute( $code );
			} finally {
				$loader->unregister();
			}

			return 0;
		}

		try {
			return $shell->run();
		} finally {
			$loader->unregister();
		}
	}
}
