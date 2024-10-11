<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Database;

use Closure;
use Exception;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

class Wpdb_Connection extends MySqlConnection {
	/** @var \wpdb $wpdb */
	protected $wpdb;

	/**
	 * Create a new database connection instance.
	 *
	 * @param  \PDO|\Closure  $pdo
	 * @param  string  $database
	 * @param  string  $tablePrefix
	 * @param  array  $config
	 * @return void
	 */
	public function __construct( $pdo, $database = '', $tablePrefix = '', array $config = [] ) {
		parent::__construct( $pdo, $database, $tablePrefix, $config );

		$this->wpdb = ! empty( $config['wpdb'] ) ? $config['wpdb'] : $GLOBALS['wpdb'];
	}

	/**
	 * Run a select statement against the database.
	 *
	 * @param  string  $query
	 * @param  array  $bindings
	 * @param  bool  $useReadPdo
	 * @return array
	 */
	public function select( $query, $bindings = [], $useReadPdo = true ) {
		// TODO: we need to use the $wpdb to query queries later & fix phpcs rule WordPress.DB.PreparedSQL.NotPrepared
		return $this->run(
			$query,
			$bindings,
			function ( $query, $bindings ) use ( $useReadPdo ) {
				list($unbound_query, $to_be_bound, $new_bindings) = $this->convert_query_with_namespace( $query, $bindings );
				if ( ! empty( $to_be_bound ) ) {
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared_query = $this->wpdb->prepare( $unbound_query, $new_bindings );
				} else {
					$prepared_query = $query;
				}

				list($unbound_query, $to_be_bound, $new_bindings) = $this->convert_query_with_question_marks( $query, $bindings );
				if ( ! empty( $to_be_bound ) ) {
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared_query = $this->wpdb->prepare( $unbound_query, $new_bindings );
				} else {
					$prepared_query = $query;
				}
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$result = $this->wpdb->get_results( $prepared_query );

				return $result;
			}
		);
	}

	/**
	 * Convert the PDO unbound (namespace) query to an unprepared wbdb query
	 * e.g. select * from `wps_posts` where `wps_posts`.`id` = :id and status = :id
	 *      and abc = :name and xyz = :name and desc LIKE :desc limit 1
	 *  would be converted to
	 *      select * from `wps_posts` where `wps_posts`.`id` = %d and status = %d
	 *      and abc = %s and xyz = %s and desc LIKE %s limit 1
	 *  (with the proper to be bound values and binding values)
	 *
	 * @param mixed $query
	 * @param array $bindings
	 * @return array 3 items: new unprepared wbdb query, array of to be bound values
	 *                  and binding values
	 */
	protected function convert_query_with_namespace( $query, $bindings = [] ): array {
		$new_bind_strings = [];
		$new_bindings = [];
		$new_query = $query;

		$new_query = preg_replace_callback(
			'/\:(\w+)/',
			function ( array $matches ) use ( $bindings, &$new_bind_strings, &$new_bindings ) {
				$binding_value = $bindings[ $matches[1] ];
				$new_bind_strings[] = $this->determine_wbdb_bound_string( $binding_value );
				$new_bindings[] = $binding_value;
				return $this->determine_wbdb_bound_string( $binding_value );
			},
			$query
		);

		return [ $new_query, $new_bind_strings, $new_bindings ];
	}

	/**
	 * Convert the PDO unbound (question marks) query to an unprepared wbdb query
	 * e.g. select * from `wps_posts` where `wps_posts`.`id` = ? and status = ?
	 *      and desc LIKE ? limit 1
	 *  would be converted to
	 *      select * from `wps_posts` where `wps_posts`.`id` = %d and status = %d
	 *      and desc LIKE %s limit 1
	 *  (with the proper to be bound values and binding values)
	 *
	 * @param mixed $query
	 * @param array $bindings
	 * @return array 3 items: new unprepared wbdb query, array of to be bound values
	 *                  and binding values
	 */
	protected function convert_query_with_question_marks( $query, $bindings = [] ): array {
		$new_bind_strings = [];
		$new_bindings = [];
		$new_query = $query;
		if ( is_array( $bindings ) && ! empty( $bindings ) ) {
			foreach ( $bindings as $tmp_index => $binding_value ) {
				if ( is_int( $tmp_index ) ) {
					$tmp_index = (int) $tmp_index;
					$new_bindings[ $tmp_index ] = $binding_value;
					$new_bind_strings[ $tmp_index ] = $this->determine_wbdb_bound_string( $binding_value );
					$new_query = preg_replace( '/\?/', (string) $new_bind_strings[ $tmp_index ], $new_query, 1 );
				}
			}
		}

		return [ $new_query, $new_bind_strings, $new_bindings ];
	}

	/**
	 * Produce the string to be used for wpdb to bind the values to wbdb query
	 * @param mixed $value
	 * @return string   %d for integer
	 *                  %f for float
	 *                  %s for string
	 *                  %i for identifiers
	 */
	protected function determine_wbdb_bound_string( $value ): string {
		if ( is_int( $value ) ) {
			return '%d';
		}

		if ( is_float( $value ) ) {
			return '%f';
		}

		if ( is_string( $value ) ) {
			return '%s';
		}

		return '%i';
	}
}
