<?php

namespace Nstaeger\CmsPluginFramework\Broker\Wordpress;

use Nstaeger\CmsPluginFramework\Broker\DatabaseBroker;
use wpdb;

class WordpressDatabaseBroker implements DatabaseBroker
{
	/**
	 * @var wpdb
	 */
	private $databaseConnection;

	public function __construct() {
		$this->databaseConnection = $GLOBALS['wpdb'];
	}

	public function delete( $table, array $where ) {
		return $this->databaseConnection->delete( $this->parsePrefix( $table ), $where );
	}

	public function executePreparedQuery( $query, $args ) {
		$prepared = $this->databaseConnection->prepare( $query, $args );
		$parsed = $this->parsePrefix( $prepared );

		return $this->databaseConnection->query( $parsed );
	}

	public function executeQuery( $query ) {
		$parsed = $this->parsePrefix( $query );

		return $this->databaseConnection->query( $parsed );
	}

	public function fetchAll( $query ) {
		return $this->databaseConnection->get_results( $this->parsePrefix( $query ), ARRAY_A );
	}

	public function insert( $table, array $data ) {
		return $this->databaseConnection->insert( $this->parsePrefix( $table ), $data );
	}

	public function update( $table, array $data, array $where ) {
		return $this->databaseConnection->update( $this->parsePrefix( $table ), $data, $where );
	}

	private function parsePrefix( $query ) {
		// Use base_prefix instead of prefix
		return str_replace( '@@', $this->databaseConnection->base_prefix, $query );
	}
}
