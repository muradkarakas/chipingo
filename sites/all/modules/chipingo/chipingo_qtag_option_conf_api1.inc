<?php


function qtag_option_conf_overview_form( $form, &$form_state, $qtag_id, $session_id ) {
	$breadcrumb = array();
	$breadcrumb[] = l( t('Home'), '<front>');
	$breadcrumb[] = l( 'QTags', 'yourqtags');
	$breadcrumb[] = t( 'Option Configuration' );
	$breadcrumb[] = t( 'Edit'); 
	drupal_set_breadcrumb($breadcrumb);
	
	$_SESSION['destination_url'] = current_path();
	
	//$_SESSION['qtag_id'] 	=	$qtag_id;
	//$_SESSION['session_id'] =	$session_id;
	
	return drupal_get_form('qtag_option_conf_edit_form', $qtag_id, $session_id );
}


/**
*
*/
function qtag_option_conf_edit_form($form, &$form_state, $qtag_id, $session_id  ) {
	
	$controller = entity_get_controller( 'qtag_session' );
	$entity = $controller->load( array( $session_id ) );
	$entity = $entity[$session_id];

	$form['qtag_id'] =  array( 
		'#type' => 'hidden',
		'#default_value' => ( isset($entity->qtag_id) ? $entity->qtag_id : '' ) 
	);
	
	$form['session_id'] = array( 
		'#type' => 'hidden',
		'#default_value' => ( isset($entity->session_id) ? $entity->session_id : '' )
	);
	
	$form['option_information']['values']['session_option_type'] = array(
		'#type' => 'radios',
		'#title' => t('Option type') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_OPTION_TYPE,
		'#description' => 'Define how to client respond to the your Chipingo',
		'#default_value' => (isset($entity->session_option_type) ? $entity->session_option_type : 0 ),
	);
	
	$form['actions']['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Save Option Configuration'),
	);
	
	$form['actions']['link'] = array(
		'#markup' => l( t('Cancel' ), 'yourqtags' )
	);
	
	return $form;
}


/**
*
*/
function qtag_option_conf_edit_form_submit( $form, &$form_state ) {
	
	$qtag = new QTagSessionEntity();

	$qtag_id 	= $form_state['input']['qtag_id'];
	$session_id = $form_state['input']['session_id'];
	
	foreach( $form_state['input'] as $key => $value ) {
		$qtag->$key = $value;
	}
	
	$qtag->qtag_id 		= $qtag_id;
	$qtag->session_id 	= $session_id;
	
	//$qtag->changed = time();
	
	$qtag->save();
	
	drupal_set_message( t('Saved') );
	
	drupal_goto( 'yourqtags' );
	
	return $form;
}

