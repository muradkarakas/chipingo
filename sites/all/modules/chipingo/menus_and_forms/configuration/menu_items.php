<?php

/**
 * 
 * @return array
 */
function configuration_menu_items() {
  $items = [];
  $items['admin/config/chipingo'] = array(
    'title' => 'ChipInGo Configuration',
    'description' => 'Configuration for ChipInGo module',
    'position' => 'left',
    'weight' => -100,
    'page callback' => 'system_admin_menu_block_page',
    'access arguments' => array('administer site configuration'),
    'file' => 'system.admin.inc',
    'file path' => drupal_get_path('module', 'system'),
  );
  
  $items['admin/config/chipingo/cassandra_node_configuration'] = array(
    'title' => 'Cassandra Node Configuration',
    'description' => 'Add/delete/modify cassandra nodes on this web server',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('cassandra_node_configuration_form'), // see cassandra install
    'access arguments' => array('administer site configuration'),
    'file' => 'menus_and_forms/configuration/menu_forms.php',
    '#weight' => 10,
  );
  
  $items['admin/config/chipingo/reinstall_drupal_cassandra'] = array(
    'title' => 'Reinstall DRUPAL Cassandra Database Object',
    'description' => 'Reinstall "DRUPAL 7" cassandra user/role/permission database objects. DO NOT run this on live server !',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('cassandra_drupal_reinstall_form'), // see cassandra install
    'access arguments' => array('administer site configuration'),
    //'access arguments' => TRUE,
    'file' => 'menus_and_forms/configuration/menu_forms.php',
    '#weight' => 20,
  );
  
  $items['admin/config/chipingo/reinstall_chipingo_cassandra'] = array(
    'title' => 'Reinstall ChipInGo Cassandra Database Object',
    'description' => 'Reinstall "ChipInGO" cassandra database objects. DO NOT run this on live server !',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('cassandra_chipingo_reinstall_form'), // see cassandra install
    'access arguments' => array('administer site configuration'),
    'file' => 'menus_and_forms/configuration/menu_forms.php',
    '#weight' => 30,
  );
  return $items;
}