<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  PURPOSE   :
 *  -----------
 *  - This table is used to Stores user data.
 *    See drupal doc for more details.
 * 
 *  - Table has 2 variation:
 *      users_by_uid
 *      users_by_name  
 */

/**
 *   RoleTable table ORM class
 */
class CountersTable extends \FluentCQL\Table {
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);
    
    $this->_name = 'counters'; // has no variations 
    $this->_primary = array( 'key_column_name' );
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'key_column_name' => Cassandra\Type\Base::VARCHAR,
      'counter_value' => Cassandra\Type\Base::INT
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_counters();
    
    // we are incresing counter in order not to produce IDs with same value for 
    // built-in drupal users and roles
    $this->getNextUserId();
    $this->getNextUserId();
    $this->getNextUserId();
    
    $this->getNextRoleId();
    $this->getNextRoleId();
    $this->getNextRoleId();
  }
  
  /**
  * Creates "counters" table (if it is not exists) 
  */
  private function _create_counters() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS counters ( " .
            "  key_column_name text, " .
            "  counter_value counter, " .
            "  PRIMARY KEY (key_column_name) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
   * Returns a new (unique) user id
   * 
   * @return int
   */
  public function getNextUserId() {
    return $this->_getNextId( 'user_id' );
  }
  
  /**
   * Returns a new (unique) role id
   * 
   * @return int
   */
  public function getNextRoleId() {
    return $this->_getNextId( 'user_role_id' );
  }
  
  /**
   * Returns next id for the $keyColumnName.
   * This function is called other public "getNext....Id()" function. 
   * Use them.
   *
   * @param text $keyColumnName    
   * @throws Exception
   *    $keyColumnName should have a string value!
   * @return int
   */
  private function _getNextId( $keyColumnName ) {
    if ( empty($keyColumnName) ) {
      throw new Exception('$keyColumnName should have a string value!');
    }
    parent::update()
        ->set('counter_value = counter_value + 1' )
        ->where('key_column_name = ? ', $keyColumnName )
        ->querySync();
    $id = parent::find( $keyColumnName )->fetchRow();
    return $id['counter_value'];      
  }
   

}