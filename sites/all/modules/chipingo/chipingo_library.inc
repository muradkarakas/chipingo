<?php


function __calculateAspectRatioFit($srcWidth, $srcHeight, $maxWidth, $maxHeight) {
  if (is_null($srcWidth)) {
    $srcWidth = $maxWidth;
  }
  if (is_null($srcHeight)) {
    $srcHeight = $maxHeight;
  }
  $ratio = min( [ $maxWidth / $srcWidth, 
                  $maxHeight / $srcHeight ] );
  return [ 'width' => $srcWidth*$ratio, 'height' => $srcHeight*$ratio ];
} 
  
/**
 * Return path of chipingo icon
 * 
 * @param text $chipingo_email
 * @return text
 */
function __getChipingoSourcePath($data, $class='small') {
  if ($class=='small') {
    $sizeCalculated = __calculateAspectRatioFit($data['chipingo_logo_content_width'], $data['chipingo_logo_content_height'], 60, 30); //$maxWidth, $maxHeight;
  } else {
    $sizeCalculated = __calculateAspectRatioFit($data['chipingo_logo_content_width'], $data['chipingo_logo_content_height'], 275, 150); //$maxWidth, $maxHeight
  }
  $html = 
      '<img class="chipingo-image-' . $class . '" ' 
      . 'style="' . 'width:' . $sizeCalculated['width'] . 'px;'
                  . 'height:' . $sizeCalculated['height'] . 'px;" '
      . ' src="/chipingo/show_chipingo_logo.php?chipingo_email=' . $data['chipingo_email'] . '">'
      . '</img>';
  return $html;
}

/**
 * Return path of chipingo icon
 * 
 * @param text $chipingo_email
 * @return text
 */
function __getPublisherSourcePath($chipingo_email) {
  return '<img class="publisher-image" src="/chipingo/show_publisher_logo.php?chipingo_email=' . $chipingo_email . '"></img>';
}

/**
 *  @file
 *	 This file contains ChipInGo global functions
 *
 *   Use double underscore in the beginning of function names, which shows that 
 * the function is defined in library.
 */

/**
 * Removes illegal characters from $text for valid HTML id.
 * 
 * @param text $dirty
 * @return text
 */
function __convertToHTMLId($dirty) {
  $clean = str_replace( '@', 
                        '-', 
                        str_replace('.', 
                                    '-', 
                                    str_replace(' ', 
                                                '-', 
                                                $dirty
                                    )
                        )
                      );
  return $clean;
}

/**
 * Returns url for edit page of a qtag
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return text
 */
function __getQTagEditPath($chipingo_email, $qtag, $session_name=NULL) {
  $path = url(  'yourqtags/edit/' . $chipingo_email . '/' . $qtag . '/' . $session_name,
                array('absolute' => TRUE)
              );
  return $path; 
}

/**
 * Returns url for Publish page of a qtag
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return text
 */
function __getSessionPublishPath($chipingo_email, $qtag, $session_name=NULL) {
  $path = url(  'session/publish/' . $chipingo_email . '/' . $qtag . '/' . $session_name, 
                array('absolute' => TRUE)
              );
  return $path; 
}

/**
 * Returns url for Delete page of a qtag
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return text
 */
function __getSessionDeletePath($chipingo_email, $qtag, $session_name=NULL) {
  $path = url(  'session/delete/' . $chipingo_email . '/' . $qtag . '/' . $session_name, 
                array('absolute' => TRUE)
              );
  return $path; 
}

/**
 * Returns url path for "session edit" form
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return text
 */
function __getSessionEditPath($chipingo_email, $qtag,  $session_name) {
  $path = url(  'session/edit/' . $chipingo_email . '/' . $qtag . '/' . $session_name, 
                array('absolute' => TRUE)
              );
  return $path;
  //return '/chipingo/session/edit/' . $chipingo_email . '/' . $qtag . '/' . $session_name;
}

/**
 * Returns url path for "create session" form
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return text
 */
function __getSessionCreatePath($chipingo_email, $qtag) {
  $path = url(  'session/add/' . $chipingo_email . '/' . $qtag, 
                array('absolute' => TRUE)
              );
  return $path;
  //return '/chipingo/session/add/' . $chipingo_email . '/' . $qtag;
}









/**
*
*/
function overview_form($form, &$form_state, $entity_type) {  
  $form['table'] = overviewTable($entity_type);
  $form['pager'] = array('#theme' => 'pager');  
  return $form;
}

