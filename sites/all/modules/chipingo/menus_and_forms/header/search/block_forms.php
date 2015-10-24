<?php

//module_load_include( 'php', 'chipingo', 'menus_and_forms/header/search/api' );

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function chipingo_search_form($form, &$form_state) {
  $current_path = current_path();
  /*if ($current_path != 'home') {
    
    return NULL;
  }*/
  //$form = [];
  $form['search-item'] = [
    '#type' => 'textfield',
    '#attributes' => array( 'placeholder' => t('Type your search text'),
                            'style' => 'margin-TOP: 7px !important;'
                          ),
    /*'#prefix' =>  '<div>' .
                  ' <table style="" cellpadding=1 cellspacing=1 border=0>' .
                  ' <tr><td style="width:99%; padding-top:15px;">',
    '#suffix' =>  ' </td>',*/
  ];
  /*$form['search-button'] = [
    '#type' => 'submit',
    '#default_value' => 'Search',
    '#description' => '',
    '#prefix' => '<td style="width:1%;">',
    '#suffix' => '</td></tr></table></div>',
  ];
   */
  return $form;
}

/**
 * 
 * @return array
 */
function user_search_result_form_wrapper($chipingo_email) {
  if (strlen($chipingo_email) == 0) {
    drupal_set_message('Parameter required', 'error');
    return [];
  }  
  
  return drupal_get_form( 'user_search_result_form', $chipingo_email);
}

/**
 * 
 * @param type $chipingo_email
 * @return type
 */
function user_search_result_form($form,&$form_state, $chipingo_email) {

  $form = [];
  $conn = Cassandra::initializeCassandraSystem();
  
  $chipingoTable = new ChipingoTable($conn);
  $result = $chipingoTable->getChipingoByChipingo($chipingo_email);
    
  $SessionTable = new SessionTable($conn);
  $qtags = (array) $SessionTable->getQTags($chipingo_email);
  
  if (count($qtags) > 0) {
    $result = $result + $qtags[0];
  }    
  $resultArray[] = $result;
  $form = $form + __showChipingoForm($resultArray, $conn);
  
  Cassandra::disConnect($conn);
  return $form;
}