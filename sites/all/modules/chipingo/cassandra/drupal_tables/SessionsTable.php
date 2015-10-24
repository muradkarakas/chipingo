<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */

use Cassandra\Request\Request;

/**
 *  PURPOSE   :
 *  -----------
 *  - This table is used to Drupal’s session handlers read and write into the....
 *    See drupal doc for more details.
 * 
 *  - Table has 2 variations:
 *      sessions_by_sid_and_uid
 *      sessions_by_timestamp
 */

/**
 *   RoleTable table ORM class
 */
class SessionsTable extends \FluentCQL\Table{
    
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);
    $this->_name = NULL; //has 2 variations
    $this->_primary = NULL; //has 2 variations 
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;

    $this->_columns = array(
      'uid' => Cassandra\Type\Base::INT,
      'sid' => Cassandra\Type\Base::VARCHAR,
      'ssid' => Cassandra\Type\Base::VARCHAR,
      'hostname' => Cassandra\Type\Base::VARCHAR,
      'timestamp' => Cassandra\Type\Base::INT,
      'cache' => Cassandra\Type\Base::INT,
      'session' => Cassandra\Type\Base::VARCHAR,
    );
	}
  
  /**
   * Return today midnight as timestamp
   * 
   * @return int
   */
  private function _getPKey() {
    return strtotime('today midnight');
  }
  
  /**
   * Session handler assigned by session_set_save_handler().
   *
   * Cleans up stalled sessions.
   *
   * @param $time
   *   The calculation of "=REQUEST_TIME - session.gc_maxlifetime", passed by PHP.
   *   Sessions not updated for more than $lifetime seconds will be removed.
   * @see session.inc file
   */
  public function deleteByTimeStamp($time) {
    /*
    $this->_name = 'sessions_by_timestamp';
    $this->_primary = ['uid'];
    //$pkey = $this->_getPKey();
    $resultSet = parent::select()
                  ->where('uid = ? and timestamp < ?', $uid, $time)
                  ->querySync()
                  ->fetchAll();
    $this->_delete_all($resultSet);
    */
    
    // TODO MK0100 stalled session'ların silinmesi işlemi pig ile merkezi olarak yapılmalı
  }
    
  /**
   * Delete all rows in resultSet from all variations
   * 
   * @param array $resultSet
   * @see deleteBy... functions
   */
  public function _delete_all($resultSet) {
    foreach($resultSet as $sessionkey => $sessionvalue) {      
      // delete form variation 1
      if ( isset( $sessionvalue['sid'] ) ) {
        $this->_db_delete_by_sid($sessionvalue['sid']);
      }    
      // delete form variation 2
      $this->_db_delete_by_timestamp($sessionvalue);  
    }  
  }
  
  /**
   * Deletes a row from 'sessions_by_timestamp' table
   * 
   * @param int $data
   */
  public function _db_delete_by_timestamp($data) {
    $this->_name = 'sessions_by_timestamp';
    $this->_primary = ['uid'];
    parent::delete()
      ->where('uid = ? and timestamp = ?', $data['uid'], $data['timestamp'])
      ->querySync();      
  }
  
 
  /**
   * Deletes a row from 'sessions_by_sid_and_uid' table
   * 
   * @param int $sid
   */
  public function _db_delete_by_sid($sid) {
    $this->_name = 'sessions_by_sid_and_uid';
    $this->_primary = ['sid'];
    parent::delete()
        ->where('sid = ?', $sid)
        ->querySync();    
  }
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_sessions_by_timestamp();
    $this->_create_sessions_by_sid_and_uid();
    //$this->_create_sessions_by_ssid_and_uid();  
  }
  
  /**
   * Returns a row from 'sessions_by_ssid_and_uid'
   * Its a private function. Please use findBY_SID_or_SSID instead
   * 
   * NOT IMPLEMENTED
   * 
   * @param int $ssid
   * @throws Exception
   */
  private function findBy_SSID( $ssid ) {
    return $this->findBy_SID( $ssid );
    /*$this->_name = 'sessions_by_ssid_and_uid';
    $this->_primary = ['ssid'];
    $rows = (array) parent::find( $ssid )->fetchAll();
    $usersTable = new UsersTable($this->_dbAdapter);
    foreach ($rows as $key => $row) {
      $user = (array) $usersTable->loadUserById( (int) $row['uid']);      
      foreach ($user as $userkey => $uservalue) {
        foreach ($uservalue as $uservaluekey => $uservaluevalue) {
          $rows[$key][$uservaluekey] = $uservaluevalue;
        }
      }
    }*/
  }
  
  /**
   * Return row
   * 
   * @param int $sid_or_ssid
   * @return array
   * @throws Exception
   */
  public function findBY_SID_or_SSID( $sid_or_ssid ) {
    if (!isset($sid_or_ssid)) {
      throw new Exception('$sid_or_ssid parameter should be an array' . __LINE__ . '/' . __FILE__);
    }
    
    if (isset($sid_or_ssid['ssid'])) {
      $entity = $this->findBy_SSID($sid_or_ssid['ssid']);
      if (isset($entity)) {
        return $entity;
      } else {
        $sid_or_ssid['sid'] = $sid_or_ssid['ssid'];
      }
    }
    
    if (isset($sid_or_ssid['sid'])) {
      return $this->findBy_SID($sid_or_ssid['sid']);
    }
    
    throw new Exception('$sid_or_ssid parameter should have at least on of these: sid or sid' . __LINE__ . '/' . __FILE__);
  }
  
  /**
   * Returns a row from 'sessions_by_sid_and_uid' table
   * Its a private function. Please use findBY_SID_or_SSID instead
   * 
   * @param int $sid
   */
  private function findBy_SID( $sid ) {
    $this->_name = 'sessions_by_sid_and_uid';
    $this->_primary = ['sid'];
    $rows = (array) parent::find( $sid )->fetchAll();
    $usersTable = new UsersTable($this->_dbAdapter);
    foreach ($rows as $key => $row) {
      $user = (array) $usersTable->loadUserById( (int) $row['uid']);      
      foreach ($user as $userkey => $uservalue) {
        foreach ($uservalue as $uservaluekey => $uservaluevalue) {
          $rows[$key][$uservaluekey] = $uservaluevalue;
        }
      }
    }
    //throw new Exception('user objesine ait sutunları donus nesnesine ekle');
    return $rows;   
  }
 
  
  /** 
   * Ends a specific user's session(s).
   *
   * @param $uid
   *   User ID.
   * $see "drupal_session_destroy_uid($uid)" function in "session.inc"
   */
  public function deleteBy_uid($uid) {
    
    $this->_name = 'sessions_by_sid_and_uid';
    $this->_primary = array( 'sid' );
    /*$sids = parent::select()
        ->where('uid = ? ', $uid)
        ->querySync()
        ->fetchAll();*/
    $this->_delete_all($resultSet);
    /*foreach ($sids as $sid) {
      parent::delete()
          ->where('sid = ? ', $sid['sid'])
          ->querySync(); 
    } */
  }
  
  /**
  * Creates "sessions" table (if it is not exists)
  */
  private function _create_sessions_by_timestamp() {

    // STEP 1: table creation
    $cql =  "create table if not exists sessions_by_timestamp ( " .
            "  uid	int, " .  //"today_as_timestamp int, " .
            "  timestamp	int, " .
            "  sid	text, " .                      
            "  hostname	text, " .            
            "  cache	int, " .
            "  session	text, " .
            "  ssid	text, " .
            "  PRIMARY KEY ( (uid), timestamp, sid ) " .
            ");";
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation
    
    // STEP 3: done
  }
  
  /**
  * Creates "sessions_by_uid_and_sid" table (if it is not exists)
  */
  private function _create_sessions_by_sid_and_uid() {

    // STEP 1: table creation
    $cql =  "create table if not exists sessions_by_sid_and_uid ( " .
            "sid	text, " .
            "uid	int, " .            
            "hostname	text, " .
            "timestamp	int, " .
            "cache	int, " .
            "session	text, " .
            "ssid	text, " .
            "PRIMARY KEY ( (sid), uid ) " .
            ");";
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation
    /*$indexCount = Cassandra::indexExists($this->_dbAdapter, 'sessions_by_sid_and_uid', 'sessions_by_sid_and_uid_idx_uid' );
    if ( $indexCount === 0 ) {*/
      $cql =  "create index if not exists sessions_by_sid_and_uid_idx_uid on sessions_by_sid_and_uid(uid);";
      $this->_dbAdapter->querySync( $cql );
    //}
    
    // STEP 3: done
  }
    
  /**
   * Insert a new row into 'sessions_by_timestamp' table.
   * 
   * This function adds a new keyed value into $data parameter 'today_as_timestamp' 
   * as primary key. This value is used as a partion key and old session information 
   * are deleted based on range search on 'timestamp'.
   * 
   * Background info: cassandra does not support range query on primary keys like
   *   delete from sessions_by_timestamp where timestamp < 123456789;
   * So I created a dummy partion key. So, I can delete old rows with a query like that
   *   delete from sessions_by_timestamp where today_as_timestamp = XXX and timestamp < 123456789;
   * This relatively does the same thing and runs faster.
   * Some session information (for yesterday or previous days) can remain in session tables 
   * 
   *  This function should be called before _db_insert_sid
   * 
   * @param type $data
   * @see cassandra docs for range queries on primary keys
   * 
   */
  public function _db_insert_session_timestamp($data) {
    //* $pkey = $this->_getPKey();
    /* before inserting, we are checking sessions_by_timestamp table whether it has
     * same sid. if it has, we are updating timestamp column. if not, inserting
     * as a new row
     */
    $insertedRow = $this->findBY_SID_or_SSID( ['sid' => $data['sid']]);    
    $this->_name = 'sessions_by_timestamp';
    $this->_primary = array( 'uid' );  
    if (count($insertedRow)==0) {
      //* $data['today_as_timestamp'] = $pkey;
      parent::insertRow( $data )->querySync();
    } else {
      parent::delete()          
          ->where('uid = ? and timestamp = ?', $insertedRow[0]['uid'], $insertedRow[0]['timestamp'])
          ->querySync();      
      //* $data['today_as_timestamp'] = $pkey;
      parent::insertRow( $data )->querySync();
    }          
  }
  
  /**
   * Inserts a new row into 'sessions_by_sid_and_uid' table
   * 
   * @param array $data
   */
  public function _db_insert_sid( $data ) {
    $this->_name = 'sessions_by_sid_and_uid';
    $this->_primary = array( 'sid' );
    parent::insertRow( $data )->querySync();
  }
      
  /**
   * Inserts a new row into all variations
   * 
   * @param array $data
   * @throws Exception
   */
  public function db_insert_all( $data ) {
    if (!is_array($data)) {
      throw new Exception('db_insert function $data parameter should be array type');
    } 
    
    // WARNING: 
    // firstly, new row should be inserted session_by_timestamp table
    $this->_db_insert_session_timestamp($data);
    
    if ( isset( $data['sid'] ) ) {
      $this->_db_insert_sid( $data );
    }/* else {   
      if ( isset($data['ssid']) && (strlen($data['ssid'])>0)) {
        $this->_db_insert_ssid( $data );
      }    
    }*/
  }
    
  /**
   * 
   * @param array $data
   * @throws Exception
   */
  public function db_update_all( $data ) {    
    if (!is_array($data)) {
      throw new Exception('db_updade function $data parameter should be array type');
    }    
    $orjinalObject = (array) $this->loadUserById($data['uid']);
    $this->_db_update_uid($data);
    $this->_db_delete_name($orjinalObject);
    $this->_db_insert_name($data);  
  }
  
  /**
   * 
   * @param type $data
   */
  public function _db_update_uid($data) {
    $created = $data['created'];
    $uid = $data['uid'];
    unset($data['uid']);
    unset($data['created']);
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' );    
    parent::updateRow( [$uid], $data )->querySync();
    $data['uid'] = $uid;
    $data['created'] = $created;
  }
  
  
  public function _db_delete_name($data) {
    // inserting into 'users_by_name'
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name', 'status' );
    $name = $data['name'];
    $status = $data['status'];
    parent::deleteRow([$name, $status])->querySync();
  }  

}