<?php

/**
*	Returns all qtag of a spesific chipingos (filtered by current user )
 * 
* @return array
*/
function _getQTags($chipingo_email, $conn) {
	if (!isset($chipingo_email)) {
    throw new Exception('chipingo_email value required');
  }
  $sessionTable = new SessionTable($conn);
  $result = $sessionTable->getQTags($chipingo_email);
  return $result;
}

/**
 * Returns user's publisher list (distinct and valid )
*  
 * @return array
*/
function _getCurrentUserQuickChipingoList($conn) {
  $chipingoTable = new ChipingoTable($conn);
  $result = $chipingoTable->getChipingosByUid((int) $GLOBALS['user']->uid);
  return $result;
}

/**
 * Returns unique email name part of full email list given by $publisher
 * 
 * @return array 
 */
function _getUniqueEmailFromChipingoEmailList($publisher, $conn) {
  $chipingo_domain_list = [];
  $userChipingoList = _getCurrentUserQuickChipingoList($conn);
  foreach($userChipingoList as $chipingo) {
    //$temp = explode('@', $chipingo['publisher']);
    if ($chipingo['publisher'] == $publisher) {
      if (! in_array($chipingo['publisher'], $chipingo_domain_list)) {
        $chipingo_domain_list[] = [ 'chipingo' => $chipingo['chipingo'],
                                    'publisher' => $chipingo['publisher'],
                                    'chipingo_status' => $chipingo['chipingo_status'],
                                    'chipingo_email' => $chipingo['chipingo_email']
                                  ];
      } 
    }
  }
  return $chipingo_domain_list;
}

/**
 * 
 * @param array $full_chipingo_email_list
 */
function _getUniqueDomainNameFromChipingoEmailList($full_chipingo_email_list) {
  $chipingo_domain_list = [];
  foreach($full_chipingo_email_list as $chipingo) {
    //$temp = explode('@', $chipingo['publisher']);
    if (! in_array($chipingo['publisher'], $chipingo_domain_list)) {
      $chipingo_domain_list[] = [
        'publisher' => $chipingo['publisher'],
        'chipingo_email' => $chipingo['chipingo_email'],
      ];
    }    
  }
  return $chipingo_domain_list;
}

