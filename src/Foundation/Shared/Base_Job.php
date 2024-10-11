<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Shared;

use Yivic_Base\Foundation\Shared\Interfaces\Job_Interface;

abstract class Base_Job implements Job_Interface {

	protected $site_id;

	public function __construct() {
		$this->site_id = get_current_blog_id();
	}

	public function before_handle() {
		// We don't want to hanlde the job if it's for different Site
		//  we simple put it back to the queue
		if ( $this->site_id && $this->site_id !== get_current_blog_id() ) {
			if ( method_exists( $this, 'release' ) ) {
				$this->release( 490 );
			}
		}
	}
}
