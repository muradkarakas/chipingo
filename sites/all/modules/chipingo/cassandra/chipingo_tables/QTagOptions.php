<?php

/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 1 variation
 *  --------------------------
 *    qtag_options
 */

/**
 *   Chipingo table ORM class
 */
class QTagOptionsTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = 'qtag_options'; //has 1 variation
    $this->_primary = ['chipingo_email','qtag','session_name']; // has 1 variation
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'chipingo_email' => Cassandra\Type\Base::VARCHAR,
      'qtag' => Cassandra\Type\Base::VARCHAR,
      'session_name' => Cassandra\Type\Base::VARCHAR,
      'option_timestamp' => Cassandra\Type\Base::INT,
      'option' => Cassandra\Type\Base::VARCHAR,
      'reply_count' => Cassandra\Type\Base::INT,
      'user_id' => Cassandra\Type\Base::INT,
      'option_owner_category' => Cassandra\Type\Base::VARCHAR, // 'P'=>Publisher, 'O'=>Others
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_qtag_options();
  }
  
  /**
  * Creates "qtag_options" table (if it is not exists) 
  */
  private function _create_qtag_options() {

    // STEP 1: table creation
    $cql =  "create table if not exists qtag_options (  " .
            " chipingo_email text,  " .
            " qtag text,  " .
            " session_name text,  " .    
            " option_timestamp int, " .
            " option text, " .
            " user_id int, " .
            " reply_count bigint, " .
            " option_owner_category text, " . //
            " primary key ( (chipingo_email, qtag, session_name), option_timestamp) " .  
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
    if (!isset($data['option'])) {
      throw new Exception('option value required');
    }
    if (!isset($data['user_id'])) {
      throw new Exception('user_id value required');
    }
    // defaults
    if (!isset($data['option_owner_category'])) {
      $chipingoTable = new ChipingoTable($this->_dbAdapter);
      $chipingo = $chipingoTable->getChipingoByChipingo($data['chipingo_email']);
      $data['option_owner_category'] = ($chipingo['user_id'] == $data['user_id']) ? 'P':'O';
    }
    
    $this->_db_insert_qtag_options($data); 
  }
  
    /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_qtag_options($data) {
    //$this->_name = 'qtag_by_uid'; 
    //$this->_primary = ['user_id']; 
    parent::insertRow($data)->querySync();
  }
  
  /**
 * Returns Options of a chipingo as array
 * Array does not contain primary columns
 * $data should contains these primary keys and their's values: $chipingo_email, $qtag, $session_name
 * @param array $data
 *    $data should contains these primary keys and their's values: 
 *          $chipingo_email, $qtag, $session_name
 * 
 * @return array
 */
  public function getOptionsByChipingo($data) {
    $this->_name = 'qtag_options'; 
    $this->_primary = ['chipingo_email','qtag','session_name']; 
    $response = parent::select()
        ->where('chipingo_email = ? and qtag = ? and session_name = ?', 
            $data['chipingo_email'], 
            $data['qtag'],
            $data['session_name']
        )->querySync();        
    $resultSet = $response->fetchAll();
   
    // sorting by REPLY_COUNT in desc order
    $newResultSet = __sortMultiDimensinalDBResultSet( $resultSet,
                                                      'reply_count',
                                                      SORT_DESC,
                                                      'option_timestamp',
                                                      SORT_ASC
                                                    );
    foreach($newResultSet as $key => $record) {
      $newResultSet[$key]['option_long'] = $this->_getOptionLabel($newResultSet[$key]['option'], $newResultSet[$key]['reply_count']);
    }
      
    return $newResultSet;
  }
  
  /**
  * 
  * @param text $option
  * @param text $reply_count
  * @return text
  */
  private function _getOptionLabel($option, $reply_count) {
    $reply_count = (isset($reply_count)) ? $reply_count : 0;
    $label =  '<table class="option-bar-graph">'.
              '<tr>'. 
              '<td class="option-bar-graph-left">' . 
              str_pad($reply_count, $reply_count, "*", STR_PAD_LEFT) . 
              '</td>'. 
              '<td class="option-bar-graph-right">' . 
                $option . 
              '</td>' .
              '</tr>' .
              '</table>' .
              '';
    //$label = $label . $option;
    //$label = $label . ' (' . $reply_count . ' ' . t('times chosen') . ')';
    return $label;
  }

  /**
   * Deletes a new row from all variations
   * should contain "uid" and "chipingo"
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_delete_all($data) {    
    $this->_db_delete_qtag_options($data); 
  }
  
  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_qtag_options($data) {
    //$this->_name = 'chipingo_by_userid'; 
    //$this->_primary = ['user_id', 'chipingo_email']; 
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
  
}

