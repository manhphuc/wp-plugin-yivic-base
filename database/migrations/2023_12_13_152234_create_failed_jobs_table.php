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
		Schema::dropIfExists( 'wp_app_failed_jobs' );
		Schema::create(
			'wp_app_failed_jobs',
			function ( Blueprint $table ) {
				$table->charset = defined( 'DB_CHARSET' ) && DB_CHARSET ? DB_CHARSET : 'utf8mb4';

				$table->bigIncrements( 'id' );
				$table->uuid( 'uuid' )->nullable();
				$table->text( 'connection' );
				$table->text( 'queue' );
				$table->longText( 'payload' );
				$table->longText( 'exception' );
				$table->timestamp( 'failed_at' )->useCurrent();

				$table->unique( 'uuid' );
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'wp_app_failed_jobs' );
	}
};