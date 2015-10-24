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
function chipingo_menu_items() {
  $items = [];
  
  // Lists chipingos
  $items['publisher_chipingo'] = array(
    'title' => t('Publisher & ChipInGo Settings'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array( 'publisher_chipingo_settings_block_form_wrapper' ),
    'description' => t('Publisher & ChipInGo'),
    'access callback' => TRUE,
    'menu_name' => ChipInGoConstants::$MENU_NAME,
    'access callback' => 'chipingo_access',
    'access arguments' => ['publisher_chipingo'],
    'file' => 'menus_and_forms/content/chipingo/menu_forms.php',
    'weight' => 500
  ); 
  
  return $items;
}


