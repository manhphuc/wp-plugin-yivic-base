<?php


namespace Yivic\Wp\YivicBase\App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class WordPressServiceProvider For registering needed things for WP
 * @package Yivic\Wp\YivicBase\App\Providers
 */
class WordPressServiceProvider extends ServiceProvider {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->registerHooks();
	}

	protected function registerHooks() {
		// For frontend
		add_action( 'safe_style_css', [ $this, 'add_safe_style_css' ] );
		add_filter( 'body_class', [ $this, 'add_site_id_to_body_class' ] );

		// For both
		add_action( 'upload_mimes', [ $this, 'allow_svg_upload' ] );
	}

	/**
	 * Add more classes to body
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function add_site_id_to_body_class( $classes ) {
		$classes[] = get_current_blog_id();
		if ( is_singular() ) {
			global $post;
			$classes[] = $post->post_name;
		}

		return $classes;
	}

	/**
	 * Allow svg file to be uploaded as a media file
	 *
	 * @param $mimes
	 *
	 * @return mixed
	 */
	public function allow_svg_upload( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['webp'] = 'image/webp';

		return $mimes;
	}

	/**
	 * Add an extra caption field to Flexible content layout title
	 *
	 * @param $title
	 * @param $field
	 * @param $layout
	 * @param $i
	 *
	 * @return bool
	 */
	public function add_caption_to_flexible_content( $title, $field, $layout, $i ) {
		if ( $value = get_sub_field( 'caption' ) ) {
			return $title . ' - ' . $value;
		} else {
			foreach ( $layout['sub_fields'] as $sub ) {
				if ( $sub['name'] == 'caption' ) {
					$key = $sub['key'];
					if ( array_key_exists( $i, $field['value'] ) && $value = $field['value'][ $i ][ $key ] ) {
						return $title . ' - ' . $value;
					}
				}
			}
		}

		return $title;
	}
}
