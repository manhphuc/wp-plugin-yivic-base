<?php

declare(strict_types=1);

namespace Yivic_Base\App\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\JobMakeCommand;

class Job_Make_Command extends JobMakeCommand {

	public function __construct( Filesystem $filesystem ) {
		if ( empty( $this->signature ) ) {
			$this->signature = $this->name . ' {name}
				{--base-namespace= : Base namespace to be prepended.}
				{--target-path= : The bath for the target file to be created.}
				{--dry-run : Run the command with doing nothing.}
				{--force : Force to overwrite destination file.}
				{--sync : Create a sync job or not.}
			';
		} else {
			$this->signature .= '
				{--base-namespace : Base namespace to be prepended.}
				{--target-path= : The bath for the target file to be created.}
				{--dry-run : Run the command with doing nothing.}
			';
		}

		parent::__construct( $filesystem );
	}

	/**
	 * Execute the console command.
	 *
	 * @return bool|null
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function handle() {
		// First we need to ensure that the given name is not a reserved word within the PHP
		// language and that the class name will actually be valid. If it is not valid we
		// can error now and prevent from polluting the filesystem using invalid files.
		if ( $this->isReservedName( $this->getNameInput() ) ) {
			$this->error( 'The name "' . $this->getNameInput() . '" is reserved by PHP.' );

			return false;
		}
		// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
		$name = ! empty( $this->get_base_namespace_option() ) ? $this->get_base_namespace_option() . '\\' . '\\' . $this->getNameInput() : $this->qualifyClass( $this->getNameInput() );

		$path = ! empty( $this->get_target_path_option() ) ? $this->get_target_path_option() . DIR_SEP . $this->getNameInput() : $this->getPath( $name );
		if ( strtolower( substr( $path, -4 ) ) !== '.php' ) {
			$path .= '.php';
		}

		// Next, We will check to see if the class already exists. If it does, we don't want
		// to create the class and overwrite the user's code. So, we will bail out so the
		// code is untouched. Otherwise, we will continue generating this class' files.
		if ( ( ! $this->hasOption( 'force' ) ||
			! $this->option( 'force' ) ) &&
			$this->files->exists( $path ) ) {
			$this->error( $this->type . ' already exists!' );

			return false;
		}

		// Next, we will generate the path to the location where this class' file should get
		// written. Then, we will build the class and make the proper replacements on the
		// stub files so that it gets the correctly formatted namespace and class name.
		$this->makeDirectory( $path );

		$built_class = $this->get_base_namespace_option()
			? $this->buildClass( $this->getNameInput() )
			: $this->buildClass( $name );
		$this->files->put( $path, $this->sortImports( $built_class ) );

		$this->info( $this->type . ' created successfully.' );
	}

	/**
	 * Get the target path from the options.
	 *
	 * @return string
	 */
	protected function get_target_path_option() {
		return $this->hasOption( 'target-path' ) ? rtrim( trim( (string) $this->option( 'target-path' ) ), ' \\/' ) : '';
	}

	protected function get_base_namespace_option() {
		return $this->hasOption( 'base-namespace' ) ? rtrim( trim( (string) $this->option( 'base-namespace' ) ), ' \\/' ) : '';
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub() {
		return $this->option( 'sync' )
						? $this->laravel->resourcePath( '/stubs/job.stub' )
						: $this->laravel->resourcePath( '/stubs/job.queued.stub' );
	}

	/**
	 * Build the class with the given name.
	 *
	 * @param  string  $name
	 * @return string
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	protected function buildClass( $name ) {
		$stub = $this->files->get( $this->getStub() );

		return $this->replaceNamespace( $stub, $name )->replaceClass( $stub, $name );
	}

	/**
	 * Replace the namespace for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return $this
	 */
	protected function replaceNamespace( &$stub, $name ) {
		$searches = [
			[ 'DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel' ],
			[ '{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}' ],
			[ '{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}' ],
		];

		foreach ( $searches as $search ) {
			$stub = str_replace(
				$search,
				[ $this->getNamespace( $name ), $this->rootNamespace(), $this->userProviderModel() ],
				$stub
			);
		}

		return $this;
	}

	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return string
	 */
	protected function replaceClass( $stub, $name ) {
		$class = str_replace( $this->getNamespace( $name ) . '\\', '', $name );

		return str_replace( [ 'DummyClass', '{{ class }}', '{{class}}' ], $class, $stub );
	}

	/**
	 * Get the full namespace for a given class, without the class name.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getNamespace( $name ) {
		return $this->get_base_namespace_option()
			? $this->correct_namespace( $this->get_base_namespace_option() )
			: parent::getNamespace( $name );
	}

	protected function getRootNamespace() {
		return $this->get_base_namespace_option()
			? $this->get_base_namespace_option()
			: parent::getRootNamespace();
	}

	/**
	 * Some namespace may have duplicated `\` backslash character, we need to repair it
	 *
	 * @param mixed  $namespace_to_repair
	 *
	 * @return void
	 */
	protected function correct_namespace( mixed $namespace_to_repair ) {
		return str_replace( '\\\\', '\\', $namespace_to_repair );
	}
}
