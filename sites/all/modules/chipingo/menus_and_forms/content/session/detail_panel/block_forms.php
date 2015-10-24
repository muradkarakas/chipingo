<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/content/session/api' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/session/detail_panel/api' );


function session_detail_panel_form_submit_ajax_callback($form, &$form_state) {
  try {  
    // do not need to call submit handler. it is called in form submit process
    //session_detail_panel_form_submit_add($form, $form_state);  
    $ajaxCommands = array(
      '#type' => 'ajax', 
      '#commands' => []
    );
    $detailPanelFormCommand = reBuiltChipingoFormForAjax($form_state, 'session_detail_panel_form', '#session-detail-panel-ajax-div');
    $ajaxCommands['#commands'][] = $detailPanelFormCommand;
  } catch (Exception $ex) {
    $ajaxCommands = getFullAjaxAlertCommandArray($ex->getMessage());
  }  
  return $ajaxCommands;
}

/**
 * 
 * @param type $form
 * @param type $form_state
 * @return type
 */
function session_detail_panel_form_submit_ajax_callback_add($form, &$form_state) {
  $ajaxCommands = session_detail_panel_form_submit_ajax_callback($form, $form_state);
  $ajaxCommands['#commands'][] = [
    'command' => 'showMessage',
    'message' => t('Added') . ': ' . $form_state['values']['session_option-new-value']
  ];
  return $ajaxCommands;
}

/**
 * 
 * @param type $form
 * @param type $form_state
 * @return type
 */
function session_detail_panel_form_submit_ajax_callback_delete($form, &$form_state) {
  $ajaxCommands = session_detail_panel_form_submit_ajax_callback($form, $form_state);
  $ajaxCommands['#commands'][] = [
    'command' => 'showMessage',
    'message' => t('Deleted') . ': ' . $form_state['clicked_button']['#option']
  ];
  return $ajaxCommands;
}

/**
 * 
 * @param array $form
 * @param array $form_state
 */
function session_detail_panel_form_submit_add($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  _saveOption(
    $form_state['values']['chipingo_email'],
    $form_state['values']['qtag'],
    $form_state['values']['session_name'],    
    // TODO mk022 nasil benzersiz id uretecegiz. konuyu incele. bilgisayarin network interface numarasi eklenmeli. ayrıca key olarak her bir string ifade için unique üretilecek hash sonucu otomatik olarak aynı ifadenin iki kere girişine de engel olur.
    time() + $GLOBALS['user']->uid, // aynı anda girilen seçeneklerin birbirinden ayırt edilebilmesi için uid giriliyor  
    $form_state['values']['session_option-new-value'],
    $GLOBALS['user']->uid,
    $conn
  );
  Cassandra::disConnect($conn);  
}




/**
 * 
 * @param array $form
 * @param array $form_state
 */
function session_detail_panel_form_submit_delete($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  _deleteOption(
    $form_state['values']['chipingo_email'],
    $form_state['values']['qtag'],
    $form_state['values']['session_name'],    
    $form_state['clicked_button']['#option_timestamp'],
    $conn
  );
  Cassandra::disConnect($conn);
}

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function session_detail_panel_form_submit_save($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  $data = [
    'chipingo_email' => $form_state['values']['chipingo_email'],
    'qtag' => $form_state['values']['qtag'],
    'session_name' => $form_state['values']['session_name'],
    'session_option_type' => $form_state['values']['session_option_type'],
    'session_question_type' => $form_state['values']['session_question_type'],
    'session_restriction_ages' => $form_state['values']['session_restriction_ages'],
    'session_restriction_country' => $form_state['values']['session_restriction_country'],
    'session_restriction_gender' => $form_state['values']['session_restriction_gender'],
    'session_restriction_language' => $form_state['values']['session_restriction_language'],
    'user_id' => $GLOBALS['user']->uid
  ];    
  _saveSession($data, $conn);
  Cassandra::disConnect($conn);
}

/**
 *  This form is loaded by block view
 */
