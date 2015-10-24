<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 2 variations
 *  --------------------------
 *    chipingo_by_userid
 *    chipingo_by_chipingo
 */

/**
 *   Chipingo table ORM class
 */
class UserFavoritesTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = NULL; //'user_favorites_by_user_id'; //has variation
    $this->_primary = []; //['user_id']; // has variation 
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'user_id' => Cassandra\Type\Base::INT,
      'chipingo_email' => Cassandra\Type\Base::VARCHAR,
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_user_favorites_by_user_id();
  }
  
  /**
  * Creates "user_favorites_by_user_id" table (if it is not exists) 
  * 
  */
  private function _create_user_favorites_by_user_id() {

    // STEP 1: table creation
    $cql =  "create table if not exists user_favorites_by_user_id ( " .
            "  user_id int, " .
            "  chipingo_email text, " .
            "  primary key ((user_id), chipingo_email) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: insert built-in rows
    
    // STEP 4: done
  }
    
  /**
   * Inserts a new row into all variations
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_insert_all($data) {    
    // mandatory values
    if (!isset($data['user_id'])) {
      throw new Exception('user_id value required');
    }
    if (!isset($data['chipingo_email'])) {
      throw new Exception('chipingo_email value required');
    }
    // defaults
    $this->_db_insert_user_favorites_by_user_id($data);  
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_user_favorites_by_user_id($data) {
    $this->_name = 'user_favorites_by_user_id'; 
    $this->_primary = ['user_id'];
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Deletes a new row from all variations
   * should contain "uid" and "chipingo"
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_delete_all($data) {    
    $this->_db_delete_user_favorites_by_user_id($data); 
  }
  
  /**
   * Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_user_favorites_by_user_id($data) {
    $this->_name = 'user_favorites_by_user_id'; 
    $this->_primary = ['user_id', 'chipingo_email']; 
    parent::deleteRow( [ $data['user_id'], $data['chipingo_email'] ] )
        ->querySync();
  }
    
  /**
   * Returns all favorites of a user
   * 
   * @param int $uid
   * @return array Chipingo list
   */
  public function getFavoritesByUid($uid) {
    $this->_name = 'user_favorites_by_user_id'; 
    $this->_primary = ['user_id'];
    $response = parent::find($uid)->fetchAll();
    return $response;
  }
  
  /**
   * 
   * @param int $user_id
   * @param text $chipingo_email
   * @return array
   */
  public function getRecord($user_id, $chipingo_email) {
    $this->_name = 'user_favorites_by_user_id'; 
    $this->_primary = ['user_id'];
    $response = parent::select()
        ->where('user_id = ? and chipingo_email = ?', $user_id, $chipingo_email)
        ->querySync();    
    $result = $response->fetchAll();
    return $result;
  }
  
}
