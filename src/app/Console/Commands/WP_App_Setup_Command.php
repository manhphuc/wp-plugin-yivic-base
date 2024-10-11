<?php

declare(strict_types=1);

namespace Yivic_Base\App\Console\Commands;

use Yivic_Base\App\Actions\Setup_WP_App_In_Console_Action;
use Illuminate\Console\Command;

class WP_App_Setup_Command extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wp-app:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup all needed things for the WP Application';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle() {
		Setup_WP_App_In_Console_Action::exec( $this );
	}
}
