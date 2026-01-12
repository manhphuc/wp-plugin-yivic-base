<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::dropIfExists( 'wp_app_sessions' );
		if ( ! Schema::hasTable( 'wp_app_sessions' ) ) {
			Schema::create(
				'wp_app_sessions',
				function ( Blueprint $table ) {
					$table->string( 'id' )->primary();
					$table->foreignId( 'user_id' )->nullable()->index();
					$table->string( 'ip_address', 45 )->nullable();
					$table->text( 'user_agent' )->nullable();
					$table->text( 'payload' );
					$table->integer( 'last_activity' )->index();
					$table->text( 'activities' )->nullable();
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
		Schema::dropIfExists( 'wp_app_sessions' );
	}
};