/**
* Generates the render array for a overview table for arbitrary entities
* matching the given conditions.
*
* @param $conditions
*   An array of conditions as needed by entity_load().

* @return Array
*   A renderable array.
*/
function overviewTable( $entity_type, $conditions = array()) {

	$query = new EntityFieldQuery();
	$query->entityCondition('entity_type', $entity_type );

	// Add all conditions to query.
	foreach ($conditions as $key => $value) {
	  $query->propertyCondition($key, $value);
	}
	if ( ChipInGoConstants::$QTAG_OVERVIEW_PAGER_LIMIT ) {
	  $query->pager( ChipInGoConstants::$QTAG_OVERVIEW_PAGER_LIMIT );
	}
	
	$results = $query->execute();
	
	if ( ! isset($results) || count($results)===0 )
		 return;
	
	$ids = array_keys( $results[$entity_type] );
	
	$entities = $ids ? entity_load( $entity_type, $ids) : array();
	ksort($entities);
	
	$rows = array();
	foreach ($entities as $entity) {
	  
	  if ( $entity_type == 'qtag' )
		$rows[] = array( 'data'=>array(''), 'no_striping'=>TRUE, 'colspan' => 10 );
		$a = overviewTableRow($conditions, entity_id($entity_type, $entity), $entity, $entity_type);
		$rows[] = array( 
			'data' => $a, 
			'no_striping'=>TRUE,
			'style'=> ( ($entity_type=='qtag') ? 'background-color: grey; color: yellow;' : 
												 'background-color: #F0F0F0 ' ),
		); 
		if ( $entity_type == 'qtag' and $_SESSION['user_view_mode'] == 'A' ) {
			$condition['qtag_id'] = $entity->qtag_id;
			$et = overviewTable( 'qtag_session', $condition );
			if ( isset($et) ) {
				$rows[] = array( 
					'data' => array( array('data'=>drupal_render( $et ), 'style'=>'padding: 5px; padding-left: 20px', 'colspan'=>'10' )  ), 
					'no_striping' => TRUE, 
					'style'=>'padding: 30px; background-color: #F0F0F0 ' 
				);	
			} else {
				$rows[] =  array( 
					array( 
						'data' => l('Create a new session','session/add/'. $entity->qtag_id), 
						'colspan'=>10, 
						'style'=>'background-color: #E0E0E0; padding: 5px; padding-left: 20px' 
					) 
				);
			}
		}
	}
	
	if ( $entity_type == 'qtag_session' ) {
		$rows[] =  array( 
			array( 
				'data' => l('Create a new session','session/add/'. $entity->qtag_id), 
				'colspan'=>10, 
				'style'=>'background-color: #E0E0E0' 
			) 
		);
	 }

	$render = array(
	  '#theme' => 'table',
	  '#header' => overviewTableHeaders($entity_type),
	  '#rows' => $rows,
	  '#empty' => t('None.'),
	);

	return $render;
}

/**
* Generates the row for the passed entity and may be overridden in order to
* customize the rows.
*
* @param $additional_cols
*   Additional columns to be added after the entity label column.
*/
function overviewTableRow($conditions, $id, $entity, $entity_type, $additional_cols = array()) {
	
	// Buttons for "qtag" entity 
	switch ( $entity_type ) {
		case 'qtag': 
			//$row[] = l( $entity->qtag, 'yourqtags/view/' . entity_id(  $entity_type, $entity) ) . ' (' .$entity->question .')';
			$temprow;
			
			$firstline = $entity->qtag . ' : ' .$entity->question;
			
			if ( $_SESSION['user_view_mode'] == 'S' ) {
				$row[] = $firstline;
				$row[] = l( t('Edit'), current_path() . '/edit/' . $id);
				$row[] = l(t('Options'), 'session/options/' . $id );
				$row[] = l(t('Publish'), current_path() . '/publish/' . $id );
				$row[] = l(t('Results'), 'session/results/' . $id );
				$row[] = l(t('Delete QTag'), current_path() . '/del/' . $id );
			} else {
				$firstline = $firstline . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . l( t('Edit'), current_path() . '/edit/' . $id) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . l(t('Delete'), current_path() . '/del/' . $id );
				$row[] = $firstline;
			}				
			break;
		case 'qtag_session' :
			$row[] = $entity->session_name;
			$row[] = l( 'Edit', 'session/edit/' . $entity->qtag_id . '/' . $entity->session_id );
			if ( $_SESSION['user_view_mode'] == 'A' ) {
				$row[] = l(t('Options'), 'session/options/' . $id );
				$row[] = l(t('Question Type'), 'questionconf/edit/' . $entity->qtag_id . '/' . $entity->session_id );
				$row[] = l(t('Option Type'), 'optionconf/edit/' . $entity->qtag_id . '/' . $entity->session_id );
				$row[] = l(t('Restrictions'), 'restriction/edit/' . $entity->qtag_id . '/' . $entity->session_id );
				$row[] = l(t('Publish'), current_path() . '/publish/' . $id );
				$row[] = l(t('Results'), 'session/results/' . $id );
			}
			$row[] = l(t('Delete Session'), 'session/del/' . $entity->qtag_id . '/' . $entity->session_id );
			break;
		/*case 'qtag_restriction':
			$row[] = l( $entity->restriction_name, 'restrictions/view/' . entity_id(  $entity_type, $entity) );
			$row[] = l( t('Edit'), current_path() . 'restrictions/edit/' . $id);
			$row[] = l(t('Delete (not implemented)'), current_path() . '/del/' . $id );
			break;*/
	}

	return $row;
}

