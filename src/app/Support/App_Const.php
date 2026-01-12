<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support;

class App_Const {
	const ACTION_WP_APP_LOADED = 'yivic_base_wp_app_loaded';
	const ACTION_WP_APP_REGISTERED = 'yivic_base_wp_app_registered';
	const ACTION_WP_APP_BOOTED = 'yivic_base_wp_app_booted';
	const ACTION_WP_APP_BOOTSTRAP = 'yivic_base_wp_app_bootstrap';
	const ACTION_WP_APP_REGISTER_ROUTES = 'yivic_base_wp_app_register_routes';
	const ACTION_WP_API_REGISTER_ROUTES = 'yivic_base_wp_api_register_routes';
	const ACTION_WP_APP_INIT = 'yivic_base_wp_app_init';
	const ACTION_WP_APP_COMPLETE_EXECUTION = 'yivic_base_wp_app_complete_execution';
	const ACTION_WP_APP_WEB_WORKER = 'yivic_base_wp_app_web_worker';
	const ACTION_WP_APP_SCHEDULE_RUN = 'yivic_base_wp_app_schedule_run';
	const ACTION_WP_APP_SETUP_APP = 'yivic_base_wp_app_setup_app';
	const ACTION_WP_APP_MARK_SETUP_APP_DONE = 'yivic_base_wp_app_mark_setup_app_done';
	const ACTION_WP_APP_MARK_SETUP_APP_FAILED = 'yivic_base_wp_app_mark_setup_app_failed';
	const ACTION_WP_APP_BROADCAST_CHANNELS = 'yivic_base_wp_app_broadcast_channels';
	const ACTION_WP_APP_AUTH_BOOT = 'yivic_base_wp_app_auth_boot';

	const FILTER_WP_APP_PREPARE_CONFIG = 'yivic_base_wp_app_prepare_config';
	const FILTER_WP_APP_MAIN_SERVICE_PROVIDERS = 'yivic_base_wp_app_main_service_providers';
	const FILTER_WP_APP_APP_CONFIG = 'yivic_base_wp_app_app_config';
	const FILTER_WP_APP_AUTH_CONFIG = 'yivic_base_wp_app_auth_config';
	const FILTER_WP_APP_BROADCASTING_CONFIG = 'yivic_base_wp_app_broadcasting_config';
	const FILTER_WP_APP_CACHE_CONFIG = 'yivic_base_wp_app_cache_config';
	const FILTER_WP_APP_DATABASE_CONFIG = 'yivic_base_wp_app_database_config';
	const FILTER_WP_APP_FILESYSTEMS_CONFIG = 'yivic_base_wp_app_filsystems_config';
	const FILTER_WP_APP_HASHING_CONFIG = 'yivic_base_wp_app_hashing_config';
	const FILTER_WP_APP_LOGGING_CONFIG = 'yivic_base_wp_app_logging_config';
	const FILTER_WP_APP_MAIL_CONFIG = 'yivic_base_wp_app_mail_config';
	const FILTER_WP_APP_QUEUE_CONFIG = 'yivic_base_wp_app_queue_config';
	const FILTER_WP_APP_SESSION_CONFIG = 'yivic_base_wp_app_session_config';
	const FILTER_WP_APP_TELESCOPE_CONFIG = 'yivic_base_wp_app_telescope_config';
	const FILTER_WP_APP_TINKER_CONFIG = 'yivic_base_wp_app_tinker_config';
	const FILTER_WP_APP_VIEW_CONFIG = 'yivic_base_wp_app_view_config';
	const FILTER_WP_APP_PASSPORT_CONFIG = 'yivic_base_wp_app_passport_config';
	const FILTER_WP_APP_WEB_PAGE_TITLE = 'yivic_base_wp_app_web_page_title';
	const FILTER_WP_APP_CHECK = 'yivic_base_wp_app_check';

	const QUEUE_HIGH = 'high';
	const QUEUE_DEFAULT = 'default';
	const QUEUE_LOW = 'low';
	const QUEUE_BACKOFF = 'queue_backoff';

	const USER_META_CLIENT_CREDENTIALS_APP_ID = 'client_credentials_app_id';
	const USER_META_CLIENT_CREDENTIALS_APP_SECRET = 'client_credentials_app_secret';

	const OPTION_VERSION = '_yivic_base_version';
	const OPTION_SETUP_INFO = '_yivic_base_setup_info';
}