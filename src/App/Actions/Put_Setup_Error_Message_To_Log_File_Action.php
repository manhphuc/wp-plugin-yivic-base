<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions;

use Yivic_Base\App\Actions\Get_WP_App_Info_Action;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Put_Setup_Error_Message_To_Log_File_Action extends Base_Action {
	use Executable_Trait;

	protected $message;
	protected $logger;

	public function __construct( $message, Logger $logger = null ) {
		$this->message = $message;
		$this->logger = $logger ?? new Logger( 'setup_app' );
		$this->logger->pushHandler( new StreamHandler( storage_path( 'logs/setup-app.log' ) ) );
	}

	public function handle() {
		$this->logger->warning( '========= Errors from Setup app ============' );
		$this->logger->error( $this->message );

		$wp_app_info = Get_WP_App_Info_Action::exec();
		$this->logger->info( devvard( $wp_app_info, 5, false ) );
		$this->logger->info( devvard( get_loaded_extensions(), 5, false ) );

		$this->logger->warning( '========= /Errors from Setup app ===========' );
	}
}
