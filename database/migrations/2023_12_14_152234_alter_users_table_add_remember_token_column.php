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
			// Temporarily disable strict mode
			DB::statement( 'SET SESSION sql_mode = ""' );

			// Convert any '0000-00-00 00:00:00' values to NULL
			DB::table( 'users' )
				->where( 'user_registered', '0000-00-00 00:00:00' )
				->update( [ 'user_registered' => null ] );

			// Re-enable strict mode
			DB::statement( 'SET SESSION sql_mode = "STRICT_ALL_TABLES"' );

			// Modify the column to be nullable and set default to null
			Schema::table(
				'users',
				function ( Blueprint $table ) {
					$table->dateTime( 'user_registered' )->nullable()->default( null )->change();

					// Add remember_token column if it doesn't exist
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
		if ( Schema::hasTable( 'users' ) ) {
			Schema::table(
				'users',
				function ( Blueprint $table ) {
					// Revert the user_registered column to not nullable if needed
					$table->dateTime( 'user_registered' )->nullable( false )->change();

					// Remove the remember_token column if it exists
					if ( Schema::hasColumn( 'users', 'remember_token' ) ) {
						$table->dropColumn( 'remember_token' );
					}
				}
			);
		}
	}
};