<?php
/**
 * Created by PhpStorm.
 * User: manhphucofficial@yahoo.com
 * Date: 11/19/20
 * Time: 6:57 PM
 */

namespace Yivic\Wp\YivicBase\Libs;


class WpCommon {

	/**
	 * Get content of a template block for the layout with params
	 * Template file should be in `templates` folder of child theme, parent theme or of this plugin
	 *
	 * @param string $template_slug name of the template
	 * @param array $params arguments needed to be sent to the view
	 *
	 * @return string
	 */
	public static function get_template_part( $template_slug, $params = [] ) {
		// Todo: add object cache function for template
		extract( $params );
		$template_default_path     = YIVIC_BASE_PLUGIN_PATH . DIRECTORY_SEPARATOR . $template_slug . '.php';
		$template_theme_path       = get_template_directory() . DIRECTORY_SEPARATOR . $template_slug . '.php';
		$template_child_theme_path = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $template_slug . '.php';
		ob_start();
		if ( file_exists( $template_child_theme_path ) ) {
			include $template_child_theme_path;
		} else if ( file_exists( $template_theme_path ) ) {
			include $template_theme_path;
		} else if ( file_exists( $template_default_path ) ) {
			include( $template_default_path );
		}
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	/**
	 * Turn a post content to complete output like `the_content`
	 *
	 * @param string $post_content
	 */
	public static function get_post_content( $content ) {
		/**
		 * Filters the post content.
		 *
		 * @since 0.71
		 *
		 * @param string $content Content of the current post.
		 */
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		return $content;
	}

	/**
	 * Highlight the occurrence of words in a string in a text
	 *
	 * @param string $text_to_highlight
	 * @param null|string $search_term
	 */
	public static function highlight_keyword( $text_to_highlight, $search_query = null, $regex_replacement = "<i class='found-text'>$0</i>", &$text_replaced_count = 0 ) {
		$search_query = trim( $search_query );
		$arr_tmp      = array_unique( preg_split( '/\s+/', $search_query ) );

		$arr_keyword_pattern   = [];
		$arr_keyword_pattern[] = "/\p{L}*?" . preg_quote( implode( ' ', $arr_tmp ) ) . "\p{L}*/i";
		foreach ( $arr_tmp as $key => $keyword_element ) {
			$keyword_element = str_replace( [ '"', "'" ], '', $keyword_element );

			if ( strlen( $keyword_element ) > 3 ) {
				$arr_keyword_pattern[] = "/\p{L}*" . preg_quote( $keyword_element ) . "\p{L}*/ui";
			} else {
				$arr_keyword_pattern[] = "/\b" . preg_quote( $keyword_element ) . "\b/ui";
			}
		}

		return preg_replace( $arr_keyword_pattern, $regex_replacement, $text_to_highlight, - 1, $text_replaced_count );
	}

	/**
	 * Shorten a text with highlighted keywords and some words around it
	 *
	 * @param $text_to_highlight
	 * @param null $search_query
	 * @param int $character_count
	 * @param string $str_ellipsis
	 * @param string $regex_replacement
	 *
	 * @return string
	 */
	public static function get_keyword_highlighted_text( $text_to_highlight, $search_query = null, $character_count = 36, $str_ellipsis = ' ... ', $regex_replacement = "<i class='found-text'>$0</i>", &$text_replaced_count = 0 ) {

		$search_query      = trim( $search_query );
		$text_to_highlight = preg_replace( '/[\s]+/', ' ', $text_to_highlight );

		$arr_tmp = array_unique( preg_split( '/\s+/', $search_query ) );

		$arr_keyword_pattern   = [];
		$arr_keyword_pattern[] = "/\p{L}*?" . preg_quote( implode( ' ', $arr_tmp ) ) . "\p{L}*/i";
		foreach ( $arr_tmp as $key => $keyword_element ) {
			$keyword_element = str_replace( [ '"', "'" ], '', $keyword_element );

			if ( strlen( $keyword_element ) > 3 ) {
				$arr_keyword_pattern[] = "/\p{L}*" . preg_quote( $keyword_element ) . "\p{L}*/ui";
			} else {
				$arr_keyword_pattern[] = "/\b" . preg_quote( $keyword_element ) . "\b/ui";
			}
		}

		$arr_text_to_return = [];

		$text_replaced_count = 0;
		foreach ( $arr_tmp as $index_arr => $search_term ) {
			if ( preg_match( '/[\s].{1,' . $character_count . '}(' . $search_term . ').{1,' . $character_count . '}[\s]/is', $text_to_highlight, $match ) ) {
				$text_with_keyword = $match[0];
			} else {
				$text_with_keyword = '';
			}

			$text_replaced_count_tmp = 0;
			if ( $tmp_text = preg_replace( $arr_keyword_pattern, $regex_replacement, $text_with_keyword, - 1, $text_replaced_count_tmp ) ) {
				$arr_text_to_return[] = $tmp_text;
				$text_replaced_count  += $text_replaced_count_tmp;
			}
		}

		return ! empty( $arr_text_to_return ) ? $str_ellipsis . implode( $str_ellipsis, $arr_text_to_return ) . $str_ellipsis : $str_ellipsis;
	}

	/**
	 * Escape a rich text field entered from Admin
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function esc_editor_field( $content ) {
		$content = static::get_post_content( $content );

		return wp_kses_post( $content );
	}

	/**
	 * Handle HTML Text to produce a safe rich text
	 *
	 * @param $html_text
	 *
	 * @return string
	 */
	public function process_html( $html_text ) {
		$content = wp_kses( $html_text, $GLOBALS['allowedposttags'] );

		/**
		 * Filters the post content.
		 *
		 * @since 0.71
		 *
		 * @param string $content Content of the current post.
		 */
		$content = apply_filters( 'the_content', $content );

		return str_replace( ']]>', ']]&gt;', $content );
	}

	/**
	 * Handle HTML Text to produce a safe rich text inside an <a> tag
	 *
	 * @param $html_text
	 *
	 * @return string
	 */
	public function process_html_for_a( $html_text ) {
		$allowed_tags_for_a = $GLOBALS['allowedposttags'];
		unset( $allowed_tags_for_a['a'] );

		$content = wp_kses( $html_text, $allowed_tags_for_a );

		/**
		 * Filters the post content.
		 *
		 * @since 0.71
		 *
		 * @param string $content Content of the current post.
		 */
		$content = apply_filters( 'the_content', $content );

		return str_replace( ']]>', ']]&gt;', $content );
	}

	/**
	 * Add more allowed styles
	 *
	 * @param $styles
	 *
	 * @return array
	 */
	public function add_safe_style_css( $styles ) {
		$styles[] = 'display';
		$styles[] = 'position';
		$styles[] = 'z-index';
		$styles[] = 'top';
		$styles[] = 'left';
		$styles[] = 'right';
		$styles[] = 'bottom';
		$styles[] = 'margin-top';
		$styles[] = 'margin-left';
		$styles[] = 'margin-bottom';
		$styles[] = 'margin-right';

		return $styles;
	}
}
