<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/content/session/api' );

/**
*
*/
function session_edit_form_submit($form, &$form_state ) {  
  $conn = Cassandra::initializeCassandraSystem();
  $data = [
    'chipingo_email' => $form_state['values']['chipingo_email'],
    'qtag' => $form_state['values']['qtag'],
    'session_name' => $form_state['values']['session_name'],
    'user_id' => $GLOBALS['user']->uid,
  ];      
	_saveSession($data, $conn);
	Cassandra::disConnect($conn);
  drupal_goto( __getQTagEditPath($data['chipingo_email'], $data['qtag'],  $data['session_name']) );	
  return $form;
}

/**
 * 
 * @param type $op
 * @param type $qtag_id
 * @param type $session_id
 * @return type
 */
function session_form_wrapper($op, $chipingo_email, $qtag, $session_name = NULL) {
 
	$_SESSION['chipingo_email'] =	$chipingo_email;
  $_SESSION['qtag'] 	=	$qtag;
	$_SESSION['session_name'] =	$session_name;
	
  switch($op) {
    case 'edit':
    case 'add':
        return drupal_get_form('session_edit_form', $op, $chipingo_email, $qtag, $session_name);
    case 'delete':
        return drupal_get_form('session_delete_form', $op, $chipingo_email, $qtag, $session_name);
    case 'publish':
        return drupal_get_form('session_publish_form', $op, $chipingo_email, $qtag, $session_name);
  }
  
  throw new Exception('Invalid op code');
}


/**
 * 
 * @param type $form
 * @param type $form_state
 * @param type $op
 * @param type $session_id
 * @param type $qtag_id
 * @return type
 */
function session_publish_form($form, &$form_state, $op, $chipingo_email, $qtag, $session_name) {
  //drupal_set_message($op. $chipingo_email. $qtag. $session_name);

  $form['description'] = [
    '#markup' => $qtag . t(' will be published. Please provide publish end date.')
  ];
  
  $form['publish_start_date'] = [
    '#type' => 'textfield',
    '#default_value' => date('r', time()),
    '#description' => t('The time when voting is start')
  ];
  
  $form['publish_end_date'] = [
    '#type' => 'textfield',
    '#default_value' => date('r', time()+(3*60)), // 3 mins later
    '#description' => t('The time when voting is finished')
  ];
  
  $form['publish'] = [
    '#type' => 'submit',
    '#chipingo_email' => $chipingo_email,
    '#qtag' => $qtag,
    '#session_name' => $session_name,    
    '#default_value' => t('Confirm Publish'),
    '#description' => t('Confirms and Publish the selected QTag and session')
  ];
  
  $form['cancel'] = [
    '#type' => 'submit',
    '#default_value' => t('Cancel'),
    '#description' => t('Cancel publication')
  ];
  return $form;
}


function session_publish_form_submit($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem(); 
  $data = [
    'chipingo_email' => $form_state['clicked_button']['#chipingo_email'],
    'qtag' => $form_state['clicked_button']['#qtag'],
    'session_name' => $form_state['clicked_button']['#session_name'],
    'publish_start_date' => strtotime($form_state['values']['publish_start_date']),
    'publish_end_date' => strtotime($form_state['values']['publish_end_date']),
  ];  
  _publishQTag($data, $conn);
  Cassandra::disConnect($conn);
}
    
/**
 * 
 * @param type $form
 * @param type $form_state
 * @param type $op
 * @param type $session_id
 * @param type $qtag_id
 * @return type
 */
function session_delete_form($form, &$form_state, $op, $chipingo_email, $qtag, $session_name) {
  drupal_set_message($op. $chipingo_email. $qtag. $session_name);
}

/**
 * 
 * @param type $form
 * @param type $form_state
 * @param type $op
 * @param type $session_id
 * @param type $qtag_id
 * @return type
 */
