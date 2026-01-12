<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\WP;

interface WP_Theme_Interface {
	public const PARAM_KEY_THEME_SLUG = 'theme_slug';
	public const PARAM_KEY_THEME_BASE_PATH = 'base_path';
	public const PARAM_KEY_THEME_BASE_URL = 'base_url';
	public const PARAM_KEY_PARENT_THEME_BASE_PATH = 'parent_base_path';
	public const PARAM_KEY_PARENT_THEME_BASE_URL = 'parent_base_url';

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register();

	/**
	 * We want to use this method to register and deregister all hooks and related things to be used in the plugin
	 * @return void
	 */
	public function manipulate_hooks(): void;

	/**
	 * The plugin should have a human-readable name
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * The plugin should have a version for being tracked
	 * @return string
	 */
	public function get_version(): string;
}