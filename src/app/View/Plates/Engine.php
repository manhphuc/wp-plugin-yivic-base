<?php
declare(strict_types=1);

namespace Yivic\WP_Plugin\Yivic_Base\App\View\Plates;

use Yivic\WP_Plugin\Yivic_Base\Dependencies\Illuminate\Contracts\Container\BindingResolutionException;
use Yivic\WP_Plugin\Yivic_Base\Dependencies\League\Plates\Engine as PlatesEngine;
use Yivic\WP_Plugin\Yivic_Base\Dependencies\League\Plates\Template\Theme as PlatesTheme;
use Yivic\WP_Plugin\Yivic_Base\Dependencies\Illuminate\Contracts\View\Engine as EngineContract;
use Throwable;

class Engine implements EngineContract {

	/** @var PlatesEngine */
	private $engine;

	public function __construct( PlatesEngine $engine ) {
		$this->engine = $engine;
	}

	/**
	 * Get the evaluated contents of the view.
	 *
	 * @param  string  $path
	 * @param  array   $data
	 * @return string
	 */
	public function get( $path, array $data = [] ) {
		/** @var \Yivic\WP_Plugin\Yivic_Base\Dependencies\Illuminate\View\FileViewFinder $view_finder */
		$view_finder = view()->getFinder();
		$template_full = array_keys( $view_finder->getViews() )[0] ?? '';
		$template_path = array_keys( $view_finder->getViews() )[1] ?? '';

		$template_parts = explode( '::', $template_full );
		if ( ! empty( $template_full[1] ) ) {
			$namespace_slug = $template_parts[0];
			$template_name = $template_parts[1];
		} else {
			$namespace_slug = '';
			$template_name = $template_parts[0];
		}

		$view_paths = config( 'view.paths' );
		if ( $namespace_slug ) {
			$themes = [];
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			// foreach ($paths as $tmp_index => $path) {
			//  $themes[] = PlatesTheme::new($path, 'plate_theme_'.$tmp_index);
			// }
			$themes[] = PlatesTheme::new( app( $namespace_slug )->get_views_path(), 'plate_theme_0' );
			for ( $tmp_index = count( $view_paths ) - 1; $tmp_index >= 0; $tmp_index-- ) {
				$themes[] = PlatesTheme::new( $view_paths[ $tmp_index ] . DIR_SEP . '_plugins' . DIR_SEP . $namespace_slug, 'plate_theme_' . ( $tmp_index + 1 ) );
			}
			$engine = PlatesEngine::fromTheme( PlatesTheme::hierarchy( $themes ), 'php' );

			$view_paths[] = app( $namespace_slug )->get_views_path();
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			// dev_dump($engine);
		} else {
			$engine = $this->engine;
		}

		$template_to_be_rendered = str_replace( '.', '/', $template_name );

		return $engine->render( $template_to_be_rendered, $data );
	}

	/**
	 * Handle a view exception.
	 *
	 * @param  \Throwable  $e
	 * @param  int  $obLevel
	 * @return void
	 *
	 * @throws \Throwable
	 */
	protected function handleViewException( Throwable $e, $obLevel ) {
		throw $e;
	}

	/**
	 * Get the path of the folder where the to be rendered view is found
	 * @param string $path
	 * @return string
	 * @throws BindingResolutionException
	 */
	private function get_found_view_path( string $template_path, array $view_paths ): string {
		foreach ( $view_paths as $view_path ) {
			if ( strpos( $template_path, $view_path ) !== false ) {
				return $view_path;
			}
		}

		return '';
	}
}
