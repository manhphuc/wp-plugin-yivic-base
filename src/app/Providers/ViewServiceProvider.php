<?php


namespace Yivic\Wp\YivicBase\App\Providers;

use Yivic\Wp\YivicBase\Libs\WpApp;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider as ConcreteViewServiceProvider;

class ViewServiceProvider extends ConcreteViewServiceProvider {
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		$this->registerFactory();

		$this->registerViewFinder();

		$this->registerBladeCompiler();

		$this->registerEngineResolver();
	}

	/**
	 * Register the view finder implementation.
	 *
	 * @return void
	 */
	public function registerViewFinder() {
		$this->app->bind( 'view.finder', function ( $app ) {
			/* @var WpApp $app */
			$paths = $app->getRuntimeViewPaths();

			return new FileViewFinder( $app['files'], $paths );
		} );
	}
}
