<?php

declare(strict_types=1);

namespace Yivic_Base\Tests\Unit\Foundation\Database\Connectors;

use Yivic_Base\Foundation\Database\Connectors\Connection_Factory;
use Yivic_Base\Foundation\Database\Connectors\Wpdb_Connector;
use Yivic_Base\Foundation\Database\Wpdb_Connection;
use Yivic_Base\Tests\Support\Unit\Libs\Unit_Test_Case;
use Yivic_Base\Tests\Unit\Foundation\Database\Connectors\Connection_Factory_Test\Container_Test_Tmp;
use Yivic_Base\Tests\Unit\Foundation\Database\Connectors\Connection_Factory_Test\Container_Test_Tmp_PDO;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Database\MySqlConnection;
use InvalidArgumentException;

class Connection_Factory_Test extends Unit_Test_Case {
	protected $container;
	protected $connection_factory;
	protected $pdo_connection;

	protected function setUp(): void {
		parent::setUp();

		$this->container = new Container_Test_Tmp();
		$this->connection_factory = new Connection_Factory( $this->container );
		$this->pdo_connection = $this->getMockBuilder( Container_Test_Tmp_PDO::class )
		->disableOriginalConstructor()
		->getMock();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function test_create_connector_throws_exception_when_driver_is_empty() {
		$config = [];

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'A driver must be specified.' );

		$this->connection_factory->createConnector( $config );
	}

	public function test_create_connector_with_registered_connector() {
		$config = [
			'driver' => 'mysql',
		];

		// Invoke the createConnector method
		$connector = $this->connection_factory->createConnector( $config );

		// Assert that the result is an instance of MySqlConnector
		$this->assertInstanceOf( MySqlConnector::class, $connector );
	}

	public function test_create_connector_with_wpdb_driver() {
		$config = [
			'driver' => 'wpdb',
		];

		// Invoke the createConnector method
		$connector = $this->connection_factory->createConnector( $config );

		// Assert that the result is an instance of Wpdb_Connector
		$this->assertInstanceOf( Wpdb_Connector::class, $connector );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_create_connection_with_resolver() {
		$driver = 'mysql';
		$database = 'test';
		$prefix = 'prefix_';
		$config = [];

		// Invoke the createConnection method
		$result = $this->invoke_protected_method( $this->connection_factory, 'createConnection', [ $driver, $this->pdo_connection, $database, $prefix, $config ] );

		// Assert that the result is an instance of Connection
		$this->assertInstanceOf( Connection::class, $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_create_connection_with_wpdb_driver() {
		$driver     = 'wpdb';
		$database   = 'test';
		$prefix     = 'prefix_';
		$config     = [];

		// Mock the $wpdb global variable
		global $wpdb;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb     = new \stdClass();
		$wpdb->db = $this->pdo_connection;

		// Invoke the createConnection method
		$result = $this->invoke_protected_method( $this->connection_factory, 'createConnection', [ $driver, $wpdb->db, $database, $prefix, $config ] );

		// Assert that the result is an instance of Wpdb_Connection
		$this->assertInstanceOf( Wpdb_Connection::class, $result );
	}

	/**
	 * @throws \ReflectionException
	 */
	public function test_create_connection_with_supported_driver() {
		$driver     = 'mysql';
		$connection = 'customConnection';
		$database   = 'customDatabase';
		$prefix     = 'customPrefix';
		$config     = [ 'customConfig' ];

		// Invoke the createConnection method
		$result = $this->invoke_protected_method( $this->connection_factory, 'createConnection', [ $driver, $connection, $database, $prefix, $config ] );

		// Assert that the result is an instance of MySqlConnection
		$this->assertInstanceOf( MySqlConnection::class, $result );
	}
}

namespace Yivic_Base\Tests\Unit\Foundation\Database\Connectors\Connection_Factory_Test;

use Illuminate\Container\Container as ContainerContainer;
use PDO;

class Container_Test_Tmp extends ContainerContainer {
}

class Container_Test_Tmp_PDO extends PDO {
}