function session_detail_panel_form( $form, &$form_state ) {
 
  $chipingo_email = NULL;
  $qtag = NULL;
  $session_name = NULL;
      
  $current_path = current_path();
  $path_array = explode('/', $current_path);
  $arrCount = count($path_array);
  if ($arrCount != 5 and $arrCount != 2) {
    return NULL;
  }
  
  $conn = Cassandra::initializeCassandraSystem();
  
  switch($path_array[0] . '/' . $path_array[1]) {
    
    case 'yourqtags/edit':      
      if (isset($path_array[2])) {
        $chipingo_email = $path_array[2];
      } 
      if (isset($path_array[3])) {
        $qtag           = $path_array[3];
      } 
      if (isset($path_array[4])) {
       $session_name    = $path_array[4]; 
       if ($session_name == 'last') {
         $session_name = _getLastSessionUsedForThisQTag($chipingo_email, $qtag, $conn);
       }
      }
      break;
    case 'system/ajax':
      $chipingo_email = $form_state['values']['chipingo_email'];
      $qtag = $form_state['values']['qtag'];
      $session_name = $form_state['values']['session_name'];
      break;
    default:
      break;
  }
  
  if(is_null($chipingo_email) or is_null($qtag) or is_null($session_name)) {
    Cassandra::disConnect($conn);
    return NULL;
  }
  
  $publishedCount = __getPublishedQTagCount($chipingo_email, $conn);
 
  $form['session-detail-panel-ajax-div-start'] = [
    '#markup' => '<div id="session-detail-panel-ajax-div">'
  ];
  
  $form['chipingo_email'] = array(
		'#type' => 'hidden',
    '#value' => $chipingo_email
  );
  $form['qtag'] = array(
		'#type' => 'hidden',
    '#value' => $qtag
  );
  $form['session_name'] = array(
		'#type' => 'hidden',
    '#value' => $session_name
  );
  
  $form['div-start'] = array(
    '#markup' => '<div id="<?php print $block_html_id; ?>" class="chipingo-block">',
  );
  
  $form['session_info'] = array(
    '#markup' =>  '<strong>' . t('Session Name') . ' : </strong>' . 
                  $session_name .
                  '<br><br>',
  );
  
  $entity = _getSession($chipingo_email, $qtag, $session_name, $conn);
  
  if (count($publishedCount) == 0) {    
    $form = $form + _getRestrictionForm($form, $entity, $conn);  
  } else {
    $form['session_restriction_country'] = array(
      '#markup' =>  '<div style="text-align: center">' .
                    ' <font color="red">' .
                      t('Question is in PUBLISHED MODE now.') . '<br>' . 
                    ' </font>' .
                    ' <font color="blue">' .
                      t('Restriction settings are as below.') . '<br><br>' .
                    ' </font>' .
                    ' <font color="black">' .
                      '<strong>' . t('Gender') . '</strong> : <i>' . ChipInGoConstants::$QTAG_SESSION_RESTRICTION_GENDER[(isset($entity['session_restriction_gender']) ? $entity['session_restriction_gender'] : 0 )] . '</i><br>' .                 
                      '<strong>' . t('Age') . '</strong> : <i>' . ChipInGoConstants::$QTAG_SESSION_RESTRICTION_AGE[(isset($entity['session_restriction_ages']) ? $entity['session_restriction_ages'] : 0 )] . '</i><br>' .                 
                      '<strong>' . t('Language') . '</strong> : <i>' . ChipInGoConstants::$QTAG_SESSION_RESTRICTION_LANGUAGE[(isset($entity['session_restriction_language']) ? $entity['session_restriction_language'] : 0 )] . '</i><br>' .                 
                      '<strong>' . t('Country') . '</strong> : <i>' . ChipInGoConstants::$QTAG_SESSION_RESTRICTION_COUNTRY[(isset($entity['session_restriction_country']) ? $entity['session_restriction_country'] : 0 )] .  '</i><br>' .
                    ' </font>' .                     
                    '</div>',
    );
  }
  
  $form['panel-end'] = [
    '#markup' =>    ' </div>'.
                    '</div>'
  ];    
  
  if (_getViewMode() == 'A' and count($publishedCount) == 0) {
    $form['session_save'] = array(
      '#type' => 'submit',
      '#name' => 'save_session_button',
      '#default_value' => t('Save Restrictions'),
      '#submit' => array('session_detail_panel_form_submit_save'),
      '#prefix' => '<div style="text-align: right; margin-top: 5px;">',
      '#suffix' => '</div>',
      '#attributes' => array(
        'class' => array( 'btn btn-primary btn-sm' )
      )
    );
  }  
  
  $form['div-end'] = array(
    '#markup' => '</div>',
  ); 
  
  $form['session-detail-panel-ajax-div-end'] = [
    '#markup' => '</div>'
  ];
  
  Cassandra::disConnect($conn);
  return $form;
}

