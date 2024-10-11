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
		if ( Schema::hasTable( 'users' ) && Schema::hasColumn( 'users', 'user_registered' ) ) {
			Schema::table(
				'users',
				function ( Blueprint $table ) {
					$table->dateTime( 'user_registered' )->nullable()->default( null )->change();
					if ( ! Schema::hasColumn( 'users', 'remember_token' ) ) {
						$table->string( 'remember_token' )->nullable();
					}
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
		Schema::table(
			'users',
			function ( Blueprint $table ) {
				if ( Schema::hasColumn( 'users', 'remember_token' ) ) {
					$table->dropColumn( 'remember_token' );
				}
			}
		);
	}
};
