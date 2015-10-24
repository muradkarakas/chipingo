<?php

/**
 * 
 */
function _cassandra_drupal_reinstall() {
  $shemaName = 'drupal';
  
  // Get cassandra connection
  $conn = Cassandra::initializeCassandraSystem();
  
  // create cassandra drupal keyspace
  Cassandra::createSchema($conn, $shemaName);  
  
  //set cassandra default keyspace
  Cassandra::setDefaultShema($conn, $shemaName);
 
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
	drupal_set_message('Drupal cassandra database objects are being created...');
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
  
  _create_drupal_database_objects($conn); 
  
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
  drupal_set_message('Drupal cassandra database objects have been created successfully' );
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
  
  Cassandra::disconnect($conn);
}

/**
 * Creates chipingo db tables. Called by chipingo_install hook.
 * 
 * @param \Cassandra\Connection $conn
 */
function _create_chipingo_database_objects($conn) {
  // create chipingo table
  $ChipingoTable = new ChipingoTable($conn);
  $ChipingoTable->create_db_objects();
  drupal_set_message('ChipingoTable created successfully.');
  
  $qtagTable = new QTagTable($conn);
  $qtagTable->create_db_objects();
  drupal_set_message('QTagTable created successfully.');  
  
  $sessionTable = new SessionTable($conn);
  $sessionTable->create_db_objects();
  drupal_set_message('SessionTable created successfully.'); 
  
  $searchPathsTable = new SearchPathsTable($conn);
  $searchPathsTable->create_db_objects();
  drupal_set_message('SearchPathsTable created successfully.'); 
  
  $qTagOptionsTable = new QTagOptionsTable($conn);
  $qTagOptionsTable->create_db_objects();
  drupal_set_message('QTagOptionsTable created successfully.');
  
  $favorites = new UserFavoritesTable($conn);
  $favorites->create_db_objects();
  drupal_set_message('UserFavoritesTable created successfully.');
  
  $QTagRepliesTable = new QTagRepliesTable($conn);
  $QTagRepliesTable->create_db_objects();
  drupal_set_message('QTagRepliesTable created successfully.');
}

/**
 * Creates drupal user managament db tables. Called by chipingo_install hook.
 * 
 * @param \Cassandra\Connection $conn
 */
function _create_drupal_database_objects($conn) {
   
  $countersTable = new CountersTable($conn);
  $countersTable->create_db_objects();
  drupal_set_message('CountersTable created successfully.');
  
  $AuthmapTable = new AuthmapTable($conn);
  $AuthmapTable->create_db_objects();
  drupal_set_message('AuthmapTable created successfully.');
  
  $RolePermissionTable = new RolePermissionTable($conn);
  $RolePermissionTable->create_db_objects();
  drupal_set_message('RolePermissionTable created successfully.');
  
  $RoleTable = new RoleTable($conn);
  $RoleTable->create_db_objects();
  drupal_set_message('RoleTable created successfully.');

  $UsersTable = new UsersTable($conn);
  $UsersTable->create_db_objects();
  drupal_set_message('UserTable created successfully.');
  
  $sessionsTable = new SessionsTable($conn);
  $sessionsTable->create_db_objects();
  drupal_set_message('SessionsTable created successfully.');
  
  $UsersRolesTable = new UsersRolesTable($conn);
  $UsersRolesTable->create_db_objects($conn);  
  drupal_set_message('UsersRolesTable created successfully.'); 
  
}