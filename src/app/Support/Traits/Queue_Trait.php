<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support\Traits;

use Yivic_Base\App\Support\App_Const;
use Illuminate\Foundation\Bus\PendingDispatch;

trait Queue_Trait {
	private function get_site_database_queue_connection() {
		return 'database';
	}

	private function get_site_default_queue() {
		$site_id = get_current_blog_id();

		return 'default_queue_for_site_' . $site_id;
	}

	private function get_queue_backoff() {
		return apply_filters( App_Const::QUEUE_BACKOFF, 420 );
	}

	/**
	 *
	 * @param PendingDispatch $job
	 * @return PendingDispatch
	 */
	private function enqueue_job( PendingDispatch $job ) {
		return $job->onConnection( $this->get_site_database_queue_connection() )->onQueue( $this->get_site_default_queue() );
	}

	/**
	 *
	 * @param PendingDispatch $job
	 * @return PendingDispatch
	 */
	private function enqueue_job_later( PendingDispatch $job ) {
		return $this->enqueue_job( $job )->delay( now()->addMinutes( 7 ) );
	}
}
