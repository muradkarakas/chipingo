<?php


/**
*
*/
class ChipingoEntity extends Entity {

	public function defaultUri() {
		return array('path' => 'publisher_chipingo' );
	}
	
	public function defaultLabel() {
		return  $this->publisher . '?' . $this->chipingo;
	}

	public function __construct(array $values = array(), $entityType = NULL) {
		parent::__construct( $values, 'chipingo' );
	}
}

/**
*
*/
class ChipingoEntityController extends EntityAPIController {
	
	public function buildContent($entity, $view_mode = 'full', $langcode = NULL, $content = array()) {
		
		$form = parent::buildContent( $entity, $view_mode, $langcode, $content );
		return $form;
	}	
	

	/**
   * Loads row(s) from "chipingo" table.
   * 
   * @param array-of-string. $ids If null, all chipingos will be loaded (limited to database/node setting.
   *                              If one or more chipingo names are provided, related rows will be returned.
   *                              All columns will be loaded by default.
   * @param array-of-array. $conditions  Additional search criterias. Only columns defined as 
   *                        an secondary index are allowed to be used. 
   *                        Secondary indexed columns are as follows: User_id
   * @return array-of-ChipingoEntity  Matched records will be returned as an array of ChipingoEntity
   */
	public function load( $ids = array(), $conditions = array()) {
		$resultSet = array();
    $conn = Cassandra::initializeCassandraSystem();
    if ( ! is_array($ids) ) {
      $resultSet[$ids] = $this->_load($ids);
    } else {
      foreach ( $ids as $id ) {
        $resultSet[$id] = $this->_load($id);
      }
    }    
    Cassandra::disconnect($conn);
    return $resultSet;
	}
	
  private function _load( $id ) {
    // creating a new object
    $entity = new ChipingoEntity();
    // loading from database
    $response = ChipingoTable::find($id)->fetchRow();
    // asignments
    $entity->chipingo = $response['chipingo'];
    $entity->user_id = $response['user_id'];
    $entity->chipingo_status = $response['chipingo_status'];
    $entity->default_chipingo = $response['default_chipingo'];
    $entity->created = $response['created'];
    $entity->changed = $response['changed'];
    // return newly created object
    return $entity;
  }
  
	public function save( $entity, DatabaseTransaction $transaction = NULL ) {
    		
    // Only first one who saves this record can be owner, 
    if ( !isset($entity->user_id) ){
      $entity->created = time();
      $entity->user_id = $GLOBALS['user']->uid;
    }

    if (!isset($entity->chipingo_id)) {
      $entity->created = time();
    }

    $entity->changed = time();

    if (filter_var($entity->chipingo . '@' . $entity->publisher, FILTER_VALIDATE_EMAIL)) {
        // it is an e-mail and not validated yet
        // even if it is user's default chipingo
        $entity->chipingo_status = 1;         
    }
    else {
      $entity->chipingo_status = 0; // it is not an e-mail
    } 

    try {
      
      $conn = Cassandra::initializeCassandraSystem();
      ChipingoTable::db_insert(
        $entity->chipingo,
        (int) $GLOBALS['user']->uid,
        $entity->chipingo_status, 
        0, //default_chipingo,
        $entity->created,
        $entity->changed
      );
      Cassandra::disconnect($conn);
    	
		} catch (Exception $e) {
			  # TODO can search $e for 'Integrity constraint violation' or 'Duplicate entry'
			  # to verify the user tried to use the same name twice.
			  drupal_set_message(t("Sorry, that didn't work." . $e), 'error');
		}
		return $entity;
	}
	
  /**
   * 
   * @param type $ids
   * @param DatabaseTransaction $transaction
   */
	public function delete($ids, DatabaseTransaction $transaction = NULL) {
		parent::delete( $ids, $transaction );
	}
}



/**
*
*/
class ChipingoEntityDefaultUIController extends EntityDefaultUIController {
		
	public function hook_menu() {

		$items = array();
		
		// List of Publisher & ChipInGo
		$items['publisher_chipingo'] = array(
		  'title' => t('Publisher & ChipInGo Settings'),
		  'page callback' => 'drupal_get_form',
		  'page arguments' => array( 'publisher_chipingo_settings_block_form_wrapper' ),
		  //'page arguments' => array( 'content' ),
		  'description' => t('Publisher & ChipInGo'),
		  'access callback' => TRUE,
		  'menu_name' => ChipInGoConstants::$MENU_NAME,
      'access callback' => 'chipingo_access',
		  'access arguments' => ['publisher_chipingo'],
      'weight' => 500
    ); 
		
		// Delete an existing Chipingo entity
		$items['publisher_chipingo/del'] = array(
		  'page callback' => 'publisher_chipingo_wrapper',
		  'page arguments' => array( 1, 2 ),  // $op, $chipingo_id
		  'access callback' => TRUE
		);	
		
		// Add a new Chipingo entity
		$items['publisher_chipingo/add'] = array(
		  'page callback' => 'publisher_chipingo_wrapper',
		  'page arguments' => array( 1 ),  // $op
		  'access callback' => TRUE
		);	
    
		/*
		$items['publisher_chipingo/add/'] = array(
		  'title' => t('Session'),
		  'page callback' => 'publisher_chipingo_add',
		  'page arguments' => array( 1, 2 ), // $page, $qtag_id3
		  'access callback' => TRUE,
		);
		
		// general menu access 
		$items[$this->path] = array(
		  'title' => t('Session'),
		  'page callback' => 'qtag_session_list_wrapper',
		  'page arguments' => array( 1, 2 ), // $page, $qtag_id3
		  'access callback' => TRUE,
		);
		
		// Session Edit
		$items[$this->path . '/edit'] = array(
		  'title' => t('Session Settings'),
		  'page callback' => 'qtag_session_form_wrapper',  
		  'page arguments' => array( 'edit', 2, 3 ),	//  $op, $qtag_id, $session_id = NULL
		  'access callback' => TRUE
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

		// Creating a new Session entity
		$items[$this->path . '/add'] = array(
		  'title' => t('Create a new Session'),
		  'page callback' => 'qtag_session_form_wrapper',
		  'page arguments' => array( 'add', 2 ),	//  $op, $qtag_id, $session_id = NULL
		  'access callback' => TRUE
		);
		
		// Viewing a single QTagSession entity
		$items[$this->path . '/view'] = array(
		  'page callback' => 'qtag_entity_view',
		  'page arguments' => array( $this->entityType, 2 ),
		  'access callback' => TRUE
		);
		
		
		*/
		return $items;
	}
}





/**
*	Shows or hide publisher&chipingo block in a region specified with first parameter.
*
*	@param
*		$region   String. 
*

function show_publisher_chipingo_block( $region = 'content' ) {
	
	$publisher_chipingo_block_object = block_load( 'chipingo', 'publisher_chipingo_edit' );
	
	$on_off = ( ($publisher_chipingo_block_object->status==1) ? 0 : 1 );
	
	db_update('block')
		->fields(
			array(
			  'region' => $region,
			  'status' => $on_off
			)
		)
		->condition('bid', $publisher_chipingo_block_object->bid )
		->execute();
	
	return $on_off;
}

*/