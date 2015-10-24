<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 2 variations
 *  --------------------------
 *    qtag_by_uid
 *    qtag_by_chipingo
 */

/**
 *   Chipingo table ORM class
 */
class QTagTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = NULL; //has variations
    $this->_primary = NULL; // has variations
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'qtag' => Cassandra\Type\Base::VARCHAR,
      'chipingo_email' => Cassandra\Type\Base::VARCHAR,
      'user_id' => Cassandra\Type\Base::INT,
      'question' => Cassandra\Type\Base::VARCHAR,
      'created' => Cassandra\Type\Base::INT,
      'last_session_name_used' => Cassandra\Type\Base::VARCHAR,
    );
	}
  
  public function getColumns() {
    return $this->_columns;
  }
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_qtag_by_uid();
    $this->_create_qtag_by_chipingo();
  }
  
  /**
  * Creates "qtag_by_uid" table (if it is not exists) 
  */
  private function _create_qtag_by_uid() {

    // STEP 1: table creation
    $cql =  "create table if not exists qtag_by_uid ( " .
            "  user_id int, " .     
            "  chipingo_email text, " .
            "  qtag text," .           
            "  question text, " .
            "  changed int, " .
            "  last_session_name_used text " .
            "  primary key ((user_id), chipingo_email, qtag ) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: insert built-in rows
    
    // STEP 4: done
  }
  
  /**
  * Creates "qtag_by_chipingo" table (if it is not exists) 
  * 
  */
  private function _create_qtag_by_chipingo() {

    // STEP 1: table creation
    $cql =  "create table if not exists qtag_by_chipingo ( " .
            "  chipingo_email text, " .
            "  qtag text," .            
            "  user_id int, " .            
            "  question text, " .
            "  changed int, " .
            "  last_session_name_used text " .
            "  primary key ((chipingo_email), qtag) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: done
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
    if (!isset($data['qtag'])) {
      throw new Exception('qtag value required');
    }
    // defaults
    
    $this->_db_insert_qtag_by_uid($data);   
    $this->_db_insert_qtag_by_chipingo($data);   
  }
  
  /**
   * 
   * @param type $chipingo
   * @param type $qtag
   */
  public function getQTag($chipingo_email, $qtag) {
    $this->_name = 'qtag_by_chipingo'; 
    $this->_primary = ['chipingo_email']; 
    $response = parent::select()
        ->where('chipingo_email = ? and qtag = ?', $chipingo_email, $qtag)
        ->querySync();    
    $result = $response->fetchAll();
    return $result;
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_qtag_by_uid($data) {
    $this->_name = 'qtag_by_uid'; 
    $this->_primary = ['user_id']; 
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_qtag_by_chipingo($data) {
    $this->_name = 'qtag_by_chipingo'; 
    $this->_primary = ['chipingo_email']; 
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Deletes a new row from all variations
   * should contain "uid" and "chipingo"
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_delete_all($data) {    
    $this->_db_delete_qtag_by_uid($data);   
    $this->_db_delete_qtag_by_chipingo($data);   
  }
  
  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_qtag_by_uid($data) {
    $this->_name = 'qtag_by_uid'; 
    $this->_primary = ['user_id', 'chipingo_email', 'qtag']; 
    parent::deleteRow( [ $data['user_id'], $data['chipingo_email'], $data['qtag']] )
        ->querySync();
  }

  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_qtag_by_chipingo($data) {
    $this->_name = 'qtag_by_chipingo'; 
    $this->_primary = ['chipingo_email', 'qtag']; 
    parent::deleteRow( [ $data['chipingo_email'], $data['qtag'] ] )
        ->querySync();
  }

  
  /**
   * Returns a user's qtag count
   * 
   * @param int $uid
   */
  public function getUserChipingoQTagCount($user_id, $chipingo_email) {
    $this->_name = 'qtag_by_uid';
    $this->_primary = [ 'user_id' ]; 
    $response = parent::select('count(*)')
        ->where('user_id = ? and chipingo_email = ?', $user_id, $chipingo_email)
        ->querySync();    
    $result = $response->fetchOne();
    return (int) $result; 
  }
 

  
}
