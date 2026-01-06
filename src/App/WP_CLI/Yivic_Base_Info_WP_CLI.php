<?php

declare(strict_types=1);

namespace Yivic_Base\App\WP_CLI;

use Yivic_Base\App\Actions\WP_CLI\Show_Basic_Info_Action;

class Yivic_Base_Info_WP_CLI {
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function __invoke( $args ) {
		Show_Basic_Info_Action::exec();

		// Return 0 to tell that everything is alright
		exit( 0 );
	}
}
