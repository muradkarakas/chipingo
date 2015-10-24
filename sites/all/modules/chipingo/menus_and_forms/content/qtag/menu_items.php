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
function qtag_menu_items() {
  $items = [];
  
  // Lists chipingos
  $items[ 'yourqtags/add' ] = array(
    'title' => t('Add a new Question'),
    'page callback' => 'qtag_form_wrapper',
    'page arguments' => array( 2 ),
    'description' => t('Add a new Question'),
    'access callback' => 'chipingo_access',
    'access arguments' => ['yourqtags/add'],
    'file' => 'menus_and_forms/content/qtag/menu_forms.php',
    'weight' => 100,
    'menu_name' => ChipInGoConstants::$MENU_NAME,
	);
    
  $items[ 'yourqtags/edit' ] = array(
    'page callback' => 'qtag_form_wrapper',
    'page arguments' => array( 2, 3, 4 ),  //   chipingo, qtag, page_name, session_name
    'access callback' => TRUE,
    'weight' => 50,
    'access callback' => 'chipingo_access',
    'file' => 'menus_and_forms/content/qtag/menu_forms.php',
    'access arguments' => ['yourqtags/edit'],
  );
  
  return $items;
}


