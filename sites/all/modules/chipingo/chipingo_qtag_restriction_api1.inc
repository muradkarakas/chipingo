<?php

function qtag_restriction_overview_form( $form, &$form_state, $qtag_id, $session_id ) {
	$breadcrumb = array();
	$breadcrumb[] = l( 'Home', '<front>');
	$breadcrumb[] = l( 'QTags', 'yourqtags');
	$breadcrumb[] = 'Restriction';
	$breadcrumb[] = t( 'Restriction Edit'); 
	drupal_set_breadcrumb($breadcrumb);
	
	$_SESSION['destination_url'] = current_path();
	
	//$_SESSION['qtag_id'] 	=	$qtag_id;
	//$_SESSION['session_id'] =	$session_id;
	
	return drupal_get_form('qtag_restriction_edit_form', $qtag_id, $session_id );
}


/**
*
*/
function qtag_restriction_edit_form($form, &$form_state, $qtag_id, $session_id  ) {
	
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
	
	$form['session_restriction_gender'] = array(
		'#type' => 'radios',
		'#title' => t('Gender') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_GENDER,
		'#default_value' => (isset($entity->session_restriction_gender) ? $entity->session_restriction_gender : 0 ),
		'#prefix' => '<table style="border: none;"><tr><td style="vertical-align:top">',
	);
	
	$form['session_restriction_language'] = array(
		'#type' => 'radios',
		'#title' => t('Language') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_LANGUAGE,
		'#default_value' => (isset($entity->session_restriction_language) ? $entity->session_restriction_language : 0 ),
		'#prefix' => '</td><td style="vertical-align:top">',
	);
		
	$form['session_restriction_country'] = array(
		'#type' => 'radios',
		'#title' => t('Country') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_COUNTRY,
		'#default_value' => (isset($entity->session_restriction_country) ? $entity->session_restriction_country : 0 ),
		'#prefix' => '</td><td style="vertical-align:top">',
	);	
		
	$form['session_restriction_city'] = array(
		'#type' => 'radios',
		'#title' => t('City') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_CITY,
		'#default_value' => (isset($entity->session_restriction_city) ? $entity->session_restriction_city : 0 ),
		'#prefix' => '</td><td style="vertical-align:top">',
	);		
		
	$form['session_restriction_ages'] = array(
		'#type' => 'radios',
		'#title' => t('Age') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_AGE,
		'#default_value' => (isset($entity->session_restriction_ages) ? $entity->session_restriction_ages : 0 ),
		'#prefix' => '</td><td style="vertical-align:top">',
		'#suffix' => '</td></tr></table>',
	);		
	
	$form['actions'] = array(
		'#type' => 'container',
		'#attributes' => array('class' => array('form-actions')),
		'#weight' => 400,
	);
	
	$form['actions']['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Save Restriction'),
	);
	
	if ( isset($entity->session_restriction_language) && $entity->session_restriction_language!=0 )
		$form['actions']['session_restriction_language'] = array(
			'#type' => 'submit',
			'#value' => t('Define "Language" restriction') . ' not implemented',
			'#submit' => array('qtag_session_edit_form_submit'),
		);
	if ( isset($entity->session_restriction_country) && $entity->session_restriction_country!=0 )
		$form['actions']['session_restriction_country'] = array(
			'#type' => 'submit',
			'#value' => t('Define "Country" restriction') . ' not implemented',
			'#submit' => array('qtag_session_edit_form_submit'),
		);
	if (isset($entity->session_restriction_city) && $entity->session_restriction_city!=0)
		$form['actions']['session_restriction_city'] = array(
			'#type' => 'submit',
			'#value' => t('Define "City" restriction') . ' not implemented',
			'#submit' => array('qtag_session_edit_form_submit'),
		);
	
	$form['actions']['link'] = array(
		'#markup' => l( t('Cancel' ), 'yourqtags' )
	);
	
	return $form;
}


/**
*
*/
function qtag_restriction_edit_form_submit($form, &$form_state ) {
	
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