/**
 * 
 * @param type $form
 * @return string
 */
function _getRestrictionForm($form, $entity, $conn) {
    
  $form['panel-start'] = [
    '#markup' =>    '<div class="tabbable chipingo-block-body" style="padding: 0px; min-height: 200px;">'.
                    ' <ul class="nav nav-tabs">' .
                    '   <li class="active">'.
                    '     <a href="#tab1" data-toggle="tab">' . t('Options') . '</a>'.
                    '   </li>' 
  ];
  
  if ((isset($_SESSION["user_view_mode"]) and $_SESSION["user_view_mode"] != 'S') ) {
    $form['panel-start']['#markup'] = $form['panel-start']['#markup'] .
                    '   <li>' .
                    '     <a href="#tab2" data-toggle="tab">' . t('Question Type') . '</a>'.
                    '   </li>'.
                    '   <li>'.
                    '     <a href="#tab3" data-toggle="tab">' . t('Option Type') . '</a>'.
                    '   </li>'.
                    '   <li>'.
                    '     <a href="#tab4" data-toggle="tab">' . t('Restrictions') . '</a>'.
                    '   </li>';
  }
  
  $form['panel-start']['#markup'] = $form['panel-start']['#markup'] .                    
                    ' </ul>'.
                    ' <div class="tab-content">';

  // OPTIONS START
  $form = $form + getOptions($entity, $conn);
  // OPTIONS END
  
  $form['session_question_type'] = array(
		'#type' => 'radios',
		'#title' => t('Question type') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_QUESTION_TYPE,
		'#default_value' => (isset($entity['session_question_type']) ? $entity['session_question_type'] : 0 ),
    '#prefix' =>  '<div class="tab-pane" id="tab2" style="text-align: center">' . 
                  ' <br>' .
                  ' <div style="width: 100px; display: inline-block; text-align: left">',
    '#suffix' =>  ' </div>' .
                  '</div>'
	);
  
  $form['session_option_type'] = array(
		'#type' => 'radios',
		'#title' => t('Option type') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_OPTION_TYPE,
		'#description' => 'Define how to client respond to the your Chipingo',
		'#default_value' => (isset($entity['session_option_type']) ? $entity['session_option_type'] : 0 ),
    '#prefix' =>  '<div class="tab-pane" id="tab3" style="text-align: center">' . 
                  ' <br>' .
                  ' <div style="width: 100px; display: inline-block; text-align: left">',
    '#suffix' =>  ' </div>' .
                  '</div>'
	);
  
  $form['session_restriction_gender'] = array(
		'#type' => 'radios',
		'#title' => t('Gender') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_GENDER,
		'#default_value' => (isset($entity['session_restriction_gender']) ? $entity['session_restriction_gender'] : 0 ),
    '#prefix' =>  '<div class="tab-pane" id="tab4" style="text-align: left; padding-left: 10px">' . 
                  '<br>' .
                  '<table style="width:100%"><tr><td style="width:50%">',
                  //' <div style="width: 100px; display: inline-block; text-align: left">',
	);
  
  $form['session_restriction_ages'] = array(
		'#type' => 'radios',
		'#title' => t('Age') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_AGE,
		'#default_value' => (isset($entity['session_restriction_ages']) ? $entity['session_restriction_ages'] : 0 ),
    '#prefix' => '</td><td>',
    '#suffix' => '</td></tr></table>'
	);		
  
  $form['session_restriction_language'] = array(
		'#type' => 'radios',
		'#title' => t('Language') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_LANGUAGE,
		'#default_value' => (isset($entity['session_restriction_language']) ? $entity['session_restriction_language'] : 0 ),
    '#prefix' => '<table style="width:100%"><tr><td style="width:50%">'
	);
  
  $form['session_restriction_country'] = array(
		'#type' => 'radios',
		'#title' => t('Country') . ' : ',
		'#options' => ChipInGoConstants::$QTAG_SESSION_RESTRICTION_COUNTRY,
		'#default_value' => (isset($entity['session_restriction_country']) ? $entity['session_restriction_country'] : 0 ),
    '#prefix' =>  '</td><td>',
    '#suffix' =>  '</td></tr></table>' .
                  '</div>'
	);
  
  $form['#attached'] = array(
    'js' => array(
        drupal_get_path('module', 'chipingo') . '/js/chipingo.js' => array(),
      ),
  );
  
  return $form;
}

