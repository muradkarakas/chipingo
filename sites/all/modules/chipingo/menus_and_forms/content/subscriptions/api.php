<?php

/**
 * 
 * @param type $sourceText
 * @param array $alternatives
 */
function _sortByBestMatch($sourceText, $alternatives) {
  $orderedlist = [];
  
  foreach($alternatives as $key => $value) {
    $levenshteinDistance =  levenshtein($sourceText, $value, 1, 1, 1);  // ins,rep,del
    $longestCommonString =  strlen(_longestCommonString( [$sourceText,$value]) );
                                
    $orderedlist[] = [ 'distance' => $longestCommonString, //+ $levenshteinDistance, 
                       'value' => $value, 
                       'key' => $key,
                       'levenshteinDistance' => $levenshteinDistance,
                       'longestCommonString' => $longestCommonString
                     ];   
  }
  
  $newResultSet = __sortMultiDimensinalDBResultSet( $orderedlist,
                                                    'distance',
                                                    SORT_DESC,
                                                    'key',
                                                    SORT_ASC
                                                  );
  $i = 0;
  $finalList = [];
  foreach($newResultSet as $record) {
    $finalList[$record['key']] = $record['value'];
    $i++;
    if ($i==10) {
      break;
    }
  }
  return $finalList;
}

/**
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @return array
 */
function _getAlternativeOptions($chipingo_email, $qtag, $session_name, $conn) {
  $data = [
    'chipingo_email' => $chipingo_email, 
    'qtag' => $qtag, 
    'session_name' => $session_name
  ];
  $all_options = (array) _getOptions($data, $conn);
  $all_options = _convertKeyValueArray($all_options,'option_timestamp', 'option');
  return $all_options;
}

/**
 * 
 * @param int $user_id
 * @param text $chipingo_email
 */
function _unSubscribeUser($user_id,$chipingo_email, $conn) {
  $UserFavoritesTable = new UserFavoritesTable($conn);
  $data = [
    'user_id' => $user_id,
    'chipingo_email' => $chipingo_email
  ];
  $UserFavoritesTable->db_delete_all($data);
}

/**
 * 
 * @param int $user_id
 * @param text $chipingo_email
 */
function _subscribeUser($user_id,$chipingo_email, $conn) {
  $UserFavoritesTable = new UserFavoritesTable($conn);
  $data = [
    'user_id' => $user_id,
    'chipingo_email' => $chipingo_email
  ];
  $UserFavoritesTable->db_insert_all($data);
}

/**
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @param int $option_chosen
 * @param int $user_id
 */
function _saveUserOption($chipingo_email, $qtag, $session_name, $option_chosen, $user_id, $conn) {
  $qtagRepliesTable = new QTagRepliesTable($conn);
  $data = [
    'chipingo_email' => $chipingo_email, 
    'qtag' => $qtag, 
    'session_name' => $session_name, 
    'option_timestamp' => $option_chosen,
    'user_id' => $user_id
  ];
  $qtagRepliesTable->db_insert_all($data);
}

/**
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @param int $user_id
 */
function _deleteUserOption($chipingo_email, $qtag, $session_name, $user_id, $conn) {
  $qtagRepliesTable = new QTagRepliesTable($conn);
  $data = [
    'chipingo_email' => $chipingo_email, 
    'qtag' => $qtag, 
    'session_name' => $session_name, 
    'user_id' => $user_id
  ];
  $qtagRepliesTable->deleteUserOption($data);
}

/**
 * 
 * 
 * @param int $uid
 * @param Cassandra\Connection $conn
 * @return array
 */
function _getUserFavorites($uid, $conn) {
  $userChipingoList = (array) __getUserFavorites($uid,$conn);
  $SessionTable = new SessionTable($conn);
  $chipingoTable = new ChipingoTable($conn);
  $now = time();
  foreach($userChipingoList as $key => $userChipingo) {
    $qtags = $SessionTable->getQTags($userChipingo['chipingo_email']);
    if (count($qtags) > 0) {
      if ($qtags[0]['publish_start_date'] < $now and $qtags[0]['publish_end_date'] > $now) {
        $userChipingoList[$key] = $userChipingoList[$key] + $qtags[0]; 
        $userChipingoList[$key]['user_id'] = $qtags[0]['user_id'];
      }
    }
    $chipingo = $chipingoTable->getChipingoByChipingo($userChipingo['chipingo_email']);
    if (!is_null($chipingo)) {
      $userChipingoList[$key] = $userChipingoList[$key] + $chipingo;
    } else {
      /* TODO mk020 Kullanici bir chipingo'yu favorilerine eklemiş ama favorisne eklediği 
       * chipingo artık mevcut değil.
       * Bu durumda bu kullanıcının favorisinden bu chipingo adresini silmeliyiz veya 
       * kendisinin silmesini sağlamak maksadı ile ekranda listeleyerek bir bilgilendirme mesajı 
       * vermeliyiz.
      */
    }
  }
  return $userChipingoList;
}

/**
 * Returns a user's favorites chipingos.
 * active connection object must be provided
 * 
 * @param int $uid
 * @param Cassandra\Connection $conn
 * @return type
 */
function __getUserFavorites($uid, $conn) {  
  $favorites = new UserFavoritesTable($conn);
  $resultset = $favorites->getFavoritesByUid($uid);
  return $resultset;
}