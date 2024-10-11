<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Actions\Get_WP_App_Info_Action;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @method static function exec(): void
 */
class Put_Setup_Error_Message_To_Log_File_Action extends Base_Action {
	use Executable_Trait;

	protected $message;

	public function __construct( $message ) {
		$this->message = $message;
	}

	public function handle() {
		$monolog = new Logger( 'setup_app' );
		$stream_handler = new StreamHandler( storage_path( 'logs/setup-app.log' ) );
		$monolog->pushHandler( $stream_handler );
		$monolog->warning( '========= Errors from Setup app ============' );
		$monolog->error( $this->message );

		/** @var array $wp_app_info */
		$wp_app_info = Get_WP_App_Info_Action::exec();
		$monolog->info( devvard( $wp_app_info ) );
		$monolog->info( devvard( get_loaded_extensions() ) );
		$monolog->warning( '========= /Errors from Setup app ===========' );
	}
}
