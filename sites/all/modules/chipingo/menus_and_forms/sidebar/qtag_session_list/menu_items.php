<?php
/**
 * $file
 * 
 *  Menu file
 *  Create a function and return drupal menu item array
 * 
 * @see menu_forms.php
 * @see api.php
 * @see menu_items.php
 */
//*****************************************************************************************


/**
 * Application hook_menu items for chipingos
 * 
 * @see hook_menu
 */
function session_menu_items() {
  $items = [];

  // Creating a new Session entity
  $items['session/add'] = array(
    'title' => t('Create a new Session'),
    'page callback' => 'session_form_wrapper',
    'file' => 'menus_and_forms/content/session/menu_forms.php',
    'page arguments' => array( 1, 2, 3 ),	//  $op, $chipingo_email, $qtag
    'access callback' => TRUE
  );

  $items['session/edit'] = array(
    'title' => t('Session Settings'),
    'page callback' => 'session_form_wrapper',  
    'page arguments' => array( 1, 2, 3 ,4 ),	//  $op, $chipingo_email, $qtag, $session_name
    'access callback' => TRUE,
    'file' => 'menus_and_forms/content/session/menu_forms.php',
  );	
  
  $items['session/delete'] = array(
    'title' => t('Session Settings'),
    'page callback' => 'session_form_wrapper',  
    'page arguments' => array( 1, 2, 3 ,4 ),	//  $op, $chipingo_email, $qtag, $session_name
    'access callback' => TRUE,
    'file' => 'menus_and_forms/content/session/menu_forms.php',
  );	

  $items['session/publish'] = array(
    'title' => t('Session Settings'),
    'page callback' => 'session_form_wrapper',  
    'page arguments' => array( 1, 2, 3 ,4 ),	//  $op, $chipingo_email, $qtag, $session_name
    'access callback' => TRUE,
    'file' => 'menus_and_forms/content/session/menu_forms.php',
  );
  
  return $items;
}


