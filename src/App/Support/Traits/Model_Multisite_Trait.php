<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support\Traits;

trait Model_Multisite_Trait {
	/**
	 * Scope a query to only include popular users.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function scopeSite( $query, int $site_id ) {
		if ( $site_id > 1 ) {
			$new_table = $site_id . '_' . $query->getModel()->getTable();
			$query->getModel()->setTable( $new_table );
		}

		return $query;
	}
}