<?php

declare(strict_types=1);

namespace Yivic_Base\Foundation\Database\Connectors;

use Yivic_Base\Foundation\Database\Wpdb_Connection;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use InvalidArgumentException;

class Connection_Factory extends ConnectionFactory {
	/**
	 * Create a connector instance based on the configuration.
	 *
	 * @param  array  $config
	 * @return \Illuminate\Database\Connectors\ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createConnector( array $config ) {
		if ( ! isset( $config['driver'] ) ) {
			throw new InvalidArgumentException( 'A driver must be specified.' );
		}

		$key = "db.connector.{$config['driver']}";

		if ( $this->container->bound( $key ) ) {
			return $this->container->make( $key );
		}

		if ( $config['driver'] === 'wpdb' ) {
			return new Wpdb_Connector();
		}

		return parent::createConnector( $config );
	}

	/**
	 * Create a new connection instance.
	 *
	 * @param  string  $driver
	 * @param  \PDO|\Closure  $connection
	 * @param  string  $database
	 * @param  string  $prefix
	 * @param  array  $config
	 * @return \Illuminate\Database\Connection
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createConnection( $driver, $connection, $database, $prefix = '', array $config = [] ) {
		$resolver = Connection::getResolver( $driver );
		if ( $resolver ) {
			return $resolver( $connection, $database, $prefix, $config );
		}

		if ( $driver === 'wpdb' ) {
			return new Wpdb_Connection( $connection, $database, $prefix, $config );
		}

		return parent::createConnection( $driver, $connection, $database, $prefix, $config );
	}
}