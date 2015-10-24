<?php

/**
 * Application hook_menu items for chipingos
 * 
 * @see hook_menu
 */ 
function search_menu_items() {
  $items = [];
  
  // Lists chipingos
  $items[ 'searchresult' ] = array(
    'title' => t('Search Result'),
    'page callback' => 'user_search_result_form_wrapper',
    'page arguments' => array(1),
    'description' => t('Subscriptions'),
    'access callback' => 'chipingo_access',
    'access arguments' => ['chipingo/subscriptions'],
    'file' => 'menus_and_forms/content/subscriptions/menu_forms.php',
    'weight' => 100,
    'menu_name' => 'nothing', //ChipInGoConstants::$MENU_NAME,
	);
  
  return $items;
}


