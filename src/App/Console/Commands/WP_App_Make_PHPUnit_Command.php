<?php

declare(strict_types=1);

namespace Yivic_Base\App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class WP_App_Make_PHPUnit_Command extends GeneratorCommand {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wp-app:make:phpunit';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new phpunit test class with the relative filepath';

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'PHPUnit';

	public function __construct( Filesystem $filesystem ) {
		$this->signature = $this->name . ' {name}
			{--namespace= : Namespace to be replace to the class.}
			{--base-namespace= : Base namespace to be prepended.}
			{--force : Force to overwrite destination file.}
		';

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
		// Name should be the relative file path
		$name = $this->getNameInput();

		$base_path = getcwd();
		$file_path = ! empty( $this->get_target_path_option() ) ? $this->get_target_path_option() . DIRECTORY_SEPARATOR . $this->getNameInput() : $base_path . DIRECTORY_SEPARATOR . $name;
		if ( strtolower( substr( $file_path, -4 ) ) !== '.php' ) {
			$file_path .= '.php';
		}

		// Next, We will check to see if the class already exists. If it does, we don't want
		// to create the class and overwrite the user's code. So, we will bail out so the
		// code is untouched. Otherwise, we will continue generating this class' files.
		if ( ( ! $this->hasOption( 'force' ) ||
				! $this->option( 'force' ) ) &&
			$this->files->exists( $file_path ) ) {
			$this->error( $this->type . ' already exists!' );

			return false;
		}

		// Next, we will generate the path to the location where this class' file should get
		// written. Then, we will build the class and make the proper replacements on the
		// stub files so that it gets the correctly formatted namespace and class name.
		$this->makeDirectory( $file_path );

		$built_class = $this->get_base_namespace_option()
			? $this->buildClass( $this->getNameInput() )
			: $this->buildClass( $this->getNameInput() );
		$this->files->put( $file_path, $this->sortImports( $built_class ) );

		$this->info( sprintf( 'File %s, type %s, created successfully.', $file_path, $this->type ) );
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

	protected function get_namespace_option() {
		return $this->hasOption( 'namespace' ) ? rtrim( trim( (string) $this->option( 'namespace' ) ), ' \\/' ) : '';
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub() {
		return $this->resolveStubPath( '/plugins/yivic-base/stubs/phpunit.stub' );
	}

	/**
	 * Resolve the fully-qualified path to the stub.
	 *
	 * @param  string  $stub
	 * @return string
	 */
	protected function resolveStubPath( $stub ) {
		$fallback_stub = __DIR__ . DIR_SEP . '..' . DIR_SEP . '..' . DIR_SEP . '..' . DIR_SEP . '..' . DIR_SEP . 'resources' . DIR_SEP . 'stubs' . DIR_SEP . basename( $stub );
		$customPath = $this->laravel->resourcePath( trim( $stub, '/' ) );
		return file_exists( $customPath ) ? $customPath : $fallback_stub;
	}

	/**
	 * Get the namespace for the created class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getNamespace( $name ) {
		if ( ! empty( $this->get_namespace_option() ) ) {
			$namespace = str_replace( '\\\\', '\\', $this->get_namespace_option() );
		} else {
			$parts = array_slice( explode( '/', $name ), 0, -1 );
			array_walk(
				$parts,
				function ( &$value ) {
					$value = ucwords( $value );
				}
			);
			$namespace = implode( '\\', $parts );
		}

		$namespace = trim( $namespace, '\\' );

		return $this->get_base_namespace_option() ? trim( str_replace( '\\\\', '\\', $this->get_base_namespace_option() ), '\\' ) . '\\' . $namespace : $namespace;
	}

	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return string
	 */
	protected function replaceClass( $stub, $name ) {
		$class = basename( $name );

		return str_replace( [ 'DummyClass', '{{ class }}', '{{class}}' ], $class, $stub );
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace( $rootNamespace ) {
		return $rootNamespace;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return [
			[ 'sync', null, InputOption::VALUE_NONE, 'Indicates that job should be synchronous' ],
		];
	}

	/**
	 * Get the file path to be created, should be a relative one.
	 *
	 * @return string
	 */
	protected function get_file_path_input() {
		return trim( $this->argument( 'name' ) );
	}
}