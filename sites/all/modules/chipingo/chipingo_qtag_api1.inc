<?php

/**
 * Extending the EntityAPIController for the QTag entity.
 */
class QTagEntityController extends EntityAPIController {
	
	/**
	*
	*/
	public function buildContent($qtag, $view_mode = 'full', $langcode = NULL, $content = array()) {
		
		$form = parent::buildContent($qtag, $view_mode, $langcode, $content);
		
		$form['fs1' . $qtag->qtag_id] = array(
			'#type' => 'fieldset',
			'#title' => '<strong>Sizce bolum sonunda ne olacak?</strong>' ,
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
		);
		/*
		$form['fs1' . $qtag->qtag_id]['qtag'] = array(
			'#type' => 'textfield',
			'#title' => 'QTag',
			'#attributes' => array( 'style' => 'width: 150px', 'placeholder' => 'QTag' ),
			'#default_value' => (isset($entity->qtag) ? $entity->qtag: '' ),
			'#description' => 'Tag your question with a word',
			'#required' => TRUE,
			'#prefix' => '<table ><tr><td style="width:1%">',
		);
		
		$form['fs1' . $qtag->qtag_id]['chipingo_id'] = array(
			'#type' => 'select',
			'#title' => 'Chipingo',
			'#attributes' => array( 'style' => 'width: 250px' ),
			'#default_value' => (isset($entity->chipingo_id) ? $entity->chipingo_id : '' ) ,
			'#required' => TRUE,
			'#description' => 'Your validated Chipingos listed here',
			'#options' => $valid_chipingo_list,
			'#prefix' => '</td><td>',
			'#suffix' => '</td></tr></table>',
		);
		
		$form['fs1' . $qtag->qtag_id]['question'] = array(
			'#type' => 'textarea',
			'#title' => 'Your question',
			'#attributes' => array( 'placeholder' => 'QTag' ),
			'#default_value' => (isset($entity->question) ? $entity->question : '' ),
			'#description' => 'Your question',
			'#required' => TRUE,
			'#rows' => 2, 
		);
		
		$form['fs1' . $qtag->qtag_id]['actions']['edit'] = array(
			'#type' => t('submit'),
			'#attributes' => array( 'class' => array('chipingo-button', 'chipingo-warning') ),
			'#value' => 'Edit',
		);
		*/
		$form['fs1' . $qtag->qtag_id]['table'] = overviewTable('qtag_session' );
		$form['fs1' . $qtag->qtag_id]['pager'] = array('#theme' => 'pager');
	
		$form['fs1' . $qtag->qtag_id]['actions']['delete'] = array(
			'#type' => 'submit',
			'#attributes' => array( 'class' => array('chipingo-button', 'chipingo-success') ),
			'#value' => t('Delete')
		);
		
		return $form;
	}	
	
	/**
	* Load function 
	*/
	public function load($ids = array(), $conditions = array()) {
		
		if ( ! isset($ids) && count($qtag_id) === 0 ) {
			 drupal_set_message( 'Parameter is incorrect', 'error');
			 return NULL;
		}
		
		$tags = parent::load( $ids, $conditions );	
		foreach( array_keys($tags) as $tag ) {			
			$tags[$tag]->created = convertUnixUTCTimestampToUserTimezoneDateString( $tags[$tag]->created );
		}
		return $tags;
	}
	
	/**
	*   Every qtag record should have at least one qtag_sessions record. 
	*	So, if this is a new qtag record we are creating corresponding qtag_sessions 
	*	record with default values
	*
	*/
	public function save($entity, DatabaseTransaction $transaction = NULL) {
		
		$is_new = ( ! isset($entity->qtag_id) or drupal_strlen($entity->qtag_id)==0 );
		
		// Only first one who saves this record can be owner, those makes change on this record cannot be owner 
		if ( $is_new ) {
			$entity->created = time();
			$entity->user_id = $GLOBALS['user']->uid;
		}

		$entity->changed = time();
		
		// We are saving master record before creating session record in order to get qtag_id
		$qtag_entity = parent::save( $entity, $transaction );		
		
		if ( $is_new ) {
			// Then, we are using primary key value as a value for foreign key in detail table
			$qtag_session_entity = new QTagSessionEntity();
			$qtag_session_entity->session_name = date( ChipInGoConstants::$CHIPINGO_SESSION_NAME_DATE_FORMAT );		
			$qtag_session_entity->user_id = $GLOBALS['user']->uid;			
			$qtag_session_entity->qtag_id = $entity->qtag_id;
			$qtag_session_entity->chipingo_id = $entity->chipingo_id;
			$qtag_session_entity->created = time();
			$qtag_session_entity->changed = time();
			$qtag_session_entity->save( $entity, $transaction );
		}
		return $qtag_entity;
	}
	
}

/**
 * QTag entity class extending the Entity class
 */
class QTagEntity extends Entity {

	/**
	* 
	*/
	public function defaultUri() {
		return array('path' => 'yourqtags/view/' . $this->identifier() );
	}
	
	public function defaultLabel() {
		return  $this->qtag;
	}
	
	public function __construct(array $values = array(), $entityType = NULL) {
		parent::__construct( $values, 'qtag' );
	}
}

/**
*
*/
class QTagEntityDefaultUIController extends EntityDefaultUIController {
			