function session_edit_form($form, &$form_state, $op, $chipingo_email, $qtag, $session_name) {
	
	if ( $op == 'edit' ) {
    $conn = Cassandra::initializeCassandraSystem();
    $sessionTable = new SessionTable($conn);
    $entity = $sessionTable->getSession($chipingo_email, $qtag, $session_name);
    Cassandra::disConnect($conn);
	} else {
    $entity['qtag'] = $qtag;
    $entity['chipingo_email'] = $chipingo_email;
    $entity['session_name'] = NULL;
  }
	
	$form['values']['qtag'] =  array( 
		'#type' => 'hidden',
		'#value' => ( isset($entity['qtag']) ? $entity['qtag'] : '' ) 
	);
	
  $form['values']['chipingo_email'] =  array( 
		'#type' => 'hidden',
		'#value' => ( isset($entity['chipingo_email']) ? $entity['chipingo_email'] : '' ) 
	);
  
  $form['values']['session_name'] =  array( 
		'#type' => 'hidden',
		'#value' => ( isset($entity['session_name']) ? $entity['session_name'] : '' ) 
	);
  
	$form['values']['session_name'] = array( 
		'#type' => 'hidden',
		'#value' => ( isset($entity['session_name']) ? $entity['session_name'] : '' )
	);

	$form['values']['session_status'] = array(
		//'#type' => 'radios',
		'#title' => t('Session status') . ' : ',
		'#markup' => '<b>' . t('Session status') . ' : </b>' . (isset($entity['session_status']) ? ChipInGoConstants::$QTAG_SESSION_STATUS[$entity['session_status']] : ChipInGoConstants::$QTAG_SESSION_STATUS[0] ),
	);
	
	$form['values']['session_name'] = array(
		'#type' => 'textfield',
		'#size' => 40,
		'#title' => t('Name of the session') . ' : ',
		'#default_value' => (isset($entity['session_name']) ? $entity['session_name'] : date( ChipInGoConstants::$CHIPINGO_SESSION_NAME_DATE_FORMAT ) ),
		'#required' => TRUE,
		'#description' => t('Name of the session'),
		'#prefix' => '<table style="border: 0px solid; "><tr><td style="vertical-align: top;">',
	);
	
	$date_desc = '<br>Be aware of your time zone : <font style="color: red">' . drupal_get_user_timezone() . '. </font><br>If wrong, go your profile page and change.<br><br>';
	
	$form['values']['publish_start_date'] = array(
		//'#type' => 'date_popup',
		'#type' => 'textfield',
		'#title' => 'Publish start date',
		'#size' => 40,
		//'#date_type' => DATE_DATETIME,
		//'#date_timezone' => drupal_get_user_timezone(),
		//'#date_format' => $format,
		'#default_value' => (isset($entity['publish_start_date']) ? $entity['publish_start_date'] : date( ChipInGoConstants::$CHIPINGO_DATE_FORMAT ) ),
		'#required' => TRUE,
		'#description' => $date_desc,
		'#prefix' => '</td><td style="vertical-align:top">'
	);
	
	$form['values']['publish_end_date'] = array(
		//'#type' => 'date_popup',
		'#type' => 'textfield',
		'#description' => $date_desc,
		'#title' => 'Publish end date:',
		'#size' => 40,
		//'#date_timezone' => drupal_get_user_timezone(),
		//'#date_format' => $format,
		'#default_value' => (isset($entity['publish_end_date']) ? $entity['publish_end_date'] : date( ChipInGoConstants::$CHIPINGO_DATE_FORMAT ) )                                    	,
		'#required' => TRUE,
		'#prefix' => '</td><td style="vertical-align:top">',
		'#suffix' => '</td></tr></table>'
	);
	
	$form['actions'] = array(
		'#type' => 'container',
		'#attributes' => array('class' => array('form-actions')),
		'#weight' => 400,
	);
	
	$form['actions']['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Save Session'),
		'#submit' => array('session_edit_form_submit'),
	);
	
	$form['actions']['link'] = array(
		'#markup' => l( t('Cancel' ), 'yourqtags' )
	);
	
	return $form;
}

