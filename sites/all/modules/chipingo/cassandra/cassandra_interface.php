<?php
/**
 * @file
 * Cassandra spesific data structures and functions
 */

use Cassandra\Request\Request;

class Cassandra {
  
  /**
   * Cassandra nodes' ip addresses
   * 
   * @var array Ip address in string
   */
  private static $nodes = [
    '192.168.56.200',     
    '192.168.56.202',
  ];
  
  /**
  * Initialize a new cassandra connection and returns correspondence \Cassandra\Connection object
  * 
  * @return \Cassandra\Connection
  */
  private static function _getConnection() {    
    $conn = new \Cassandra\Connection( static::$nodes );
    $conn->connect();
    $conn->setConsistency( Request::CONSISTENCY_QUORUM );
    return $conn;
  }

  /**
  * Disconnect $conn connection 
  * 
  * @param Cassandra\Connection $conn
  */
  public static function disConnect( $conn ) {
    $conn->disconnect();
  }
  
  /**
  * Checks and creates "chipingo" keyspace 
  *  
  * @param $conn
  *    Cassandra\Connection
  */
  public static function createSchema( $conn, $keyspaceName ) {

    $cql =  "create keyspace if not exists \"" . $keyspaceName . "\" " . 
            "   WITH REPLICATION = { 'class' : 'NetworkTopologyStrategy','DC1' : 2 }";

    $conn->querySync( $cql );  
  }
  
  /**
  * sets connection's default key space
  * 
  * @param $conn
  *    Cassandra\Connection $conn
  * 
  * @param $schemaName
  *    keyspace name to be set 
  */
  public static function setDefaultShema($conn, $schemaName) {
    $conn->querySync( 'use ' . $schemaName );
  }
 
  /**
  * Looks for a $tableAndIndexName to check whether it is exists or not
  * 
  * @param type $conn
  * @param type $tableName
  * @param type $tableAndIndexName
  * @return integer If it is exists, returns 1, otherwise 0
  */
  public static function indexExists( $conn, $tableName, $indexName ) {
    $cql =  "select count(*) " .
            "from system.\"IndexInfo\" " .
            "where " .
            "  table_name = 'chipingo' and " .
            "  index_name = '" . $tableName . '.' . $indexName . "';";
    $response = $conn->querySync( $cql );
    $row = $response->fetchRow(); 
    return (int) $row['count'];
  }
 
  /**
   * Checks and creates "chipingo" keyspace 
   *  
   * @param Cassandra\Connection $conn
   */
  public static function createChipInGoSchema( $conn ) {
    static::createSchema($conn, 'chipingo');
  }

  /**
   * Initialize FluentCQL library with active \Cassandra\Connection object. 
   * 
   * @param Cassandra\Connection $conn
   
  public static function initializeFluentcql( $conn ) {
    FluentCQL\Table::setDefaultDbAdapter( $conn );
  }
  */
  
  /**
   * Initialize cassandra software system. 
   * Before calling any DML function, call this function.
   * After calling this method, set default schema with the command below
   *  Cassandra::setDefaultShema($conn, 'XXXXXXX');
   * 
   * @return \Cassandra\Connection 
   *    Returns initialized \Cassandra\Connection object to run queries or to disconnect
   * @see Cassandra::setDefaultShema
   */
  public static function initializeCassandraSystem() {
    // Get cassandra connection
    $conn = static::_getConnection();
    //set cassandra default keyspace
    //static::setDefaultShema($conn, 'drupal');
    return $conn;
  }
  
}



