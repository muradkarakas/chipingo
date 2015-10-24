<?php

function _getLastSessionUsedForThisQTag($chipingo_email, $qtag, $conn) {
  $qtagTable = new QTagTable($conn); 
  $result = (array) $qtagTable->getQTag($chipingo_email, $qtag);
  if (count($result) > 0 and
      isset($result[0]['last_session_name_used']) and 
      count($result[0]['last_session_name_used'])>0) {
    $session_name = $result[0]['last_session_name_used'];
  }
  return $session_name;
}

/**
 * 
 * @param type $data
 * @param type $conn
 */
function _setLastSessionUsedForThisQTag($data, $conn) {
        
  $qtagTable = new QTagTable($conn);
  $data['last_session_name_used'] = $data['session_name'] ;
  $dataToInsert =  array_intersect_key($data, $qtagTable->getColumns());
  $qtagTable->db_insert_all($dataToInsert);
  //$data['session_name'] = $data['last_session_name_used'];
}

/**
 * Array of "valid chipingos owned by the user $user_id 
 * @param int $user_id 
 *    default value is current user
 * @return array
 */
function _getUserChipingoComboboxOptions($conn) {
  $user_id = (int) $GLOBALS['user']->uid;
  $chipingo_list = _getUsersChipingos($user_id, $conn);
  $valid_chipingo_list = \array_filter(
        (array) $chipingo_list, 
        function ($v) { 
          return $v['chipingo_status'] == 0 or 
                 $v['chipingo_status'] == 2;
        }
  );
  $cb_list = [];
  foreach ($valid_chipingo_list as $key => $value) {
    $cb_list[$value['chipingo_email']] = 
          __getPublisherSourcePath($value['chipingo_email'])  .
          '&nbsp;&nbsp;' . $value['chipingo']; 
  }
  return $cb_list;
}

/**
 * Insert a QTag into db qtag tables and session tables
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $question
 * @param int $user_id
 * @param text $session_name
 *    This value is used whether this QTag is a new record or not.
 *    In other words, if $session_name is null, a new record is to be inserted into session table as a default session for the QTag
 */
function _saveQTag($chipingo_email, $qtag, $question, $user_id, $session_name, $conn) {
  $qtagTable = new QTagTable($conn);
  $data = [
    'chipingo_email' => $chipingo_email,
    'qtag' => $qtag,
    'question' => $question,
    'user_id' => $user_id
  ];      
  // creates a new default session for this qtag record if $session_name is NULL
  if ( is_null($session_name) ) {
    $sessionTable = new SessionTable($conn);
    $data['session_name'] = date("d-m-Y");
    $backup = $data['question'];
    unset($data['question']);
    $sessionTable->db_insert_all($data);
    $data['question'] = $backup;
  }    
  $data['last_session_name_used'] = $data['session_name'];
  unset($data['session_name']);
  $qtagTable->db_insert_all($data);
  $data['session_name'] = $data['last_session_name_used'];
}

/**
 * 
 * @param text $chipingo_email
 * @param text $qtag
 */
function __deleteQTag($chipingo_email, $qtag, $conn) {
  $qtagTable = new QTagTable($conn);
  $data = [
    'chipingo_email' => $chipingo_email,
    'qtag' => $qtag,
    'user_id' => $GLOBALS['user']->uid,
   ];      
  $qtagTable->db_delete_all($data);  
  $sessionTable = new SessionTable($conn);
  $sessionTable->deleteByChipingoAndQTag($data);
}

/** 
*	Returns published qtag count of $chipingo_email.
*	filtered by current user
*
*	@param text $chipingo_email	
*
*	@return int	  
*/	
function __getPublishedQTagCount($chipingo_email, $conn) {
  if (!isset($chipingo_email)) {
    throw new Exception('chipingo_email value required');
  }
  $searchPathsTable = new SearchPathsTable($conn);
  $result = $searchPathsTable->getPublishedQTagCount($chipingo_email);
	return $result;
}