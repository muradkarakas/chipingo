<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  PURPOSE   :
 *  -----------
 *  - This table is used to Stores user roles..
 *    See drupal doc for more details.
 * 
 *  - Table has 2 variation:
 *      role_by_rid
 *      role_by_name  
 */

/**
 *   RoleTable table ORM class
 */
class RoleTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);
    $this->_name = NULL; //has variations
    $this->_primary = NULL; //has variations rid, name
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'rid' => Cassandra\Type\Base::INT,
      'name' => Cassandra\Type\Base::VARCHAR,
      'weight' => Cassandra\Type\Base::INT
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_role_by_rid();
    $this->_create_role_by_name();
    
    /*
    *  Built-in roles.
    */
    $this->db_insert_all( 
                          [
                            'rid' => 1,
                            'name' => 'anonymous user', 
                            'weight' => 0
                          ] 
                        );
  
    $this->db_insert_all( 
                          [
                            'rid' => 2,
                            'name' => 'authenticated user', 
                            'weight' => 1
                          ] 
                        );
  
    $this->db_insert_all( 
                          [
                            'rid' => 3,
                            'name' => 'administrator', 
                            'weight' => 3
                          ] 
                        );
  }
  
  /**
  * Creates "role_by_name" table (if it is not exists) 
  * 
  * @param $conn Connection 
   *    Should be an instance of Cassandra\Connection
  */
  private function _create_role_by_name() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS role_by_name ( ". 
            "  name text, ". 
            "  rid int, ". 
            "  weight int, ". 
            "  PRIMARY KEY (name) ". 
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation

    // STEP 3: done
  }
     

  /**
  * Creates "role_by_rid" table (if it is not exists) 
  * 
  * @param $conn Connection 
   *    Should be an instance of Cassandra\Connection
  */
  private function _create_role_by_rid() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS role_by_rid ( ". 
            "  rid int, ". 
            "  name text, ". 
            "  weight int, ". 
            "  PRIMARY KEY (rid) ". 
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
   * Converts $response to StdClass object. 
   * If it is object or it is an array of rows, return it as StdClass, otherwise
   * raise exception 
   * 
   * @param \Cassandra\Response $response
   * @return \StdClass
   * @throws Exception
   *    $response is not an array or object
   */
  public function convertToStdClass( $response ) {  
    
    if ( ! is_object($response) ) {      
      $obj = new stdClass();
      $obj->rid = $response['rid'];
      $obj->weight = $response['weight'];
      $obj->name = $response['name'];
      return $obj;      
    } else {      
      $retValue = array();
      foreach( $response as $row ) {
        $obj = new StdClass();
        $obj->rid = $row['rid'];
        $obj->weight = $row['weight'];
        $obj->name = $row['name'];
        $retValue[] = $obj;
      }
      return $retValue;
      
    }
    
    throw new Exception('$response is not an array or object');
  }
  
  /**
   * Returns all rows from "role_by_rid" table
   * 
   * @return array of StdClass
   */
  public function selectAll() {
    $this->_name = 'role_by_rid';
    $this->_primary = array( 'rid' );
    $response = parent::select()->querySync()->fetchAll();
    $retValue = $this->convertToStdClass($response);
    return $retValue;      
  }
  
  public function selectAllExceptAnonymous() {
    $result = $this->selectAll();
    foreach ($result as $key => $value) {
      switch ($value->rid) {
        // We are looking for anonymous role
        case DRUPAL_ANONYMOUS_RID:
          unset( $result[$key] );
          break;
      }
    }
    return $result;
  }
  
  /**
   * Finds and returns the row as array object
   * 
   * @param array $rid
   *    
   * @return array
   */
  public function findByRid( $rid ) {
    $this->_name = 'role_by_rid';
    $this->_primary = array( 'rid' ); 
    $response = parent::find( $rid )->fetchRow();
    //$retValue = $this->_convertToStdClass($response);
    return $response;
  }
  
  /**
   * Finds and returns the row as StdClass object
   * 
   * @param array $rid
   *    
   * @return stdClass
   */
  public function findByRid_asStdClass( $rid ) {
    $response = $this->findByRid($rid);
    $retValue = $this->convertToStdClass($response);
    return $retValue;
  }
 
  /**
   * Finds and returns the row as StdClass object
   * 
   * @param array $name
   *    
   * @return stdClass
   */
  public function findByName( $name ) {
    $this->_name = 'role_by_name';
    $this->_primary = array( 'name' ); 
    $response = parent::find( $name )->fetchRow();
    if (is_null($response)) {
      // there is no role in db
      return FALSE;
    }
    $retValue = $this->_convertToStdClass($response);
    return $retValue;
  }
  
  /**
   * Creates a new row both in "role_by_name" and "role_by_rid" table.
   * 
   * @param array $data
   *    Keys will be used as column names, values will be used as a column data
   * @throws Exception
   *    Parameter should be array
   */
  public function db_insert_all( $data ) {
    
    if (!\is_array($data)) {
      throw new Exception('db_insert $data parameter should be array type');
    }    
    // inserting into 'role_by_name'
    $this->_name = 'role_by_name';
    $this->_primary = array( 'name' );
    parent::insertRow( $data )->querySync();    
    // inserting into 'role_by_rid'
    $this->_name = 'role_by_rid';
    $this->_primary = array( 'rid' );
    parent::insertRow( $data )->querySync();
  }
  
  /**
   * Creates a new row both in "role_by_name" and "role_by_rid" table.
   * 
   * @param array $data
   *    Keys will be used as column names, values will be used as a column data
   * @throws Exception
   *    Parameter should be array
   */
  public function db_update_all( $data ) {
    
    if (!\is_array($data)) {
      throw new Exception('db_insert $data parameter should be array type');
    }    
    
    if ( !isset($data['rid']) || !isset($data['name']) ) {
      throw new Exception('db_update $data parameter should have both rid aname values');
    }    
    
    //throw new Exception('Role adı değiştiğinde "users_roles" tablosundaki "role_name" adlarını da update etmelisin');
    
    // before updating row, getting orjinal 'name' column value
    // we will use it to delete and insert with new 
    // name into 'role_by_name' table
    
    $row_orjinal = $this->convertToStdClass( 
        $this->findByRid( (int) $data['rid'] ) 
    );
    
    // updating 'role_by_rid' table
    $this->_name = 'role_by_rid';
    $this->_primary = array( 'rid' );
    $key = (int) $data['rid'];
    unset($data['rid']);
    parent::updateRow( [ $key ], $data )->querySync();
        
    // we cannot update primary key in 'role_by_name',
    // so we should delete it first, then insert it again with new name column value
    $this->_name = 'role_by_name';
    $this->_primary = array( 'name' );
    parent::deleteRow( $row_orjinal->name )->querySync();      
    // inserting as a new row    
    $data['rid'] = $key;
    $this->insertRow($data)->querySync();
    
    // we should update all table contains role name
    $usersRolesTable = new UsersRolesTable($this->_dbAdapter);
    $usersRolesTable->updateRoleNameByRid($data['rid'], $data['name']);
  }
  
  /**
   * Delete role from database
   * 
   * @param array $data
   *    $data should contain both 'rid' and 'name' keys
   * @return array  Cassandra\Response 
   * @throws Exception
   *    $data should have rid and name key !
   */
  public function db_delete( $data ) {
    
    if ( !isset($data['name']) || !isset($data['rid']) ) {
      throw new Exception('$data should have rid and name keys !');
    }
    
    // inserting into 'role_by_name'
    $this->_name = 'role_by_name';
    $this->_primary = array( 'name' );
    parent::deleteRow( $data['name'] )->querySync();
    
    // inserting into 'role_by_rid'
    $this->_name = 'role_by_rid';
    $this->_primary = array( 'rid' );
    parent::deleteRow( $data['rid'] )->querySync();
  }
  
  /**
   * Creates a new row both in "role_by_name" and "role_by_rid" table.
   * @param int $rid
   * @param text $name
   * @param int $weight
   * @return \Cassandra\Response 
   */
  public function db_insert1( $rid,
                              $name,
                              $weight
                            ) {
    
    // inserting into 'role_by_name'
    this::$_name = 'role_by_name';
    this::$_primary = array( 'name' );
    $response = parent::insertRow( [
                'rid' => $rid,  
                'name' => $name,
                'weight' => $weight
              ] )->querySync();
    
    // inserting into 'role_by_rid'
    this::$_name = 'role_by_rid';
    this::$_primary = array( 'rid' );
    $response = parent::insertRow( [
                'rid' => $rid,  
                'name' => $name,
                'weight' => $weight
              ] )->querySync();
        
    return $response;    
  }
  
  
  
  
  
}