	public function hook_menu() {

		$items = array();
		
		// Edit or Add QTag entity
		

			
		return $items;
	}

}

/*
  ~~~~~~~~~~~~~~ END OF CLASS DECLARATION ~~~~~~~~~~~~~~ ~~~~~~~~~~~~~~ ~~~~~~~~~~~~~~ 
*/


/**
*  Delete wrapper form
*/
function qtag_del_wrapper( $qtag_id ) {
	$obj = entity_get_controller('qtag');
	$qtag = $obj->load( array($qtag_id) );
	$qtag = $qtag[ $qtag_id ];	
	
	return drupal_get_form('qtag_del_form', $qtag_id, $qtag->qtag );
}

/**
*  Show "Are you sure ?" message before deletion
*/
function qtag_del_form($form, &$form_state, $qtag_id, $qtag ) {
	
	$form['question'] = array(
		'#markup' => 'QTag <b>'. $qtag . t('</b> and its all data will be deleted!' . '<br><br><b>' . 'Do you want to continue ?' . '</b><br><br>' )
	);
	
	
	$form['actions']['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Delete') . ' "' . $qtag . '"'
	);
	$form['actions']['link'] = array(
		'#markup' => l( t('Cancel deletion' ), 'yourqtags' )
	);
	return $form;
}

/**
*   Delete single QTag from db and shows "Success" message
*/
function qtag_del_form_submit($form, &$form_state ) {	
	if ( ! isset($form_state['build_info']['args'][0]) ) 
		 return;
	$obj = entity_get_controller('qtag'); 
	$obj->delete( array( $form_state['build_info']['args'][0] ) );	 
	drupal_set_message( t('QTag') . ' : "' . $form_state['build_info']['args'][1] . '"<br>' . t('Successfully deleted') ); 
	$form_state['redirect'] = 'yourqtags';
}







/**
 * Form API validate callback for the qtag form
 */
function qtag_edit_form_validate(&$form, &$form_state) {

}

/**
* Returns the operation count for calculating colspans.
*/
function operationCount() {
	$count = 3;
	// $count += !empty($this->entityInfo['i18n controller class']) ? 1 : 0;
	return $count;
}

/**
*
*/
function qtaq_overview_form($form, &$form_state, $entity_type) {
	$breadcrumb = array();
	$breadcrumb[] = l('Home', '<front>');
	$breadcrumb[] = l('QTags', 'yourqtags');
	drupal_set_breadcrumb($breadcrumb);
	
	//$form['#attributes'] = array( 'class' => array('metro') );
	
	$controller = entity_get_controller( 'qtag' );
	$qtags = $controller->load( array(1) );

	$form['div-start'] = array(
		'#markup' => '<div class="chipingo-div">',
	);
	
	$valid_chipingo_list = get_current_user_chipingo_combobox_options(); 
		
	$form['chipingo_list'] = array(
		'#type' => 'select',
		'#title' => 'Chipingo',
		'#default_value' => (isset($entity->chipingo_id) ? $entity->chipingo_id : '' ) ,
		'#required' => TRUE,
		//'#description' => 'Select one of your valid Chipingos listed here in order to list questions under it',
		'#options' => $valid_chipingo_list,
		'#prefix' => '<table ><tr><td style="width:1%">',
	);
	
	$form['chipingo_list_button'] = array(
		'#type' => 'submit',
		'#attributes' => array( 'class' => array('chipingo-button', 'chipingo-success') ),
		'#value' => t('List'),
		'#prefix' => '</td><td>',
		'#suffix' => '</td></tr></table>',
	);
		
	for ( $i=0; $i < 5; $i++ ) {
		$form['chipingos' . $i] = $controller->buildContent( $qtags[1] );
	}
	
	$form['div-end'] = array(
		'#markup' => '</div>',
	);
	
	
	return $form;
	return $form;
}


/**
 * Implements hook_entity_property_info().
 */
function qtag_entity_property_info() {
  $info = array();

  /*
  $info['qtag']['properties']['tag_id'] = array(
    'label' => t('Project ID'),
    'description' => t('The ID of the project.'),
    'type' => 'integer',
    'schema field' => 'id',
  );

  $info['project']['properties']['name'] = array(
    'label' => t('Project name'),
    'description' => t('Name of the project.'),
    'type' => 'text',
    'schema field' => 'name',
  );
  $info['project']['properties']['description'] = array(
    'label' => t('Project description'),
    'description' => t('Description of the project.'),
    'type' => 'text',
    'schema field' => 'description',
  );*/
  
  $info['qtag']['properties']['start_date'] = array(
    'label' => t('Start date'),
    'description' => t('Start date'),
    'type' => 'date',
    'schema field' => 'start_date',
  );
  $info['qtag']['properties']['end_date'] = array(
    'label' => t('End date'),
    'description' => t('End date'),
    'type' => 'date',
    'schema field' => 'end_date',
  );
  $info['qtag']['properties']['created'] = array(
    'label' => t('End date'),
    'description' => t('Creation Date'),
    'type' => 'date',
    'schema field' => 'created',
  );
  $info['qtag']['properties']['changed'] = array(
    'label' => t('Last change'),
    'description' => t('Last Change'),
    'type' => 'date',
    'schema field' => 'changed',
  );

  return $info;
}