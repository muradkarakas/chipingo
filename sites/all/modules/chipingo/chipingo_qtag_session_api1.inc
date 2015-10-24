<?php


class QTagSessionEntity extends Entity {

	public function defaultUri() {
		return array('path' => 'session' );
	}
	
	public function defaultLabel() {
		return  $this->session_name;
	}

	public function __construct(array $values = array(), $entityType = NULL) {
		parent::__construct( $values, 'qtag_session' );
	}
}

class QTagSessionEntityController extends EntityAPIController {
	
	public function buildContent($entity, $view_mode = 'full', $langcode = NULL, $content = array()) {
		
		$form = parent::buildContent( $entity, $view_mode, $langcode, $content );
				
		$form['group'] = array(
			'#type' => 'fieldset',
			'#title' => t('QTag') . ' : ' . $entity->session_name,
			'#collapsible' => TRUE,
			'#collapsed' => FALSE
		);
		
		$form['group']['qtag_id'] = array(
			'#markup' => t('QTag Id') . ' : ' . $entity->qtag_id  .  '<br><br>'
		);
		
		$form['group']['publish_start_date'] = array(
			'#markup' => t('Publish start date') . ' : ' . (isset($entity->publish_start_date) ? $entity->publish_start_date : '' ) .  '<br><br>', 
		);
		
		$form['group']['publish_end_date'] = array(
			'#markup' => t('Publish end date') .  ' : ' . (isset($entity->publish_end_date) ? $entity->publish_end_date : '' ) .  '<br><br>',  
		);
		
		$form['group']['session_status'] = array(
			'#markup' => t('Session Status') .  ' : ' . (isset($entity->session_status) ? ChipInGoConstants::$QTAG_SESSION_STATUS[$entity->session_status] : ChipInGoConstants::$QTAG_SESSION_STATUS[0] )  .  '<br><br>',
		);
		
		$form['group']['session_question_type'] = array(
			'#markup' => t('Session Question Type') .  ' : ' . (isset($entity->session_question_type) ? ChipInGoConstants::$QTAG_SESSION_QUESTION_TYPE[$entity->session_question_type] : ChipInGoConstants::$QTAG_SESSION_QUESTION_TYPE[0] )  .  '<br><br>',
		);
		
		$form['group']['session_option_type'] = array(
			'#markup' => t('Session Option Type') .  ' : ' . (isset($entity->session_option_type) ? ChipInGoConstants::$QTAG_SESSION_OPTION_TYPE[$entity->session_option_type] : ChipInGoConstants::$QTAG_SESSION_OPTION_TYPE[0] )  .  '<br><br>',
		);
		
		
		$form['my_field'] = array(
		  '#type' => 'link',
		  '#title' => t('Go back'),
		  '#href' =>  entity_get_info( $this->entityType )['admin ui']['path'],
		);

		return $form;
	}	
	
	/**
	* Load function 
	*/
	public function load( $ids = array(), $conditions = array()) {
		
		$qtag_sessions = parent::load( $ids, $conditions );	
		
		foreach( array_keys($qtag_sessions) as $qtag_session ) {
			
			if ( isset($qtag_sessions[$qtag_session]->publish_start_date) )
				$qtag_sessions[$qtag_session]->publish_start_date = convertUnixUTCTimestampToUserTimezoneDateString( $qtag_sessions[$qtag_session]->publish_start_date );
			if ( isset($qtag_sessions[$qtag_session]->publish_end_date) )
				$qtag_sessions[$qtag_session]->publish_end_date = convertUnixUTCTimestampToUserTimezoneDateString( $qtag_sessions[$qtag_session]->publish_end_date );
			//$qtag_sessions[$qtag_session]->created = convertUnixUTCTimestampToUserTimezoneDateString( $qtag_sessions[$qtag_session]->created );
		}
		return $qtag_sessions;
	}
	
	public function save($entity, DatabaseTransaction $transaction = NULL) {
		
		try {
			
			if ( isset($entity->publish_start_date) ) {
				$start_date = createDateFromStrWithUserTimeZone( $entity->publish_start_date );
				$entity->publish_start_date = convertUTCTimestamp( $start_date );	
			}
				
			if ( isset($entity->publish_end_date) ) {
				$end_date = createDateFromStrWithUserTimeZone( $entity->publish_end_date );
				$entity->publish_end_date = convertUTCTimestamp( $end_date );
			}
			
			// Only first one who saves this record can be owner, 
			if ( !isset($entity->user_id) )
				$entity->user_id = $GLOBALS['user']->uid;
			
			$entity->changed = time();
			
			$entity = parent::save( $entity, $transaction );		
		} catch (Exception $e) {
			  # TODO can search $e for 'Integrity constraint violation' or 'Duplicate entry'
			  # to verify the user tried to use the same name twice.
			  drupal_set_message(t("Sorry, that didn't work." . $e), 'error');
		}
		return $entity;
	}
	
	public function delete($ids, DatabaseTransaction $transaction = NULL) {
		
		parent::delete( $ids, $transaction );
	}
	
}

class QTagSessionEntityDefaultUIController extends EntityDefaultUIController {
		
	public function hook_menu() {

		$items = array();
		
		// general menu access 
		$items[$this->path] = array(
		  'title' => t('Session'),
		  'page callback' => 'qtag_session_list_wrapper',
		  'page arguments' => array( 1, 2 ), // $page, $qtag_id3
		  'access callback' => TRUE,
		);
		
		
		
		// Restriction Edit
		$items['restriction/edit'] = array(
		  'title' => t('Session Restriction Setting'),
		  'page callback' => 'drupal_get_form',
		  'page arguments' => array( 'qtag_restriction_overview_form', 2, 3 ), // $qtag_id, $session_id
		  'description' => $this->entityInfo['label'],
		  'access callback' => TRUE
		);
		
		// Question Settings Edit
		$items['questionconf/edit'] = array(
		  'title' => t('Session Question Settings'),
		  'page callback' => 'drupal_get_form',
		  'page arguments' => array( 'qtag_question_conf_overview_form', 2, 3 ), // $qtag_id, $session_id
		  'description' => $this->entityInfo['label'],
		  'access callback' => TRUE
		);
		
		// Option Settings Edit
		$items['optionconf/edit'] = array(
		  'title' => t('Session Option Settings'),
		  'page callback' => 'drupal_get_form',
		  'page arguments' => array( 'qtag_option_conf_overview_form', 2, 3 ), // $qtag_id, $session_id
		  'description' => $this->entityInfo['label'],
		  'access callback' => TRUE
		);

		
		
		// Viewing a single QTagSession entity
		$items[$this->path . '/view'] = array(
		  'page callback' => 'qtag_entity_view',
		  'page arguments' => array( $this->entityType, 2 ),
		  'access callback' => TRUE
		);
		
		// Editing an existing QTag entity
		$items[$this->path . '/del'] = array(
		  'page callback' => 'qtag_session_del_wrapper',
		  'page arguments' => array( 2 ,3 ),  // $qtag_id, $session_id
		  'access callback' => TRUE
		);	
		
		return $items;
	}
}





