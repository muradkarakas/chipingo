<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  PURPOSE   :
 *  -----------
 *  - This table is used to Maps users to roles..
 *    See drupal doc for more details.
 * 
 *  - Table has 1 variation:  
 */

/**
 *   RoleTable table ORM class
 */
class UsersRolesTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);
    $this->_name = 'users_roles'; //has 1 variation
    $this->_primary = array('uid','rid'); //has 1 variation 
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'uid' => Cassandra\Type\Base::INT,
      'user_name' => Cassandra\Type\Base::VARCHAR, 
      'rid' => Cassandra\Type\Base::INT,
      'role_name' => Cassandra\Type\Base::VARCHAR
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_users_roles();    
    $this->db_insert_all( [
                            'uid' => 1, 
                            'rid' => 3,
                            'user_name' => 'admin',
                            'role_name' => 'administrator'
                          ]
                        );
  }
  
  /**
   * Updates role_name column of all rows equal to $rid parameter value.
   * 
   * @param int $rid
   * @param text $role_name
   */
  public function updateRoleNameByRid($rid, $role_name) {
    $resultSet = $this->findByRid($rid);
    foreach ($resultSet as $row) {
      $query = parent::update();
      $query = $query->set('role_name = ?', $role_name ); 
      $query = $query->where('uid = ? and rid = ?', (int) $row['uid'], (int) $rid ); 
      $query = $query->ifExists();
      $query->assemble();
      $response = $query->querySync();
    }  
  }
  
  /**
   * Updates user_name column of all rows equal to $uid parameter value.
   * 
   * @param int $uid
   * @param text $user_name
   */
  public function updateUserNameByUid($uid, $user_name) {
    $resultSet = $this->findByUid($uid);
    foreach ($resultSet as $row) {
      $query = parent::update();
      $query = $query->set('user_name = ?', $user_name ); 
      $query = $query->where('uid = ? and rid = ?', (int) $row['uid'], (int) $row['rid'] ); 
      $query = $query->ifExists();
      $query->assemble();
      $response = $query->querySync();
    }  
  }
  
  /**
   * Returns user's roles
   * 
   * @param int or array $uid
   * @return array
   */
  public function findByRid($rids) {
    
    if ( ! is_array($rids)) {
      return $this->_findByRid($rids);
    } else {
      $return = [];
      foreach ($rids as $rid) {        
        $return = $return + $this->_findByRid($rid);
      }
      return $return;
    }    
  }
  
  /**
  * Creates "users_roles" table (if it is not exists) 
  * 
  * @param $conn Connection 
   *    Should be an instance of Cassandra\Connection
  */
  private function _create_users_roles() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS users_roles ( ". 
            "   uid int, " .
            "   rid int, " .
            "   user_name text, " .
            "   role_name text, " .
            "   PRIMARY KEY ( (uid, rid) ) " . 
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation
    /*$indexCount = Cassandra::indexExists($this->_dbAdapter, 'users_roles', 'users_roles_idx_uid' );
    if ( $indexCount === 0 ) {*/
      $cql =  "create index if not exists users_roles_idx_uid on users_roles (uid);";
      $this->_dbAdapter->querySync( $cql );
    //}
        
    $indexCount = Cassandra::indexExists($this->_dbAdapter, 'users_roles', 'users_roles_idx_rid' );
    if ( $indexCount === 0 ) {
      $cql =  "create index if not exists users_roles_idx_rid on users_roles (rid);";
      $this->_dbAdapter->querySync( $cql );
    }
    // STEP 3: done
  }
     
  /**
   * Creates a new row in "user_rows".
   * 
   * @param array $data
   *    Keys will be used as column names, values will be used as a column data
   * @return \Cassandra\Response 
   * @throws Exception
   *    Parameter should be array
   */ 
  
  public function db_insert_all( $data ) {
    if (!is_array($data)) {
      throw new Exception('db_insert $data parameter should be array type');
    }
    // inserting into 'users_roles'
    $response = parent::insertRow( $data )->querySync();        
    return $response;    
  }
 
  /**
   * Deletes user's roles
   * 
   * @param int $uid
   */
  public function deleteByUid($uid) {
    $resultSet = $this->findByUid($uid);
    foreach ($resultSet as $row) {
      $query = parent::delete();
      $query = $query->where('uid = ? and rid = ?', (int) $row['uid'], (int) $row['rid'] ); 
      $query->assemble();
      $response = $query->querySync();
    }    
  }
  
  /**
   * Returns user's roles
   * 
   * @param int or array $uid
   * @return array
   */
  public function findByUid($uids) {
    
    if ( ! is_array($uids)) {
      return $this->_findByUid($uids);
    } else {
      $return = [];
      foreach ($uids as $uid) {        
        $return = $return + $this->_findByUid($uid);
      }
      return $return;
    }    
  }
  
  /**
   * Returns user's roles
   * 
   * @param int or array $uid
   * @return array
   */
  private function _findByUid($uid) {
    $query = parent::select();
    $query = $query->where('uid = ?', (int) $uid ); 
    $query->assemble();
    $response = $query->querySync();     
    $rows = $response->fetchAll();
    return (array) $rows;
    /*
    foreach($rows as $row) {
     $return[ $row['uid'] ] = (object) $row; 
    } */ 
  }
  
  /**
   * Returns rows filtered by rid column value
   * 
   * @param int or array $rid
   * @return array
   */
  private function _findByRid($rid) {
    $query = parent::select();
    $query = $query->where('rid = ?', (int) $rid ); 
    $query->assemble();
    $response = $query->querySync();     
    $rows = $response->fetchAll();
    return (array) $rows;
  }
  
  public function db_delete_all($data) {
    return parent::deleteRow( [$data['uid'], $data['rid']] );
  }
  
  /**
   * Returns all rows from "users_roles" table
   * 
   * @return \SplFixedArray
   */
  public function selectAll() {
    $response = parent::select()->querySync();
    return $response->fetchAll();      
  }
  
}
