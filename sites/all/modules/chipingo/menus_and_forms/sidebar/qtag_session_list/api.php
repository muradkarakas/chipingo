<?php

/**
 * 
 * @param int $user_id
 * @param type $qtag
 */
function _getSessions($chiping_email, $qtag, $conn) {
  $sessionTable = new SessionTable($conn);
  $result = $sessionTable->getSessions($chiping_email, $qtag);
  return $result;
}

/**
 * 
 * $data contains these keys: 
 *    $chipingo_email, 
 *    $qtag, 
 *    $session_name, 
 *    $publish_start_time, 
 *    $publish_end_time
 * 
 * @param array $data
 */
function _publishQTag($data, $conn) {
  
  // TODO mk010 "Option Type" ayarı "Strict" olan qtag'lar option eklenmeden yayınlanamazlar. Kontrol et ve engelle
  /*
  $data = [
    'chipingo_email' => $chipingo_email,
    'qtag' => $qtag,
    'session_name' => $session_name,
    'publish_start_date' => $publish_start_time,
    'publish_end_date' => $publish_end_time,
  ]; */
  
// setting session table
  $sessionTable = new SessionTable($conn);
  $sessionTable->publishSession($data);  
  // insert search table
  $searchPathsTable = new SearchPathsTable($conn);
  $searchPathsTable->db_insert_all($data);    
  drupal_set_message( 
      $data['qtag'] . ' ' . 
      t('will be published on') . ' ' . 
      $data['publish_start_date'] . ' ' . 
      t('and finilized on') . ' ' . 
      $data['publish_end_date']);
}