<?php


/**
 * 
 * @param text $message
 */
function getFullAjaxAlertCommandArray($message) {
  $commands[] = array(
    'command' => 'alert',
    'text' => $message
  );
  $ajaxArray = array(
    '#type' => 'ajax',
    '#commands' => $commands,
  );
  return $ajaxArray;
}


/**
 * 
 * @param type $form_id
 * @param type $div_id
 * @return type
 */
function builtChipingoFormForAjax($form_id, $div_id) {
  $form_state = [];
  $new_form_array = drupal_build_form($form_id, $form_state);
  $new_form = drupal_render($new_form_array);
  $commands = array();
  $commands[] = ajax_command_html($div_id, $new_form);
  return $commands[0];
}

/**
 * 
 * 
 * @param type $form_state
 * @param type $form_id
 *    function name
 * 
 * @param type $div_id
 *    Put # character for id
 *    Ex: '#chipingo_form_div'
 * 
 * @return array
 *  ajax command array
 */
function reBuiltChipingoFormForAjax($form_state, $form_id, $div_id) {
  $new_state = array();
  $new_state['build_info'] = $form_state['build_info'];
  $new_state['rebuild'] = TRUE;
  $new_state['values'] = $form_state['values'];
  $new_state += form_state_defaults();
  $new_form_array = drupal_rebuild_form($form_id, $new_state);
  $new_form = drupal_render($new_form_array);
  $commands = array();
  $commands[] = ajax_command_html($div_id, $new_form);
  return $commands[0];
}


/**
 * Example usage:
 *  $array = array(
    'PTT757LP4',
    'PTT757A',
    'PCT757B',
    'PCT757LP4EV'
  );
  echo longest_common_substring($array);
  // => T757
 * 
 * @param array $words
 * @return text
 */
function _longestCommonString($words)
{
  $words = array_map('strtolower', array_map('trim', $words));
  $sort_by_strlen = create_function('$a, $b', 'if (strlen($a) == strlen($b)) { return strcmp($a, $b); } return (strlen($a) < strlen($b)) ? -1 : 1;');
  usort($words, $sort_by_strlen);
  // We have to assume that each string has something in common with the first
  // string (post sort), we just need to figure out what the longest common
  // string is. If any string DOES NOT have something in common with the first
  // string, return false.
  $longest_common_substring = array();
  $shortest_string = str_split(array_shift($words));
  while (sizeof($shortest_string)) {
    array_unshift($longest_common_substring, '');
    foreach ($shortest_string as $ci => $char) {
      foreach ($words as $wi => $word) {
        if (!strstr($word, $longest_common_substring[0] . $char)) {
          // No match
          break 2;
        } // if
      } // foreach
      // we found the current char in each word, so add it to the first longest_common_substring element,
      // then start checking again using the next char as well
      $longest_common_substring[0].= $char;
    } // foreach
    // We've finished looping through the entire shortest_string.
    // Remove the first char and start all over. Do this until there are no more
    // chars to search on.
    array_shift($shortest_string);
  }
  // If we made it here then we've run through everything
  usort($longest_common_substring, $sort_by_strlen);
  return array_pop($longest_common_substring);
}

/**
 * Returns a new array 
 * @param array $orjinalArray
 * @param text $keyColumnName
 * @param text $valueColumnName
 * @return array
 */
function _convertKeyValueArray($orjinalArray, $keyColumnName, $valueColumnName) {
  $newlist = [];
  foreach($orjinalArray as $record) {
    $newlist[$record[$keyColumnName]] = $record[$valueColumnName]; 
  }
  return $newlist;
}

/**
 * Result set is an array of array
 * Example: 
 *  // sorting by REPLY_COUNT in desc order and sorting by option_timestamp in asc order
    $newResultSet = __sortMultiDimensinalDBResultSet( $resultSet,
                                                      'reply_count',
                                                      SORT_DESC,
                                                      'option_timestamp',
                                                      SORT_ASC
                                                    );
 * 
 * @param array $resultSet
 * @param type $column1_name
 * @param type $column1_order
 * @param type $column2_name
 * @param type $column2_order
 * @return type
 */
function __sortMultiDimensinalDBResultSet($resultSet, $column1_name, $column1_order,
                                                      $column2_name, $column2_order  ) {    
  if (! is_array($resultSet)) {
    $resultSet = (array) $resultSet;
  }
  if (count($resultSet)==0) {
    return $resultSet;
  }

  // Obtain a list of columns
  foreach ($resultSet as $key => $row) {
      $column1_value[$key]  = $row[ $column1_name ];
      $column2_value[$key] = $row[ $column2_name ];
  }
  // Sort the data with volume descending, edition ascending
  // Add $data as the last parameter, to sort by the common key
  array_multisort($column1_value, $column1_order, $column2_value, $column2_order, $resultSet);
  return $resultSet;
}
  
/**
* Returns html code to show icon
 * 
*	@param int $chipingo_status 
* @param int $published_qtag_count
*	@return string
*/
function __getChipingoIconHtml( $chipingo_status, $published_qtag_count ) {
	if ( $chipingo_status == 1 )
		 return '<i class="fa fa-thumbs-down fa-lg"></i>';
	
	// If it is valid, check if it is running or not
	if ( $published_qtag_count > 0 ) 
		 return '<i class="fa fa-cog fa-spin fa-lg"></i>';
	else 
		 return '<i class="fa fa-thumbs-up fa-lg"></i>';
}

  
