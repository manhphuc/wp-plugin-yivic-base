<?php

declare(strict_types=1);

namespace Yivic_Base\App\Models;

use Yivic_Base\App\Support\Traits\Model_Multisite_Trait;
use Illuminate\Database\Eloquent\Model;

class Postmeta extends Model {
	use Model_Multisite_Trait;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'postmeta';

	/**
	 * We may want to use Wpdb_Connection for the db
	 * @var string
	 */
	protected $connection = 'mysql';
}
