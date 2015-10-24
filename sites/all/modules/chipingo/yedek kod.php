









/**
*
*/
function qtag_session_delete_form_submit($form, &$form_state) {
	
	drupal_set_message( t('deleted') );
	return $form;
}

function qtag_session_list_wrapper( $page, $qtag_id ) {
	return drupal_get_form('qtag_session_list_wrapper_form', $page, $qtag_id );
}

function qtag_session_list_wrapper_form($form, &$form_state, $page, $qtag_id ) {
	
	$breadcrumb = array();
	$breadcrumb[] = l('Home', '<front>');
	$breadcrumb[] = l('QTags', 'yourqtags');
	$breadcrumb[] = 'Restrictions';
	$breadcrumb[] = l('Sessions', 'session/restrictions/'. $qtag_id ); 
	drupal_set_breadcrumb($breadcrumb);
		
	$_SESSION['destination_url'] = current_path();
	
	$form[ 'create_qtag_session_link' ] = array(
		'#markup' => l( t('Create a new Session'), 'session/add/' . $qtag_id ),
		'#suffix' => '<br><br>'
	);	
		
	$form[ 'restriction_description' ] = array(
		'#markup' => t('Please choose one of the active sessions in order to create/modify/delete Restriction(s).'),
		'#suffix' => '<br><br>'
	);

	$conditions['qtag_id'] = $qtag_id;
	
	$form['table'] = overviewTable('qtag_session' ,$conditions);
	$form['pager'] = array('#theme' => 'pager');
	
	return $form;
}

/**
*   Delete single QTag from db and shows "Success" message
*/
function qtag_session_del_form_submit( $form, &$form_state ) {

	if ( ! isset($form_state['build_info']['args'][0]) ) 
		 return;
	 
	$obj = entity_get_controller('qtag_session'); 
	$obj->delete( array( $form_state['build_info']['args'][0] ) );	 
	drupal_set_message( t('QTag session') . ' : "' . $form_state['build_info']['args'][2] . '"<br>' . t('Successfully deleted') ); 
	drupal_goto( $_SESSION['destination_url'] );
}



/**
*  Show "Are you sure ?" message before deletion
*/
function qtag_session_del_form($form, &$form_state, $session_id, $qtag_id, $session_name ) {
	
	$form['question'] = array(
		'#markup' => 'Session <b>'. $session_name . t('</b> and its all data will be deleted!' . '<br><br><b>' . 'Do you want to continue ?' . '</b><br><br>' )
	);
	$form['actions']['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Delete') . ' "' . $session_name . '"'
	);
	$form['actions']['link'] = array(
		'#markup' => l( t('Cancel deletion' ), 'yourqtags' )
	);
	return $form;
}

/**
*  Delete wrapper form
*/
function qtag_session_del_wrapper( $qtag_id, $session_id ) {
	
	$obj = entity_get_controller('qtag_session');
	$qtag_session = $obj->load( array($session_id) );
	$qtag_session = $qtag_session[ $session_id ];	
	
	return drupal_get_form('qtag_session_del_form', $qtag_session->session_id, $qtag_session->qtag_id, $qtag_session->session_name );
}


