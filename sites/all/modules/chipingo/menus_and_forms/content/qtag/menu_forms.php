<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/content/qtag/api' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/chipingo/api' );

/**
*  Add or Update QTag entity 
*/
function qtag_edit_form_submit(&$form, &$form_state) {	
	$conn = Cassandra::initializeCassandraSystem();
  $qtag = $form_state['values']['qtag'];
	$question = $form_state['values']['question'];
  $chipingo_email = $form_state['values']['chipingo'];
  $session_name = '';
  _saveQTag($chipingo_email, $qtag, $question, $GLOBALS['user']->uid, NULL, $conn);
  drupal_set_message( t('QTag saved successfully') );
  Cassandra::disConnect($conn);
	$form_state['redirect'] = __getQTagEditPath($chipingo_email,$qtag,$session_name) . 'last';
}

/**
*   This form is used by Edit and Add actions
*   only difference is Edit action has $qtag_id, Add action does not""
*/
function qtag_form_wrapper( $chipingo_email = NULL, $qtag = NULL, $session_name = NULL ) {
  $data = [
    'chipingo_email' => $chipingo_email,
    'qtag' => $qtag,
    'session_name' => $session_name,
    'user_id' => $GLOBALS['user']->uid
  ];
  $conn = Cassandra::initializeCassandraSystem();
  if (isset($session_name) and strlen($session_name)>0) {
    if ($session_name != 'last' ) {
      _setLastSessionUsedForThisQTag($data, $conn);
    } else {
      $session_name = _getLastSessionUsedForThisQTag($chipingo_email, $qtag, $conn); 
    } 
  }
  $_SESSION['chipingo_email']  = $chipingo_email;
	$_SESSION['qtag']    = $qtag;
  $_SESSION['session_name'] = $session_name;
  Cassandra::disConnect($conn);
	return drupal_get_form('qtag_edit_form', $chipingo_email, $qtag, $session_name);
}

