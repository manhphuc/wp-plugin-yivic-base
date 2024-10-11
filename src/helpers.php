<?php

declare(strict_types=1);

use Yivic_Base\App\Support\Yivic_Base_Helper;

if ( ! function_exists( 'yivic_base_wp_app_web_page_title' ) ) {
	function yivic_base_wp_app_web_page_title() {
		return Yivic_Base_Helper::wp_app_web_page_title();
	}
}
