<?php

/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 2 variation
 *  --------------------------
 *    qtag_replies_by_session
 *    qtag_replies_by_user_id
 */

/**
 *   Chipingo table ORM class
 */
class QTagRepliesTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = NULL; //has 2 variation
    $this->_primary = NULL; //['chipingo_email','qtag','session_name','option_timestamp']; // has 1 variation
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'chipingo_email' => Cassandra\Type\Base::VARCHAR,
      'qtag' => Cassandra\Type\Base::VARCHAR,
      'session_name' => Cassandra\Type\Base::VARCHAR,
      'option_timestamp' => Cassandra\Type\Base::INT,
      'user_id' => Cassandra\Type\Base::INT
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_qtag_replies_by_session();
    $this->_create_qtag_replies_by_user_id();
  }
  
  /**
  * Creates "qtag_replies_by_session" table (if it is not exists) 
  */
  private function _create_qtag_replies_by_session() {

    // STEP 1: table creation
    $cql =  "create table if not exists qtag_replies_by_session ( " . 
            " chipingo_email text,       " .
            " qtag text,      " .
            " session_name text, " .
            " option_timestamp int," .
            " user_id int, " .
            " primary key ( (chipingo_email, qtag, session_name, option_timestamp), user_id) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: insert built-in rows
    
    // STEP 4: done
  }
  
  private function _create_qtag_replies_by_user_id() {

    // STEP 1: table creation
    $cql =  "create table if not exists qtag_replies_by_user_id ( " . 
            " user_id int, " .
            " chipingo_email text,       " .
            " qtag text,      " .
            " session_name text, " .
            " option_timestamp int," .
            " primary key ( (user_id), chipingo_email, qtag, session_name) " .
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
    if (!isset($data['chipingo_email'])) {
      throw new Exception('chipingo_email value required');
    }
    if (!isset($data['qtag'])) {
      throw new Exception('qtag value required');
    }
    if (!isset($data['session_name'])) {
      throw new Exception('session_name value required');
    }
    if (!isset($data['option_timestamp'])) {
      throw new Exception('option_timestamp value required');
    }
    if (!isset($data['user_id'])) {
      throw new Exception('user_id value required');
    }
    // defaults
    $this->_db_insert_qtag_replies_by_session($data);
    $this->_db_insert_qtag_replies_by_user_id($data); 
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_qtag_replies_by_session($data) {
    $this->_name = 'qtag_replies_by_session'; 
    $this->_primary = ['chipingo_email','qtag','session_name','option_timestamp'];
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_qtag_replies_by_user_id($data) {
    $this->_name = 'qtag_replies_by_user_id'; 
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
    $this->_db_delete_qtag_replies_by_session($data); 
    $this->_db_delete_qtag_replies_by_user_id($data); 
  }
  
  /**
   * 
   * @param type $data
   */
  public function deleteUserOption($data) {
    
    if (!isset($data['option_timestamp'])) {
      $this->_name = 'qtag_replies_by_user_id'; 
      $this->_primary = ['user_id'];
      $response = parent::select('option_timestamp')
        ->where('user_id = ? and chipingo_email = ? and qtag = ? and session_name = ?', 
            $data['user_id'],
            $data['chipingo_email'], 
            $data['qtag'],
            $data['session_name']
        )->querySync();        
      $data['option_timestamp'] = $response->fetchOne();
    }
    $this->db_delete_all($data);
  }
  
  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_qtag_replies_by_session($data) {
    $this->_name = 'qtag_replies_by_session'; 
    $this->_primary = ['chipingo_email','qtag','session_name','option_timestamp'];
    parent::delete()
        ->where( 
              'chipingo_email = ? and ' .
              'qtag = ? and ' .
              'session_name = ? and ' . 
              'option_timestamp = ? ', 
              $data['chipingo_email'], 
              $data['qtag'], 
              $data['session_name'], 
              $data['option_timestamp'] 
        )->querySync();   
  }
  
  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_qtag_replies_by_user_id($data) {
    $this->_name = 'qtag_replies_by_user_id'; 
    $this->_primary = ['user_id'];
    parent::delete()
        ->where( 
              'user_id = ? and ' .
              'chipingo_email = ? and ' .
              'qtag = ? and ' .
              'session_name = ? ',
              $data['user_id'],
              $data['chipingo_email'], 
              $data['qtag'], 
              $data['session_name']
        )->querySync();   
  }
  
  /**
   * Returns $user_id's reply as an integer
   * 
   * @param int $user_id
   * @param array $data
   *    Should contaion following columns
   *      $chipingo_email
   *      $qtag
   *      $session_name
   * 
   * @return int
   */
  public function getUserReply($user_id, $data) {
    $this->_name = 'qtag_replies_by_user_id'; 
    $this->_primary = ['user_id'];
    $response = parent::select('option_timestamp')
        ->where('user_id = ? and chipingo_email = ? and qtag = ? and session_name = ?', 
            $user_id,
            $data['chipingo_email'], 
            $data['qtag'],
            $data['session_name']
        )->querySync();        
    $result = $response->fetchOne();
    return $result;
  }
  
  /**
  * Returns Options of a chipingo as array
  * Array does not contain primary columns
  * $data should contains these primary keys and their's values: $chipingo_email, $qtag, $session_name
  * @param array $data
  * @return array
  */ /*
  public function getOptionsByChipingo($data) {
    $response = parent::select()
        ->where('chipingo_email = ? and qtag = ? and session_name = ?', 
            $data['chipingo_email'], 
            $data['qtag'],
            $data['session_name']
        )->querySync();        
    $result = $response->fetchAll();
    return $result;
  } */
 
  
  
}