/**
*  
*/
function qtag_edit_form($form, &$form_state, $chipingo = NULL, $qtag = NULL, $session_name = NULL) {
	
  $conn = Cassandra::initializeCassandraSystem();
  $publishedCount = __getPublishedQTagCount($chipingo,$conn);
  
	$form['values-chipingo-block-start'] = array(
		'#markup' => 	'<div class="chipingo-block">' .
						' 	<div class="accordion chipingo-radius chipingo-div "> ' . 
						'		<div class="chipingo-block-header"> '.
						'			My '.
						'			<font style="color:#F90">Chip</font> '.
						'			<font style="color:#093">In</font> '.
						'			<font style="color:#F00">Go</font> '.
						'		</div>' .
						'		<div class="chipingo-block-body"> ',
	);
	
	//Getting user's valid chipingos
	$valid_chipingo_list = _getUserChipingoComboboxOptions($conn); 
	
	if ( count($valid_chipingo_list) == 0 ) {
		drupal_set_message( t('Please validate your ChipInGos or add a new ones. Please visit ' . l('"Publisher & ChipInGo Settings"', 'publisher_chipingo' ). ' link' ), 'warning');
		$form['values']['actions']['link'] = array(
			'#markup' => l( t('Back' ), 'yourqtags' )
		);
		return $form;
	}
	
	// If in edit form, QTag info is loading from the db in order to show them on the edit form
	if ( isset($chipingo) && isset($qtag) ) {
		$entity = _getQTag($chipingo, $qtag, $conn);	
	}
	
	$form['values']['qtag_id'] = array(
		'#default_value' => isset($entity['qtag']) ? $entity['qtag'] : '' 
	);
	
	$form['values']['qtag'] = array(
		'#type' => 'textfield',
		'#title' => 'QTag',
    '#id' => 'qtag',  // !!! this id is used in a javascript. DO NOT CHANGE IT 
		'#attributes' => array( 'style' => 'width: 150px', 'placeholder' => 'QTag' ),
		'#default_value' => (isset($entity['qtag']) ? $entity['qtag']: '' ),
		'#description' => 'Tag your question with a word',
		'#required' => TRUE,
	);

	$form['values']['chipingo'] = array (
		'#type' => 'radios', 
    '#required' => TRUE,
		'#default_value' => (isset($entity['chipingo_email']) ? $entity['chipingo_email'] : '' ),
		'#title' => t('Choose one of your valid ChipInGo'),
		'#options' => $valid_chipingo_list,
		'#description' => t(''),
		'#prefix' => '<span >',
		'#suffix' => '</span>'
	);
	
	$form['values']['question'] = array(
		'#type' => 'textarea',
		'#rows' => 2,
		'#title' => t('Question'),
		'#attributes' => array( 'placeholder' => t('Question') ),
		'#default_value' => (isset($entity['question']) ? $entity['question'] : '' ),
		'#description' => t('Question'),
		'#required' => TRUE
	);	

	$form['values-chipingo-block-stop'] = array(
		'#markup' => 	'		</div>'
	);
  
  $form['actions'] = array(
		'#type' => 'container',
		'#attributes' => array('class' => array('form-actions')),
		'#weight' => 400,
	);
  
  $form['values-chipingo-div'] = array(
		'#markup' => 	'		<div style="text-align: right; margin-top: 5px;">'
	);
      
  if (isset($qtag)) {    
    $form['actions']['submit-publish'] = array(
      '#type' => 'submit',
      '#value' => ((count($publishedCount)>0) ? '<i class="fa fa-cog fa-spin fa-lg"></i>':'') . '&nbsp;&nbsp;' . t('Publish ChipInGo'),
      '#disabled' => (count($publishedCount)>0),
      '#session_name' =>  $session_name,
      '#submit' => array('publish_form'),
      //'#prefix' => '<div style="text-align: right; margin-top: 5px;">',
      //'#suffix' => '</div>',
      '#attributes' => array(
        'class' => array( 'btn btn-success btn-sm' )
      )
    );
    $form['actions']['submit-see-results'] = array(
      '#type' => 'submit',
      '#value' => t('Results'),
      '#submit' => array('qtag_edit_form_submit'),
      '#attributes' => array(
        'class' => array( 'btn btn-primary btn-sm' )
      )
    );
  }  
  
  if (isset($qtag)) {    
    $form['actions']['submit-delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Question'),
      '#disabled' => (count($publishedCount)>0),
      '#submit' => array('qtag_delete_form_submit'),
      '#attributes' => array(
        'class' => array( 'btn btn-primary btn-sm' )
      )
    );
  }  
  
  $form['actions']['submit-save'] = array(
		'#type' => 'submit',
		'#value' => isset($qtag) ? t('Save Question') : t('Create a New Question'),
		'#submit' => array('qtag_edit_form_submit'),
    '#disabled' => (count($publishedCount)>0),
    '#suffix' => '</div>',
		'#attributes' => array(
			'class' => array( 'btn btn-primary btn-sm' )
		)
	);
  
  $form['values-chipingo-block-stop'] = array(
		'#markup' => 	'	</div>' .
                  '</div>',
	);
  
  Cassandra::disConnect($conn);  
	return $form;
}

/**
 * 
 * @param type $form
 * @param type $form_state
 * @return type
 */
function publish_form($form, &$form_state) {
  if ( ! isset($form_state['clicked_button']['#session_name']) ) {
    return NULL;
  }
  $path = __getSessionPublishPath(
      $_SESSION['chipingo_email'], 
      $_SESSION['qtag'], 
      $form_state['clicked_button']['#session_name']
  );
  $form_state['redirect'] = $path;
}

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function qtag_delete_form_submit($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  $path = __deleteQTag(
      $_SESSION['chipingo_email'], 
      $_SESSION['qtag'],
      $conn
  );
  Cassandra::disConnect($conn);
  $form_state['redirect'] = 'home';
}