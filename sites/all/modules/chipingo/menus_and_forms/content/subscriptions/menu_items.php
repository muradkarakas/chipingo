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
function subscriptions_menu_items() {
  $items = [];
  
  // Lists chipingos
  $items[ 'home' ] = array(
    'title' => t('Subscriptions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('user_subscriptions_form'),
    //'page arguments' => array(),
    'description' => t('Subscriptions'),
    'access callback' => 'chipingo_access',
    'access arguments' => ['chipingo/home'],
    'file' => 'menus_and_forms/content/subscriptions/menu_forms.php',
    'weight' => 100,
    'menu_name' => 'nothing',// ChipInGoConstants::$MENU_NAME,
	);
  
  $items[ 'show_alternatives' ] = array(
    'title' => t('Show Alternatives'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('show_alternatives_form'),
    //'page arguments' => array(),
    'description' => t('Show Alternatives'),
    'access callback' => 'chipingo_access',
    'access arguments' => ['show_alternaives'],
    'file' => 'menus_and_forms/content/subscriptions/menu_forms.php',
    'weight' => 100,
    'menu_name' => 'nothing', //ChipInGoConstants::$MENU_NAME,
	);
  
  return $items;
}


