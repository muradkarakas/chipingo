<?php

/**
 * @param callback // default
 * @param username
 * @param password
 * @param rememberme  optional. if it is set, its value will be passed to 
 * callback function as a key named 'rememberme', if not, value will be set as 'OFF'
 * 
 * @return 
 *  login
 *    'OK' -> login successfull
 *    'NO' -> login unsuccessfull. 
 *  error
 *    if any type of error is occured, reason will be in this parameter.
 */
try {
  
  

  $data = [];

  // check parameters
  if ( empty($_GET['username']) ) {
    $data = ['error' => 'User name is missing']; 
  }
  if ( empty($_GET['password']) ) {
    $data = ['error' => 'Password is missing']; 
  }
  if (count($data)>0) {
    echo "jsonpCallback(".json_encode( $data ).")";
    exit();
  }
  
  // loading user from cassandra
  set_include_path('.;C:\Calismalarim\wamp\www\chipingo\sites\all\modules\chipingo\cassandra\\;C:\\Calismalarim\\wamp\\www\\chipingo\\includes\\');
  require_once 'php-cassandra_includes.php';
  require_once 'password.inc';
  require_once 'cassandra_interface.php';
  $conn = Cassandra::initializeCassandraSystem();  
  
  $usersTable = new UsersTable($conn);    
  $conditions = [ 'name' => $_GET['username'] ];
  $accounts = $usersTable->loadUsers([], $conditions);  
  if (count($accounts) != 1) {
    $data = ['login' => 'NO', 'error' => 'Password and/or user name are incorrect'];
  } else {
    $user = reset($accounts);
    if (user_check_password($_GET['password'], $user)) {
          $data = ['login' => 'OK'];
          if (! empty($_GET['rememberme'])) {
            $data['rememberme'] = $_GET['rememberme'];
          } else {
            $data['rememberme'] = 'false';
          }
    } else {
      $data = ['login' => 'NO', 'error' => 'Password and/or user name are incorrect'];
    }
  }
  header('Content-type: application/x-javascript');
  echo "jsonpCallback(".json_encode( $data ).")";
  Cassandra::disConnect($conn); 
} catch (Exception $ex) {
  Cassandra::disConnect($conn);
  $data = ['error' => 'User name is missing'];
  echo "jsonpCallback(".json_encode( $ex->getMessage() ).")";
}