/**
* Generates the table headers for the overview table.
*/
function overviewTableHeaders( $entity_type ) {
	
	$header = array();
	
	switch ( $entity_type ) {
		case 'qtag':
			$caption_style = 'background-color: black; color: white;';
			//$header[] = array( 'data' => 'QTag List', 'style'=>$caption_style ); 
			if ( $_SESSION['user_view_mode'] == 'S' )
				 $header[] = array( 'data' => t('Operations'), 'style'=>'text-align: center;'.$caption_style, 'colspan'=> (($_SESSION["user_view_mode"]== 'A') ? 2 : 10) );			
			
			break;
		case 'qtag_session':
			$caption_style = 'background-color: #B6B6BD; color: 996633;';
			$header[] = array( 'data'=>t('Session Name'), 'style'=>$caption_style );
			$header[] = array( 'data'=>t('Operations'), 'style'=>'text-align: center;'.$caption_style, 'colspan'=>10 );	
			break;
	}
	
	return $header;
}

/**
*   Views a single entity
*/ 
function qtag_entity_view( $entity_type, $qtag_entity_id ) {
	$obj = entity_get_controller( $entity_type );
	$entity = $obj->load( array($qtag_entity_id) );
	return $obj->buildContent( $entity[$qtag_entity_id] );
}

/**
* Converts from String to DateTime object with using drupal user date time zone.
*
* @param  string $original_datetime
*		string parameter 
* 
* @return
*     DateTime object which is converted from original_datetime
* 
* $see drupal_get_user_timezone
* $see DateTimeZone
*/
function createDateFromStrWithUserTimeZone( $original_datetime ) {
	$date = new DateTime( $original_datetime, new DateTimeZone( drupal_get_user_timezone() ) );
	return $date;
}	

/**
* Converts from DateTime object to UTC Unix timestamp object.
*
* @param  DateTime $original_datetime
*		date as DateTime 
* 
* @return
*     DateTime object with UTC DateTimeZone
* 
* $see drupal_get_user_timezone
* $see DateTimeZone
*/
function convertUTCTimestamp( $original_datetime ) {
	
	static $databaseTimestampTimeZone;

	if ( get_class($original_datetime) != 'DateTime' ){
		 drupal_set_message( 'Incorrect type is passed to function convertUTCTimestamp ', 'error' );
		 return;
	}
	
	if ( ! isset($databaseTimestampTimeZone) )
		 $databaseTimestampTimeZone = new DateTimeZone('UTC');

	$original_datetime->setTimeZone( $databaseTimestampTimeZone );

	return $original_datetime->getTimestamp(); 
}

/**
* Converts UTC unix timestamp to date string (in ChipInGo format)and return
* Drupal user timezone is used in conversationn
*
* @param  $utc_time_stamp   
*		unix utc time stamp 
* 
* @return
*     DateTime object with UTC DateTimeZone
* 
* $see drupal_get_user_timezone
* $see DateTimeZone
*/
function convertUnixUTCTimestampToUserTimezoneDateString( $utc_time_stamp ) {		
	return format_date( $utc_time_stamp, 'custom', ChipInGoConstants::$CHIPINGO_DATE_FORMAT, drupal_get_user_timezone() );		
}	






/**
*	@param $email
*		String. 	Email address to be checked
*/
function check_email_address_format( $email ) {

	// First, we check that there's one @ symbol, and that the lengths are right 
	if (!preg_match( "^[^@]{1,64}@[^@]{1,255}$", $email)) { 
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols. 
		return false; 
	} 
	// Split it into sections to make life easier 
	$email_array = explode("@", $email); 
	$local_array = explode(".", $email_array[0]); 

	for ($i = 0; $i < sizeof($local_array); $i++) { 
		if ( !preg_match("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) { 
			return false; 
		} 
	} 

	if (!preg_match("^\[?[0-9\.]+\]?$", $email_array[1])) { 
		// Check if domain is IP. If not, it should be valid domain name 
	
		$domain_array = explode(".", $email_array[1]); 
	
		if (sizeof($domain_array) < 2) { 
			return false; // Not enough parts to domain 
		} 
	
		for ($i = 0; $i < sizeof($domain_array); $i++) { 
			if (!preg_match("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) { 
				return false; 
			} 
		}
	}
	return true; 
}