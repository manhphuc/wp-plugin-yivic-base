<?php

declare(strict_types=1);

namespace Yivic_Base\App\Models;

use Yivic_Base\App\Support\Traits\Model_Multisite_Trait;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
	use Model_Multisite_Trait;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * We may want to use Wpdb_Connection for the db
	 * @var string
	 */
	protected $connection = 'mysql';

	public static function insert( ...$params ) {
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// Use wp_insert_post() instead
		throw new Exception( 'Invalid Method Call' );
	}

	public function update( array $attributes = [], array $options = [] ) {
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// Use wp_update_post() instead
		throw new Exception( 'Invalid Method Call' );
	}

	public function delete() {
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// Use wp_delete_post() instead
		throw new Exception( 'Invalid Method Call' );
	}
}