/**
 * 
 * @return array
 */
function getOptions($entity, $conn) {
  //$entity['chipingo_email'], 
  //$entity['qtag'], 
  //$entity['session_name']
      
  $form['session_option_start'] = array(
    '#markup' =>  '<div class="tab-pane active" id="tab1" style="width: 100%; valign: middle; text-align: center">' . 
                  ' <br>' .
                  '<table border=0 style="width: 90%; margin-left: auto; margin-right: auto;">',
  );
  
  $options = _getOptions($entity, $conn);
  
  foreach ($options as $record) { 
    
    $form['session_option-' . $record['option_timestamp'] . '-option-timestamp'] = array(
      '#type' => 'hidden',
      '#value' => $record['option_timestamp'],
      '#prefix' =>  ' <tr><td>',
      '#suffix' =>  ' </td>'
    );    
    $form['session_option-' . $record['option_timestamp'] . '-title'] = array(
      '#markup' => t('Option') . ' :&nbsp;&nbsp;',
      '#prefix' =>  ' <tr><td style="text-align: right; padding-bottom: 15px;">',
      '#suffix' =>  ' </td>'
    );    
    $form['session_option-' . $record['option_timestamp'] . '-value'] = array(
      '#type' => 'textfield',
      '#value' => $record['option'], 
      '#prefix' =>  ' <td >',
      '#suffix' =>  ' </td>'
    );
    $form['session_option-' . $record['option_timestamp'] . '-delete-button'] = array(
      '#type' => 'submit',
      '#name' => 'delete' . $record['option_timestamp'],
      '#default_value' => t('Delete'),    
      '#option_timestamp' => $record['option_timestamp'],
      '#option' => $record['option'],
      '#submit' => ['session_detail_panel_form_submit_delete'],
      '#attributes' => array('style' => 'min-width: 75px;'),
      '#prefix' =>  '<td style="padding-bottom: 15px;">',
      '#suffix' =>  '</td></tr>',
      '#ajax' => [
        'callback' => 'session_detail_panel_form_submit_ajax_callback_delete',
        'wrapper' => 'quick-chipingo-list-ajax-div', 
        'method' => 'replace',
      ],
    );
  }
  
  if (count($options) <= 20) {
    // NEW RECORD START
    $form['session_option-new-title'] = array(
      '#markup' => t('New Option') . ' :&nbsp;&nbsp;',
      '#prefix' =>  ' <tr><td style="text-align: right; padding-bottom: 15px;">',
      '#suffix' =>  ' </td>'
    );

    $form['session_option-new-value'] = array(
      '#type' => 'textfield',
      '#attributes' => array(''),
      '#prefix' =>  ' <td>',
      '#suffix' =>  ' </td>'
    );
    
    $form['session_option-new-add-button'] = array(
      '#type' => 'submit',
      '#value' => t('Add'),
      '#submit' => ['session_detail_panel_form_submit_add'],
      '#attributes' => array('style'=>'min-width: 75px'),
      '#prefix' =>  '<td style="padding-bottom: 15px;">',
      '#suffix' =>  '</td></tr>',
      '#ajax' => [
        'callback' => 'session_detail_panel_form_submit_ajax_callback_add',
        'wrapper' => 'quick-chipingo-list-ajax-div', 
        'method' => 'replace',
      ],
    );
  }
  // NEW RECORD END
    
  $form['session_option_end'] = array(
    '#markup' =>  '</table>' .
                  '</div>'
  );
  
  return $form;
}