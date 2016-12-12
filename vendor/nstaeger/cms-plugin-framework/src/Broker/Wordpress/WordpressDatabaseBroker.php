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

    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['wpdb'];
    }

    public function delete($table, array $where)
    {
    	// $deleted = $this->databaseConnection->delete($this->parsePrefix($table), $where);
     //    if ( false === $deleted ) {
     //        _log( $deleted );
     //    } else {
     //        _log( $deleted );
     //        global $wpdb;
     //        _log( $wpdb->last_query );
     //    }
        return $this->databaseConnection->delete($this->parsePrefix($table), $where);
    }

    public function executePreparedQuery($query, $args)
    {
        $prepared = $this->databaseConnection->prepare($query, $args);
        $parsed = $this->parsePrefix($prepared);

        return $this->databaseConnection->query($parsed);
    }

    public function executeQuery($query)
    {
        $parsed = $this->parsePrefix($query);

        return $this->databaseConnection->query($parsed);
    }

    public function fetchAll($query)
    {
        return $this->databaseConnection->get_results($this->parsePrefix($query), ARRAY_A);
    }

    public function insert($table, array $data)
    {
        return $this->databaseConnection->insert($this->parsePrefix($table), $data);
    }

    public function update($table, array $data, array $where ) {

        // $updated = $this->databaseConnection->update($this->parsePrefix($table), $data, $where );
        // if ( false === $updated ) {
        //     _log( $updated );
        // } else {
        //     _log( $updated );
        //     global $wpdb;
        //     _log( $wpdb->last_query );
        // }
        return $this->databaseConnection->update($this->parsePrefix($table), $data, $where );
    }

    private function parsePrefix($query)
    {
        return str_replace("@@", $this->databaseConnection->prefix, $query);
    }
}
