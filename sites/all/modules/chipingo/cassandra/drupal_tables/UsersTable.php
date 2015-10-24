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
 *  - Table has 3 variation:
 *      users_by_uid
 *      users_by_name  
 *      users_by_mail
 */

/**
 *   RoleTable table ORM class
 */
class UsersTable extends \FluentCQL\Table{
    
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'drupal');
    parent::__construct($conn);
    
    $this->_name = NULL; //has variations
    $this->_primary = NULL; //has variations 
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'uid' => Cassandra\Type\Base::INT,
      'name' => Cassandra\Type\Base::VARCHAR,
      'pass' => Cassandra\Type\Base::VARCHAR,
      'mail' => Cassandra\Type\Base::VARCHAR,
      'theme' => Cassandra\Type\Base::VARCHAR,
      'signature' => Cassandra\Type\Base::VARCHAR,
      'signature_format' => Cassandra\Type\Base::VARCHAR,
      'access' => Cassandra\Type\Base::INT,
      'login' => Cassandra\Type\Base::INT,
      'status' => Cassandra\Type\Base::INT,
      'timezone' => Cassandra\Type\Base::VARCHAR,
      'language' => Cassandra\Type\Base::VARCHAR,
      'picture' => Cassandra\Type\Base::INT,
      'init' => Cassandra\Type\Base::VARCHAR,
      'data' => Cassandra\Type\Base::BLOB,
      'created' => Cassandra\Type\Base::INT
    );
	}
  
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_users_by_uid();
    $this->_create_users_by_name();
    $this->_create_users_by_mail();
    
    /* 
    * Built-in users
    */
    $this->db_insert_all(  
                          [
                            'uid' => 0,
                            'name' => 'anonymous',
                            'status' => 0,
                            'mail' => 'no mail'
                          ]
                        );

    $this->db_insert_all(  
                          [
                            'uid' => 1,
                            'name' => 'admin',
                            'pass' => '$S$DZ8kRSxBXDkWeMSVvUKmlTEQiDp8OfBNldD4vENTIka/vruW5Fsh',
                            'mail' => 'muradkarakas@gmail.com',
                            'signature_format' => 'filtered_html',
                            'status' => 1,
                            'timezone' => 'Europe/London',
                            'language' => 'en',
                            'init' => 'muradkarakas@gmail.com',
                            'created' => REQUEST_TIME,
                            //'data' => 0x613a323a7b733a373a22636f6e74616374223b693a303b733a373a226f7665726c6179223b693a313b7d,
                           ]
                       );
  }
  
  /**
  * Creates "users_by_uid" table (if it is not exists) 
  */
  private function _create_users_by_uid() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS users_by_uid ( ". 
            "   uid int, " .
            "   name text, " .
            "   pass text, " .
            "   mail text, " .
            "   theme text, " .
            "   signature text, " .
            "   signature_format text, " .
            "   created int, " .
            "   access int, " .
            "   login int, " .
            "   status int, " .
            "   timezone text, " .
            "   language text, " .
            "   picture int, " .
            "   init text, " .
            "   data blob, " .
            "   PRIMARY KEY (uid) " . 
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
  * Creates "users_by_name" table (if it is not exists) 
  */
  private function _create_users_by_name() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS users_by_name ( ". 
            "   name text, " .
            "   uid int, " .
            "   pass text, " .
            "   mail text, " .
            "   theme text, " .
            "   signature text, " .
            "   signature_format text, " .
            "   created int, " .
            "   access int, " .
            "   login int, " .
            "   status int, " .
            "   timezone text, " .
            "   language text, " .
            "   picture int, " .
            "   init text, " .
            "   data blob, " .
            "   PRIMARY KEY ( (name), status ) " . 
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
  * Creates "users_by_mail" table (if it is not exists) 
  */
  private function _create_users_by_mail() {

    // STEP 1: table creation
    $cql =  "CREATE TABLE IF NOT EXISTS users_by_mail ( ". 
            "   uid int, " .
            "   name text, " .
            "   pass text, " .
            "   mail text, " .
            "   theme text, " .
            "   signature text, " .
            "   signature_format text, " .
            "   created int, " .
            "   access int, " .
            "   login int, " .
            "   status int, " .
            "   timezone text, " .
            "   language text, " .
            "   picture int, " .
            "   init text, " .
            "   data blob, " .
            "   PRIMARY KEY (mail) " . 
            ");";  
    $this->_dbAdapter->querySync( $cql );
   
    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
   * Updates "login" column with $loginTime of the $uid row
   * @param int $uid
   *    user uid
   * @param int $loginTime
   *    last login time
   * @see "user_login_finalize" function in "user.module" file
   */
  public function updateLastLogin($uid, $loginTime) {
    // updating variation 1
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' );
    $query = parent::update();
    $query = $query->set('login = ?', $loginTime);
    $query = $query->where('uid  = ?', (int) $uid);
    $response = $query->querySync();
  
    // getting user's database entity in order to update variation 2 
    $entity = (array) $this->loadUserById($uid);
    
    // updating variation 2
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name', 'status' );
    parent::update()
      ->set('login = ?', $loginTime )
      ->where('name  = ? and status = ?', $entity[$uid]->name, $entity[$uid]->status)
      ->querySync(); 
  }
  
  /**
   * Returns rows from variations. Both parameter should at least contain 'uid,'name' or 'mail'
   * data. If at least one of these values is exists, $conditions can contain other columns.
   * For id based search use $ids parameter, for other variations use $conditions parameter
   * 
   * @param array $ids
   * @param array $conditions
   * @return array
   * 
   * @see "load" function of "UserController" class in "user.module"
   */
  public function loadUsers($ids = [], $conditions = []) {
    $return = [];
    
    // if "ids" are given
    if ( count($ids) > 0) {
      $this->_name = 'users_by_uid';
      $this->_primary = array( 'uid' );      
      foreach ($ids as $one) {
        $query = parent::select();
        $query = $query->where('uid = ?', (int) $one ); 
        $query->assemble();
        $response = $query->querySync();     
        $rows = $response->fetchAll();
        foreach($rows as $row) {
         $return[ $row['uid'] ] = (object) $row; 
        }  
      }
    }
    
    // if "conditions" are given
    if ( count($conditions) > 0) {
              
      foreach ($conditions as $key => $value) {
        
        switch($key) {
          case 'mail':            
            $this->_name = 'users_by_mail';
            $this->_primary = array( 'mail' );
            $query = parent::select();
            $query = $query->where($key . ' = ?', $value ); 
            break;
          case 'status':            
            $this->_name = 'users_by_name';
            $this->_primary = array( 'name' );
            $query = parent::select();            
            $query = $query->where($key . ' = ?', (int) $value ); 
            $query->_appendClause('allow filtering');
            break;
          case 'name':            
            $this->_name = 'users_by_name';
            $this->_primary = array( 'name' );
            $query = parent::select();
            $query = $query->where($key . ' = ?', $value ); 
            break;
          default:
            throw new Exception('$conditions parameter should contaion at least on of these: name or mail');
            break;
        }      
        //$query->assemble();
        $response = $query->querySync();
        $rows = $response->fetchAll();
        foreach($rows as $row) {
         $return[ $row['uid'] ] = (object) $row; 
        }      
      } 
    }        
    
    // if no "$conditions" and "$ids" provided
    // all rows returned
    if ( (count($conditions)==0) && (count($ids)==0) ) {      
      $this->_name = 'users_by_uid';
      $this->_primary = array( 'uid' );      
      //foreach ($ids as $one) {
        $query = parent::select();
        //$query = $query->where('uid = ?', (int) $one ); 
        $query->assemble();
        $response = $query->querySync();     
        $rows = $response->fetchAll();
        foreach($rows as $row) {
         $return[ $row['uid'] ] = (object) $row; 
        }  
      //}
    }
    
    // loading user's roles into "roles" array
    $usersRoles = new UsersRolesTable($this->_dbAdapter);
    foreach ($return as $entity) {
      $entity->roles = [];
      // this method is called from mobile interface, so we should initialize some drupal spesific variables
      if ( !defined('DRUPAL_AUTHENTICATED_RID') ) { 
        define('DRUPAL_AUTHENTICATED_RID', 2);
      }
      $entity->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';
      $roles = $usersRoles->findByUid($entity->uid); 
      foreach($roles as $key => $value) {
        $entity->roles[$value['rid']] = $value['role_name'];
      }
    }
    
    return $return;    
  }
  
  /**
   * Reads and returns corresponding row from tha database.
   * 
   * @param string $userName
   *    user name.
   * @return object
   */
  public function getActiveUserRow($userName) {
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name' );    
    $query = parent::select()
      ->where('name = ?', $userName );    
    $query->assemble();
    $response = $query->querySync();  
    $return = (object) $response->fetchRow();
    return $return;
  }
  
  /**
  * Checks for usernames blocked by user administration.
  *
  * @param $name
  *   A string containing a name of the user.
  *
  * @return
  *   Object with property 'name' (the user name), if the user is blocked;
  *   FALSE if the user is not blocked.
  * 
  * @see "user_is_blocked" function in "user.module" file 
  */
  public function isUserBlocked($userName) {
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name' );    
    $query = parent::select('status')
      ->where('name = ?', $userName );    
    $query->assemble();
    $response = $query->querySync();     
    if ($response->fetchOne() == 1) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }
    
  /**
   * Checks if $uid or $username is exists in the user tables. 
   * If one of the parameters is found, returns TRUE, otherwise FALSE.
   * 
   * @param int $uid
   * @param text $userName
   */
  public function isUserExists($uid, $userName) {
    
    if ( isset($userName) ) {
      $return1 = $this->loadUserByName($userName);
      if ( $return1 == NULL ) {
        return FALSE;
      }
      if ($return1->name === $userName) {
        return TRUE;
      }
      return FALSE;
    }
    
    if ( isset($uid) ) {
      // !!!!!
      // this parameter not tested !!!!!!!!!!!!!
      // !!!!
      $return2 = $this->loadUserById((int) $uid);
      return ( $return2->uid === (int)$uid);
    }    
  }
  
  /**
   * Returns row data matching name column to $username parameter value
   * 
   * @param text $username
   * @return object or NULL 
   */
  public function loadUserByName($username) {
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name' );    
    $query = parent::select()
      ->where('name = ?', $username );    
    $query->assemble();
    $response = $query->querySync();     
    $resultSet = $response->fetchAll();
    return isset($resultSet[0]) ? (object) $resultSet[0] : NULL;
  }
  
  /**
   * Returns one user's row as object from database and returns. 
   * 
   * @param int $uid
   * @return object
   *    user object
   * @see loadUsers
   *  for advanced version look at loadUsers function of this class
   * 
   */
  public function loadUserById($uid) {
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' ); 
    if ( !is_array($uid)) {
      $uid = (array) $uid;
    }
    $user = (object) $this->loadUsers($uid);
    return $user;   
  }
  
  /**
   * Returns a user's row matching $mail parameter as object from database. 
   * 
   * 
   * @param text or array $mail
   * @return object
   *    user object
   * @see loadUsers
   *  for advanced version look at loadUsers function of this class
   * 
   */  
  public function loadUserByEmail($mail) {
    if ( !is_array($mail)) {
      $mail = ['mail'=>$mail];
    }
    $user = $this->loadUsers(null, $mail);
    if (count($user)>0) {
      return (object) $user;
    } else {
      return NULL;
    }
       
  }
  
  /**
   * Inserts a new row to all variations
   * 
   * @param array $data
   *    Keys will be used as column names, values will be used as a column data
   * @return \Cassandra\Response 
   * @throws Exception
   *    Parameter should be array
   */
  public function db_insert_all( $data ) {
    if (!is_array($data)) {
      throw new Exception('$data parameter should be array type');
    }
    if (! isset($data['uid']) or 
        ! isset($data['name']) ) {
      throw new Exception('$data parameter should contain uid and name');
    }
    if (! isset($data['mail']) and $data['uid'] != 0) {
      throw new Exception('$data parameter should contain mail value');
    }
    $this->_db_insert_uid( $data );
    $this->_db_insert_name( $data );
    $this->_db_insert_mail( $data );
  }
  
  /**
   * Insert a new row into users_by_uid table only.
   * If you want to insert a new row to all variations,
   * use db_insert_all function
   * 
   * @param array $data
   * @see db_insert_all
   * @see _db_insert_mail
   * @see _db_insert_name
   */
  public function _db_insert_uid( $data ) {
    // inserting into 'users_by_uid'
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' );
    parent::insertRow( $data )->querySync();
  }
  
  /**
   * Insert a new row into users_by_name table only.
   * If you want to insert a new row to all variations,
   * use db_insert_all function
   * 
   * @param array $data
   * @see db_insert_all
   * @see _db_insert_mail
   * @see _db_insert_uid
   */
  public function _db_insert_name( $data ) {
    // inserting into 'users_by_name'
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name' );
    parent::insertRow( $data )->querySync();   
  }
    
  /**
   * Insert a new row into users_by_mail table only.
   * If you want to insert a new row to all variations,
   * use db_insert_all function
   * 
   * @param array $data
   * @see db_insert_all
   * @see _db_insert_name
   * @see _db_insert_uid
   */
  public function _db_insert_mail( $data ) {
    // inserting into 'users_by_mail'
    $this->_name = 'users_by_mail';
    $this->_primary = array( 'mail' );
    parent::insertRow( $data )->querySync();   
  }
  
  
  /**
   * Update "access" column of the $uid user with $time value
   *   
   * @param int $uid
   *    user id
   * @param int $time
   *    REQUEST_TIME
   */
  public function updateLastAccessTime($uid, $time) {
    // updating variation 1
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' );
    $query = parent::update()
              ->set('access = ?', $time)
              ->where('uid  = ?', (int) $uid);
    $response = $query->querySync();
  
    // getting user's database entity in order to update variation 2 
    $entity = (array) $this->loadUserById($uid);
    
    // updating variation 2
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name', 'status' );
    parent::update()
      ->set('access = ?', $time)
      ->where('name  = ? and status = ?', $entity[$uid]->name, $entity[$uid]->status)
      ->querySync(); 
    
    // updating variation 3
    $this->_name = 'users_by_mail';
    $this->_primary = array( 'mail' );
    parent::update()
      ->set('access = ?', $time)
      ->where('mail  = ?', $entity[$uid]->mail)
      ->querySync(); 
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
    
    // variation uid
    $this->_db_update_uid($data);
    // variation name
    $this->_db_delete_name($orjinalObject[$data['uid']]->name);
    $this->_db_insert_name($data);  
    // variation mail
    $this->_db_update_mail($data);
    
    // we should update all table contains role name
    $usersRolesTable = new UsersRolesTable($this->_dbAdapter);
    $usersRolesTable->updateUserNameByUid($data['uid'], $data['name']);
  }
  
  /**
   * Updates user_by_mail table
   * 
   * @param array $data
   */
  public function _db_update_mail($data) {
    $this->_name = 'users_by_mail';
    $this->_primary = array( 'mail' );
    $mail = $data['mail'];
    unset($data['mail']);
    parent::updateRow( [$mail], $data )->querySync();
    $data['mail'] = $mail;
  }
  
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
  
  
  /**
   * Delete one row from "users_by_name" table.
   * $data parameter should be array and must contain 'name' and 'status' 
   * keyed values.
   * If you want to delete a row from all variations, use db_delete_all function
   * 
   * @param int $uid
   */
  public function _db_delete_name($name) {
    $this->_name = 'users_by_name';
    $this->_primary = array( 'name' );
    parent::deleteRow([$name])->querySync();
  }
  
  /**
   * Delete one row from "users_by_nmail" table.
   * $data parameter should be array and must contain 'mail' keyed values.
   * If you want to delete a row from all variations, use db_delete_all function
   * 
   * @param text $mail
   */
  public function _db_delete_mail($mail) {
    $this->_name = 'users_by_mail';
    $this->_primary = array( 'mail' );
    parent::deleteRow([$mail])->querySync();
  }
  
  /**
   * Delete one row from "users_by_uid" table.
   * $data parameter should be array and must contain 'name' and 'status' 
   * keyed values.
   * If you want to a row from all variations, use db_delete_all function
   * 
   * @param int $uid
   */
  public function _db_delete_uid($uid) {
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' );
    parent::deleteRow([$uid])->querySync();
  }
  
  /**
   * Deletes all users sent by $uids parameter from all variations
   * @param int $uid
   */
  public function db_delete_all($uids) {
    $arr = [];
    
    if ( ! is_array($uids)) {
      $arr[] = $uids;
    } else {
      $arr = $uids;
    }
    foreach ($arr as $each) {
      $entities = $this->loadUserById($each);
      foreach ($entities as $userkey => $uservalue) {
        //$entity = $entities[$each];
        $this->_db_delete_uid($userkey);
        $this->_db_delete_name($uservalue->name);
        $this->_db_delete_mail($uservalue->mail);
      }
    }    
  }
  
  /**
   * Returns all rows from "role_by_rid" table
   * 
   * @return \SplFixedArray
   */
  public function selectAll() {
    $this->_name = 'users_by_uid';
    $this->_primary = array( 'uid' );
    $response = parent::select()->querySync();
    return $response->fetchAll();      
  }  
  
  
  

}