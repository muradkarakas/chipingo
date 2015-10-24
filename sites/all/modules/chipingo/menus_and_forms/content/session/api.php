<?php

/**
 * Returns a single session row
 * 
 * @param type $chipingo_email
 * @param type $qtag
 * @param type $session_name
 */
function _getSession($chipingo_email, $qtag, $session_name, $conn) {
  $sessionTable = new SessionTable($conn);
  $result = (array) $sessionTable->getSession($chipingo_email, $qtag, $session_name);
  if (isset($result[0])) {
    $entity = $result[0];
  } else {
    $entity = [];
  }
  return $entity;
}

/**
 * 
 * @param array $data
 */
function _saveSession($data, $conn) {
  $sessionTable = new SessionTable($conn);
  $sessionTable->db_insert_all($data);
  _setLastSessionUsedForThisQTag($data, $conn);
  drupal_set_message( t('Session saved') );
}