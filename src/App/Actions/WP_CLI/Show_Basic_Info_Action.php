<?php

declare(strict_types=1);

namespace Yivic_Base\App\Actions\WP_CLI;

use Yivic_Base\App\Actions\Get_WP_App_Info_Action;
use Yivic_Base\Foundation\Actions\Base_Action;
use Yivic_Base\Foundation\Support\Executable_Trait;
use WP_CLI;

/**
 * @method static function exec(): void
 */
class Show_Basic_Info_Action extends Base_Action {
	use Executable_Trait;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		/** @var array $wp_app_info */
		$wp_app_info = Get_WP_App_Info_Action::exec();

		foreach ( $wp_app_info as $info_key => $info_value ) {
			WP_CLI::success( "Key $info_key: " . $info_value );
		}

		// Exit 0 for telling that the command is a successful one
		$this->exit_with_status( 0 );
	}

	protected function exit_with_status( int $status ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Status code exit does not need escaping
		exit( $status );
	}
}
