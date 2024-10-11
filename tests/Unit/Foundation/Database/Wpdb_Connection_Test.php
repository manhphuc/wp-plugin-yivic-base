<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Database;

use Yivic_Base\Foundation\Database\Wpdb_Connection;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Database\Wpdb_Connection_Test\Wpdb_Connection_Test_PDO;

class Wpdb_Connection_Test extends Unit_Test_Case {

	protected $wpdb_connection;
	protected $wpdb_connection_tmp;
	protected $wpdb_mock;

	protected function setUp(): void {
		parent::setUp();

		$pdo_mock = $this->getMockBuilder( Wpdb_Connection_Test_PDO::class )
		->disableOriginalConstructor()
		->getMock();

		$config = [
			'wpdb' => 'mock_wpdb_instance',
		];

		$this->wpdb_connection = new Wpdb_Connection( $pdo_mock, 'database', 'prefix', $config );
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_constructor() {
		$property_value = $this->get_protected_property_value( $this->wpdb_connection, 'wpdb' );

		// Verify that the $wpdb property is set correctly
		$this->assertEquals( 'mock_wpdb_instance', $property_value );
	}

	public function test_convert_query_with_namespace_replaces_bindings() {
		$query = 'SELECT * FROM wp_posts WHERE id = :id AND status = :status';
		$bindings = [
			'id' => 1,
			'status' => 'published',
		];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_namespace', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts WHERE id = %d AND status = %s', $new_query );
		$this->assertSame( [ '%d', '%s' ], $new_bind_strings );
		$this->assertSame( [ 1, 'published' ], $new_bindings );
	}

	public function test_convert_query_with_namespace_handles_empty_bindings() {
		$query = 'SELECT * FROM wp_posts';
		$bindings = [];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_namespace', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts', $new_query );
		$this->assertSame( [], $new_bind_strings );
		$this->assertSame( [], $new_bindings );
	}

	public function test_convert_query_with_namespace_handles_multiple_same_bindings() {
		$query = 'SELECT * FROM wp_posts WHERE id = :id OR parent_id = :id';
		$bindings = [ 'id' => 1 ];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_namespace', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts WHERE id = %d OR parent_id = %d', $new_query );
		$this->assertSame( [ '%d', '%d' ], $new_bind_strings );
		$this->assertSame( [ 1, 1 ], $new_bindings );
	}

	public function test_convert_query_with_question_marks_replaces_bindings() {
		$query = 'SELECT * FROM wp_posts WHERE id = ? AND status = ?';
		$bindings = [ 1, 'published' ];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_question_marks', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts WHERE id = %d AND status = %s', $new_query );
		$this->assertSame( [ '%d', '%s' ], $new_bind_strings );
		$this->assertSame( [ 1, 'published' ], $new_bindings );
	}

	public function test_convert_query_with_question_marks_handles_empty_bindings() {
		$query = 'SELECT * FROM wp_posts';
		$bindings = [];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_question_marks', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts', $new_query );
		$this->assertSame( [], $new_bind_strings );
		$this->assertSame( [], $new_bindings );
	}

	public function test_convert_query_with_question_marks_handles_mixed_bindings() {
		$query = 'SELECT * FROM wp_posts WHERE id = ? AND parent_id = :parent_id';
		$bindings = [
			1,
			'parent_id' => 2,
		];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_question_marks', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts WHERE id = %d AND parent_id = :parent_id', $new_query );
		$this->assertSame( [ '%d' ], $new_bind_strings );
		$this->assertSame( [ 1 ], $new_bindings );
	}

	public function test_convert_query_with_question_marks_handles_multiple_same_bindings() {
		$query = 'SELECT * FROM wp_posts WHERE id = ? OR parent_id = ?';
		$bindings = [ 1, 2 ];

		$result = $this->invoke_protected_method( $this->wpdb_connection, 'convert_query_with_question_marks', [ $query, $bindings ] );
		list($new_query, $new_bind_strings, $new_bindings) = $result;

		$this->assertSame( 'SELECT * FROM wp_posts WHERE id = %d OR parent_id = %d', $new_query );
		$this->assertSame( [ '%d', '%d' ], $new_bind_strings );
		$this->assertSame( [ 1, 2 ], $new_bindings );
	}

	public function test_determine_wbdb_bound_string_returns_d_for_integer() {
		$result = $this->invoke_protected_method( $this->wpdb_connection, 'determine_wbdb_bound_string', [ 121212 ] );
		$this->assertSame( '%d', $result );
	}

	public function test_determine_wbdb_bound_string_returns_f_for_float() {
		$result = $this->invoke_protected_method( $this->wpdb_connection, 'determine_wbdb_bound_string', [ 123.45 ] );

		$this->assertSame( '%f', $result );
	}

	public function test_determine_wbdb_bound_string_returns_s_for_string() {
		$result = $this->invoke_protected_method( $this->wpdb_connection, 'determine_wbdb_bound_string', [ 'test string' ] );

		$this->assertSame( '%s', $result );
	}

	public function test_determine_wbdb_bound_string_returns_i_for_other_types() {
		$result = $this->invoke_protected_method( $this->wpdb_connection, 'determine_wbdb_bound_string', [ [ 'array' ] ] );

		$this->assertSame( '%i', $result );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Database\Wpdb_Connection_Test;

use PDO;

class Wpdb_Connection_Test_PDO extends PDO {
}
