<?php

/**
 * Insert a new row or update an existing row
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @param int $option_timestamp
 * @param text $option
 * @param int $user_id
 */
function _saveOption($chipingo_email, $qtag, $session_name, $option_timestamp, $option, $user_id, $conn) {
  $QTagOptionsTable = new QTagOptionsTable($conn);
  $data = [
    'chipingo_email' => $chipingo_email, 
    'qtag' => $qtag, 
    'session_name' => $session_name, 
    'option_timestamp' => $option_timestamp, 
    'option' => $option,
    'user_id' => $user_id
  ];
  $QTagOptionsTable->db_insert_all($data);
}


/**
 * Delete an option
 * 
 * @param type $chipingo_email
 * @param type $qtag
 * @param type $session_name
 * @param type $option_timestamp
 * @param type $user_id
 */
function _deleteOption($chipingo_email, $qtag, $session_name, $option_timestamp, $conn) {
  $QTagOptionsTable = new QTagOptionsTable($conn);
  $data = [
    'chipingo_email' => $chipingo_email, 
    'qtag' => $qtag, 
    'session_name' => $session_name, 
    'option_timestamp' => $option_timestamp, 
    //'user_id' => $user_id
  ];
  $QTagOptionsTable->db_delete_all($data);
}

/**
 * Returns Options of a chipingo as array
 * Array does not contain primary columns
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return array
 */
function _getOptions($entity, $conn) {
  $QTagOptionsTable = new QTagOptionsTable($conn);
  $data = [
    'chipingo_email' => $entity['chipingo_email'], 
    'qtag' => $entity['qtag'], 
    'session_name' => $entity['session_name'], 
  ];
  $result = $QTagOptionsTable->getOptionsByChipingo($data);
  return $result;
}