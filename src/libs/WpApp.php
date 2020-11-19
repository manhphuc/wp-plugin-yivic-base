<?php


namespace Yivic\Wp\YivicBase\Libs;

use Yivic\Wp\YivicBase\App\Helpers\ArrayHelper;
use Yivic\Wp\YivicBase\Libs\Traits\ServiceTrait;
use Illuminate\Foundation\Application;
use Illuminate\Config\Repository as ConfigRepository;

class WpApp extends Application {
	use ServiceTrait;

	protected $runtimeViewPaths = [];

	/**
	 * WpApp constructor.
	 *
	 * @param null|string $base_path
	 */
	public function __construct( $base_path = null ) {
		parent::__construct( $base_path );
	}

	/**
	 * Initialize Application with config array
	 *
	 * @param null|array $config
	 */
	public function initAppWithConfig( $config = null ) {
		$this->initConfig( $config );
	}

	/**
	 * Initialize config instance for Application from config array
	 *
	 * @param null|array $config
	 */
	public function initConfig( $config = null ) {
		$this->singleton( 'config', function ( $app ) use ( $config ) {
			return new ConfigRepository( $config );
		} );
	}

	/**
	 * @return mixed
	 */
	public static function config() {
		return static::getInstance()->make( 'config' );
	}

	/**
	 * Set the view paths on runtime
	 *
	 * @param array $paths
	 */
	public function setRuntimeViewPaths( Array $paths ) {
		$this->runtimeViewPaths = $paths;
	}

	/**
	 * Get working runtime view paths
	 *
	 * @return array
	 */
	public function getRuntimeViewPaths() {
		return $this->runtimeViewPaths;
	}

	/**
	 * Set up view paths with WordPress child and parent theme paths
	 */
	public static function setWpThemeViewPaths() {
		static::getInstance()->runtimeViewPaths = array(
			get_stylesheet_directory(),
			get_template_directory(),
		);
	}

	/**
	 * Append more paths to current view paths, it's useful when you want to render view files in plugins
	 *
	 * @param array $paths
	 */
	public static function appendViewPaths( Array $paths ) {
		static::getInstance()->runtimeViewPaths .= ArrayHelper::merge( static::getInstance()->runtimeViewPaths, $paths );
	}
}
