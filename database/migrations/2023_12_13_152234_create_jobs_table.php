<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists( 'wp_app_jobs' );
		if ( ! Schema::hasTable( 'wp_app_jobs' ) ) {
			Schema::create(
				'wp_app_jobs',
				function ( Blueprint $table ) {
					$table->charset = defined( 'DB_CHARSET' ) && DB_CHARSET ? DB_CHARSET : 'utf8mb4';

					$table->bigIncrements( 'id' );
					$table->uuid( 'uuid' )->nullable();
					$table->string( 'queue' )->index();
					$table->longText( 'payload' );
					$table->unsignedTinyInteger( 'attempts' );
					$table->unsignedInteger( 'reserved_at' )->nullable();
					$table->unsignedInteger( 'available_at' );
					$table->unsignedInteger( 'created_at' );
				}
			);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'wp_app_jobs' );
	}
};
