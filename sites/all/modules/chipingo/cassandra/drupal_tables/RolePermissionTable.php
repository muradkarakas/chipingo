<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */

use Cassandra\Request\Request;

/**
 *  PURPOSE   :
 *  -----------
 *  - This table is used to Store the permissions assigned to user roles..
 *    See drupal doc for more details.
 * 
 *  - Table has 1 variation:
 *      role_permission  
 */

/**
 *   RolePermission table ORM class
 */
class RolePermissionTable extends \FluentCQL\Table{

  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);    
		$this->_primary = array( 'rid', 'permission');
    $this->_name = 'role_permission'; 
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      // don't change column order
      'rid' => Cassandra\Type\Base::INT,
      'permission' => Cassandra\Type\Base::VARCHAR,
      'module' => Cassandra\Type\Base::VARCHAR
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_role_permission();
  }
  
  /**
  * Creates "role_permission" table (if it is not exists) 
  * 
  * @param $conn Connection 
   *    Should be an instance of Cassandra\Connection
  */
  private function _create_role_permission() {
    
    $this->_primary = array( 'rid' ); //, 'permission');
    
    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS role_permission ( ". 
            "  rid int, ". 
            "  permission text, ". 
            "  module text, ". 
            "  PRIMARY KEY ((rid), permission) ". 
            ");";  
    $this->_dbAdapter->querySync( $cql );
    
    // STEP 2: secondary index creation
    $cql =  "create index if not exists role_permission_idx_permission on role_permission (permission);";
    $this->_dbAdapter->querySync( $cql );
    
    $inserts = [
           " INSERT INTO role_permission (rid, permission, module) VALUES (1, 'access comments', 'comment'); " ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (1, 'access content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (1, 'use text format filtered_html', 'filter');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (2, 'access comments', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (2, 'access content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (2, 'post comments', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (2, 'skip comment approval', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (2, 'use text format filtered_html', 'filter');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access administration menu', 'admin_menu');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access administration pages', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access comments', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access content overview', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access contextual links', 'contextual');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access dashboard', 'dashboard');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access devel information', 'devel');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access overlay', 'overlay');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access site in maintenance mode', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access site reports', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access site-wide contact form', 'contact');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access toolbar', 'toolbar');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access user contact forms', 'contact');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'access user profiles', 'user');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer actions', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer blocks', 'block');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer comments', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer contact forms', 'contact');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer content translations', 'i18n_node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer content types', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer filters', 'filter');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer image styles', 'image');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer languages', 'locale');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer menu', 'menu');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer module filter', 'module_filter');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer modules', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer nodes', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer pathauto', 'pathauto');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer permissions', 'user');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer search', 'search');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer shortcuts', 'shortcut');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer site configuration', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer software updates', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer taxonomy', 'taxonomy');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer themes', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer url aliases', 'path');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'administer users', 'user');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'block IP addresses', 'system');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'bypass node access', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'cancel account', 'user');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'change own username', 'user');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'create article content', 'node'); " ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'create page content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'create url aliases', 'path');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'customize shortcut links', 'shortcut');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'delete any article content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'delete any page content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'delete own article content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'delete own page content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'delete revisions', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'delete terms in 1', 'taxonomy');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'display admin pages in another language', 'admin_language');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'display drupal links', 'admin_menu');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'edit any article content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'edit any page content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'edit own article content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'edit own comments', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'edit own page content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'edit terms in 1', 'taxonomy');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'execute php code', 'devel');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'flush caches', 'admin_menu');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'notify of path changes', 'pathauto');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'post comments', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'revert revisions', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'search content', 'search');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'select account cancellation method', 'user');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'skip comment approval', 'comment');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'switch shortcut sets', 'shortcut');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'switch users', 'devel');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'translate admin strings', 'i18n_string');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'translate content', 'translation');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'translate interface', 'locale');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'translate user-defined strings', 'i18n_string');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'use advanced search', 'search');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'use all enabled languages', 'admin_language');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'use ctools import', 'ctools');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'use text format filtered_html', 'filter');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'use text format full_html', 'filter');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'view own unpublished content', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'view revisions', 'node');" ,
           " INSERT INTO role_permission (rid, permission, module) VALUES (3, 'view the administration theme', 'system');"
          ];
    foreach($inserts as $insert) {
      $this->_dbAdapter->querySync($insert);
    }
    
    // STEP 3: done
  }
  
  /**
   * 
   * @param array $data
   * @return array  Cassandra\Response 
   * @throws Exception
   *    $data should have at least one of the rid and permission keys
   */
  public function db_delete( $data ) {
    $this->_primary = array( 'rid', 'permission');
    
    if ( !isset($data['rid']) && !isset($data['permission']) ) {
      throw new Exception('$data should have at least one of the rid and permission keys!');
    }
    $response = parent::delete()
                  ->where('rid = ? AND permission = ?', $data['rid'], $data['permission'])
                  ->querySync();
    return $response;
  }
  
  /**
   * Creates a new row in "role_permission" table.
   * @param int $rid
   * @param text $permission
   * @param text $module
   * @return \Cassandra\Response 
   */
  public function db_insert(  $rid,
                              $permission,
                              $module
                            ) {
    // inserting into 'role_permission
    $this->_primary = array( 'rid', 'permission');
    $response = parent::insertRow( [
                'rid' => $rid,  
                'permission' => $permission,
                'module' => $module
              ] )->querySync();
        
    return $response;    
  }
  
  /**
   * Returns all rows from "role_permission" table
   * 
   * @return \SplFixedArray
   */
  public function selectAll() {
    $this->_primary = array( 'rid', 'permission');
    $response = parent::select()->querySync();
    return $response->fetchAll();      
  }
  
  /**
   * 
   * @param int $rid
   * @return stdClass
   */
  public function findByRid( $rid ) {
    $this->_primary = array( 'rid' );
    if ( is_array($rid) ) {
      $retValue = [];      
      foreach ( $rid as $one ) {
        $response = parent::find( $one )->fetchAll();
        foreach ($response as $arr) {
          $obj = new StdClass();
          $obj->rid = $arr['rid'];
          $obj->permission = $arr['permission'];
          $obj->module = $arr['module'];
          array_push($retValue, $obj ); //$this->_convertToStdClass($obj) 
        }
      }
    } else {
      $response = parent::find( $rid )->fetchAll();
      $retValue = $this->_convertToStdClass($response);
    }
    
    return $retValue;
  }
  
  
  /**
   * 
   * @param string $permission
   * @return array
   */
  public function findByPermission( $permission ) {
    $this->_primary = array( 'permission' );   
    $response = parent::find( $permission )->fetchCol(0);
    return $response->toArray();
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
  private function _convertToStdClass( $response ) {
    
    if ( is_object($response) && get_class($response) != 'SplFixedArray' ) {
      $obj = new StdClass();
      $obj->rid = $response['rid'];
      $obj->permission = $response['permission'];
      return $obj;      
    } 
    
    if ( is_array($response) || get_class($response) == 'SplFixedArray' ) {

      $retValue = array();
      foreach( $response as $row ) {
        $obj = new StdClass();
        $obj->rid = $row['rid'];
        $obj->permission = $row['permission'];
        $retValue[] = $obj;
      }
      return $retValue;
    }
    
    throw new Exception('$response is not an array or object');
  }
  
  
}