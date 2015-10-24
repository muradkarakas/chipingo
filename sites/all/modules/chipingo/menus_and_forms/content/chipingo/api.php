<?php
/**
 * $file
 *  Business logic file.
 *  This is the only file to allowed Cassandra. Do not access cassandra from other
 * file
 *  
 * @see menu_forms.php
 * @see api.php
 * @see menu_items.php
 */
//*****************************************************************************************

/**
 * Returns users' chipingos.
 * 
 * @param int $uid
 *  if not provided, current user's uid is used
 * 
 * @return array
 */
function _getUsersChipingos($uid, $conn) {
  if (!isset($uid)) {
    $uid = $GLOBALS['user']->uid;
  }
  $ChipingoTable = new ChipingoTable($conn);
  $result = $ChipingoTable->getChipingosByUid((int) $uid);  
  return $result;
}

/**
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param Cassandra\Connection $conn
 */
function _getQTag($chipingo_email, $qtag, $conn) {
  if( !isset($chipingo_email)) {
    throw new ChipingoException( t('Chipingo parameter is mandatory') );
  }
  if (!isset($qtag)) {
    throw new ChipingoException( t('qtag parameter is mandatory') );
  }
  $qtagTable = new QTagTable($conn);
  $result = (array) $qtagTable->getQTag($chipingo_email, $qtag); 

  // We are reading chipingo detailed info and injecting it to the data strucete
  $ChipingoTable = new ChipingoTable($conn);
  $chipingo = $ChipingoTable->getChipingoByChipingo($chipingo_email);
  if (!is_null($chipingo)) {
    return (isset($result[0]) ? $result[0] : []) + $chipingo;
  } else {
    /* TODO mk021 Silinen bir chipingo ile tanımlanmış QTag'lar mevcut. QTag'a ait chipingo
     * bilgilerine erişilemiyor. 
     * Ne yapacağız ?
     *    Normal şartlar altında bir chipingo silindiği zaman ona ait tüm bilgilerin (örnek: favori 
     * tablosunda, qtag tablosunda vs vs) silinmesi gerekiyor.
     * - Bir çok tablodan silinmesi gerekiyor ve zahmatli bir iş.
     * - Sunumcuya ve veritabanına yük getirecek.
     * - Silme işlemi yarıda kalabilir
     * - Bir çok ilişkiler var. Cascade işleminde bazı basamaklar illaki unutulacak
     * Bu konuyu düşün ? ? ?
     * 
     */
    return (isset($result[0]) ? $result[0] : []);
  }
  
}

/** 
*	Returns qtag count of $chipingo_id of $user_id.
*
*	@param
*		$chipingo_id  :	
*
*	@return
*		int	:	there are how many qtags in db ownned by $chipingo_id
*/	
function _getChipingoQtagCount($user_id, $chipingo_id, $conn) {
  //$conn = Cassandra::initializeCassandraSystem();
  $qtagTable = new QTagTable($conn);
  $result = $qtagTable->getUserChipingoQTagCount($user_id, $chipingo_id);    
  //Cassandra::disConnect($conn);
	return $result;
}

/**
 * Delete a chipingo
 * Chipingo should belongs to current user. Otherwise, it will not be deleted and 
 * no exception will be raised.
 * 
 * @param array $data
 *    consists of "chipingo" and "uid".
 *    Should include at least chipingo value. If not provided, current user's id 
 * will be used.
 */
function _deleteChipingo($data, $conn) {
  if( !isset($data['chipingo_email'])) {
    throw new ChipingoException( t('chipingo_email parameter is mandatory') );
  }
  if (!isset($data['user_id'])) {
    $data['user_id'] = $GLOBALS['user']->uid;
  }
  $ChipingoTable = new ChipingoTable($conn);
  $ChipingoTable->db_delete_all($data);
  send_gcm_notify(NULL, 'Deleted:' . $data['chipingo_email']);
}

/**
 * Insert a row into database
 * 
 * @param text $chipingo
 * @param int $user_id
 * @param int $chipingo_status
 * @param int $default_chipingo
 * @throws ChipingoException
 */
function _addNewChipingo( $chipingo,
                          $publisher,
                          $chipingo_email,
                          $user_id,
                          $chipingo_status = 1, // default one is validated by default
                          $default_chipingo = 0,
                          $conn
                        ) {
  
  if (!isset($chipingo) or strlen($chipingo)==0) {
    throw new ChipingoException( t('Chipingo is mandatory') );
  }
  if (!isset($publisher) or strlen($publisher)==0) {
    throw new ChipingoException( t('Publisher is mandatory') );
  }
  if (!isset($chipingo_email) or strlen($chipingo_email)==0) {
    throw new ChipingoException( t('E-mail is mandatory') );
  }
  $chipingo_email = filter_var($chipingo_email, FILTER_SANITIZE_EMAIL);
  if(filter_var($chipingo_email, FILTER_VALIDATE_EMAIL) === false) {
    throw new ChipingoException( t('E-mail value is not valid') );
  }
  if (!isset($user_id)) {
    throw new ChipingoException( t('User id is mandatory') );
  }  
  $data = [ 'chipingo' => $chipingo,
            'publisher' => $publisher,
            'user_id' => $user_id,
            'chipingo_email' => $chipingo_email,
            'chipingo_status' => $chipingo_status, 
            'default_chipingo' => $default_chipingo
  ];
  $ChipingoTable = new ChipingoTable($conn);
  $ChipingoTable->db_insert_all($data);
  send_gcm_notify(NULL, 'Added: ' . $chipingo . ' by ' . $publisher);
  return $data;
}

/**
 * Returns a single chipingo from database
 * 
 * @param text $chipingo
 * @return array
 */
function _getChipingoByChipingo($chipingo_email, $conn) {
  if (!isset($chipingo_email)) {
    throw new ChipingoException( t('ChipInGo e-mail is mandatory') );
  }
  $ChipingoTable = new ChipingoTable($conn);
  $result = $ChipingoTable->getChipingoByChipingo($chipingo_email);
  return $result;
}

