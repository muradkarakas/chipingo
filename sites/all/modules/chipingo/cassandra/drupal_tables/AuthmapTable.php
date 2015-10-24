<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */

use Cassandra\Request\Request;

/**
 *  PURPOSE   :
 *  -----------
 *  - This table is used to store distributed authentication mapping.
 *    See drupal doc for more details.
 * 
 *  - Table has 2 variations:
 *      authmap_by_authname
 *      authmap_by_uid
 *  
 * 
 */


/**
 *   Authmap table ORM class
 */
class AuthmapTable extends \FluentCQL\Table{
    
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);
    $this->_name = NULL; //has variations
    $this->_primary = NULL; //has variations authname, uid
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'authname' => Cassandra\Type\Base::VARCHAR,
      'aid' => Cassandra\Type\Base::INT,
      'uid' => Cassandra\Type\Base::INT,
      'module' => Cassandra\Type\Base::VARCHAR,
      'created' => Cassandra\Type\Base::INT,
      'changed' => Cassandra\Type\Base::INT,
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_authmap_by_authname();
    $this->_create_authmap_by_uid();
  }
  
  
  /**
   * NOT IMPLEMENTED
   * 
   * @param type $data
   */
  public function delete_all($data) {
    throw new Exception('BU SATIRI TAMAMLA - ' . __FILE__ . ' - '. __LINE__ );
  }
  
  
  /**
  * Creates "authmap_by_authname" table (if it is not exists) 
  * 
  * @param $conn Connection 
   *    Should be an instance of Cassandra\Connection
  */
  private function _create_authmap_by_authname() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS authmap_by_authname ( ". 
            "  authname text, ". 
            "  aid int, ". 
            "  uid int, ". 
            "  module text, ". 
            "  PRIMARY KEY (authname) ". 
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
  * Creates "authmap_by_uid" table (if it is not exists) 
  * 
  * @param $conn Connection 
   *    Should be an instance of Cassandra\Connection
  */
  private function _create_authmap_by_uid() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS authmap_by_uid ( ". 
            "  uid int, " . 
            "  authname text, ". 
            "  aid int, " . 
            "  module text, ". 
            "  PRIMARY KEY (uid) ". 
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: done
  }
   
  /**
   * Creates a new row in "authmap" table.
   * @param int $uid User id
   * @param text $authname See drupal doc
   * @param int $aid See drupal doc
   * @param text $module Drupal module name
   */
  public function db_insert(  $uid,
                              $authname,
                              $aid,
                              $module
                            ) {
    
    // inserting into 'authmap_by_authname'
    $this->_name = 'authmap_by_authname';
    $this->_primary = array('authname');
    parent::insertRow( [
                'authname' => $authname,  
                'aid' => $aid,
                'uid' => $uid,
                'module' => $module
              ] )->querySync();
    
    // inserting into 'authmap_by_uid'
    $this->_name = 'authmap_by_uid';
    $this->_primary = array('uid');
    parent::insertRow( [
                'authname' => $authname,  
                'aid' => $aid,
                'uid' => $uid,
                'module' => $module
              ] )->querySync();   
  }
  
  /**
   * Returns all rows from "authmap_by_uid" table
   * 
   * @return \SplFixedArray
   */
  public function selectAll() {
    this::$_name = 'authmap_by_uid';
    this::$_primary = array('uid');
    $response = parent::select()->querySync();
    return $response->fetchAll();      
  }
  
}
