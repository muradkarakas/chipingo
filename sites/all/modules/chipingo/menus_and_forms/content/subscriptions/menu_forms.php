<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/content/subscriptions/api' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/chipingo/api' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/session/detail_panel/api' );

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function cancel_user_own_option($form, &$form_state) {
  drupal_goto('home');
}

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function save_user_own_option($form, &$form_state) {
  if ($form_state['input']['alternative-options'] == 0) {
    $option_chosen = time();
  } else {
    $option_chosen = $form_state['input']['alternative-options'];
  } 
  $conn = Cassandra::initializeCassandraSystem();
  _saveOption($form_state['clicked_button']['#chipingo_email'], 
              $form_state['clicked_button']['#qtag'], 
              $form_state['clicked_button']['#session_name'], 
              $option_chosen, 
              $form_state['clicked_button']['#user_input'], 
              $GLOBALS['user']->uid,
              $conn);
  _saveUserOption(
        $form_state['clicked_button']['#chipingo_email'],
        $form_state['clicked_button']['#qtag'],
        $form_state['clicked_button']['#session_name'],
        $option_chosen,
        $GLOBALS['user']->uid,
        $conn);
  Cassandra::disConnect($conn);
  
  $form_state['redirect'] = 'home';
}


/**
 * 
 * @param type $form
 * @param type $form_state
 * @return string
 */
function show_alternatives_form($form, &$form_state) {
  
  $conn = Cassandra::initializeCassandraSystem();
  $form['values-chipingo-block-start'] = array(
      '#markup' => 	
                    ' 	<div class="accordion chipingo-radius chipingo-div "> ' . 
                    '     <div class="chipingo-block-body">' 
                    //'       <font style="color:#F90">Chip</font> '.
                    //'       <font style="color:#093">In</font> '.
                    //'       <font style="color:#F00">Go</font> : ' 
  );
  
  $alternative_options =  _getAlternativeOptions( $_SESSION['chipingo_email'], 
                                                  $_SESSION['qtag'], 
                                                  $_SESSION['session_name'],
                                                  $conn);
  $alternative_options = _sortByBestMatch($_SESSION['user_input'], $alternative_options) +
                          [ 0 => '<strong><i>' . $_SESSION['user_input'] . '</i></strong>' ];
  $form['alternative-options'] = [
    '#type' => 'radios',
    '#title' => t('Your own option is selected. However, your option may match one of these') . ' : ',
    //'#description' => t('When a poll is closed, visitors can no longer vote for it.'),
    '#options' => $alternative_options,
    '#default_value' => 0,
    '#validated' => 'TRUE',
  ];  
  $form['save-user-own-option'] = array(
    '#type' => 'submit',
    '#name' => 'save-user-own-option',
    '#default_value' => t('Vote'),
    '#submit' => array('save_user_own_option'),
    '#chipingo_email' => $_SESSION['chipingo_email'],
    '#session_name' => $_SESSION['session_name'],
    '#qtag' => $_SESSION['qtag'],
    '#user_input' => $_SESSION['user_input'],
    '#prefix' => '<div style="text-align: right; margin-top: 5px;">',
    '#attributes' => array(
      'class' => array( 'btn btn-success btn-sm' )
    ),
  );
  $form['cancel-user-own-option'] = array(
    '#type' => 'submit',
    '#name' => 'cancel-user-own-option',
    '#default_value' => t('Cancel Voting'),
    '#submit' => array('cancel_user_own_option'),
    '#chipingo_email' => $_SESSION['chipingo_email'],
    '#session_name' => $_SESSION['session_name'],
    '#qtag' => $_SESSION['qtag'],
    '#attributes' => array(
      'class' => array( 'btn btn-primary btn-sm' )
    ),
    '#suffix' => '</div>',
  );
  
  $form['values-chipingo-block-stop'] = array(
		'#markup' => 	'	</div>' .
                  '</div>',
	);    
  Cassandra::disConnect($conn);
  return $form;  
}

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function delete_user_option($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  $form_state['clicked_button']['#chipingo_email'];
  $form_state['clicked_button']['#qtag'];
  $form_state['clicked_button']['#session_name'];
  _deleteUserOption(
        $form_state['clicked_button']['#chipingo_email'],
        $form_state['clicked_button']['#qtag'],
        $form_state['clicked_button']['#session_name'],
        $GLOBALS['user']->uid,
        $conn
      );
  Cassandra::disConnect($conn);
  drupal_set_message( t('Your options has been deleted') );
  return NULL;
}

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function save_user_option($form, &$form_state) {  
  
  // User has set his/her own option
  if (isset($form_state['input'][ 'options-others-own-' . $form_state['clicked_button']['#id'] ]) and
      drupal_strlen( $form_state['input'][ 'options-others-own-' . $form_state['clicked_button']['#id'] ] ) > 0) {
      // adding new option to the option table first
      $_SESSION['user_input'] = $form_state['input'][ 'options-others-own-' . $form_state['clicked_button']['#id'] ];
      $_SESSION['chipingo_email'] = $form_state['clicked_button']['#chipingo_email'];
      $_SESSION['qtag'] = $form_state['clicked_button']['#qtag'];
      $_SESSION['session_name'] = $form_state['clicked_button']['#session_name'];
      //drupal_goto('show_alternatives');
      $form_state['redirect'] = 'show_alternatives';
      return $form; //show_alternatives($form);
  }
  
  // which option is chosed ?
    $option_chosen = NULL;
    // detect whether publisher provided option is selected or not?
    if (isset($form_state['input'][ 'options-publisher-options-' . $form_state['clicked_button']['#id'] ]) ) {
      $option_chosen = $form_state['input'][ 'options-publisher-options-' . $form_state['clicked_button']['#id'] ];
    }
    // detect whether others' provided option is selected or not?
    if (isset($form_state['input'][ 'options-others-options-' . $form_state['clicked_button']['#id'] ] ) ) {
      if (!is_null($option_chosen)) {
        drupal_set_message(t('Chosing multiple options is not allowed'),'error');
        return;
      } else {
        $option_chosen = $form_state['input'][ 'options-others-options-' . $form_state['clicked_button']['#id'] ];
      }
    } 
 
  // now time to save selected option to save replies table
  if (! is_null($option_chosen) ) {
      $conn = Cassandra::initializeCassandraSystem();
      _saveUserOption(
        $form_state['clicked_button']['#chipingo_email'],
        $form_state['clicked_button']['#qtag'],
        $form_state['clicked_button']['#session_name'],
        $option_chosen,
        $GLOBALS['user']->uid,
        $conn  
      );
      Cassandra::disConnect($conn);
      drupal_set_message( t('Your reply saved') );  
  } else {
      drupal_set_message( t('No option is chosed') );
  }
}

/**
 * Returns user favorite chipingos as form elements
 * 
 * @return array
 */
function user_subscriptions_form() {
  $conn = Cassandra::initializeCassandraSystem();
  $qtags = _getUserFavorites($GLOBALS['user']->uid, $conn);  
  $form = __showChipingoForm($qtags);
  Cassandra::disConnect($conn);
  return $form;
}



/**
 * Returns form elements to show any chipingo regardless of whether it has published 
 * qtag or not.
 * If it has published qtag, qtags array of array should include detailed information
 * about session.
 * 
 * @param array $qtags
 * @param Cassandra\Connection $conn
 * @return array
 */
function __showChipingoForm($qtags, $conn=NULL) {
  
  $form = [];
  $i = 0;
  foreach($qtags as $qtag) {
    
    $form['start-' . $i] = array(
      '#markup' => '<div class="chipingo-block">'
    );
    $form = $form + ___getQTagForm($qtag, $conn);
    $form['stop-' . $i] = array(
      '#markup' => 	'		</div>'
    );
    $i = $i + 1;
  }
  if (count($qtags) == 0) {
    $form['message'] = array(
      '#markup' => t('There is no published ChipInGo'),
    );
  }
  return $form;
} 

/**
 * Returns form array
 * 
 * @param array $entity
 * @param Cassandra\Connection $conn
 * @return array
 */
function ___getQTagForm($entity, $conn) {
  
  $id = __convertToHTMLId($entity['chipingo_email']);
  
  $form['values-chipingo-block-start-' . $id] = array(
      '#markup' => 	
                    ' 	<div class="accordion chipingo-radius chipingo-div "> ' . 
                    '     <div class="chipingo-block-body">' 
                    //'       <font style="color:#F90">Chip</font> '.
                    //'       <font style="color:#093">In</font> '.
                    //'       <font style="color:#F00">Go</font> : ' 
  );
  
  $form = $form + ___getChipingoInfoFormElements($id, $entity, $conn);
  
  $form = $form + ___getButtonFormElements($id, $entity, $conn); 
  
  $form['values-chipingo-block-stop-' . $id] = array(
		'#markup' => 	'	</div>' .
                  '</div>',
	);  
  return $form;
}

/**
 * 
 * @param text $id
 * @param array $entity
 * @return array
 */
function ___getChipingoInfoFormElements($id, &$entity, $conn) {
  
  // LOGO
  $image_options = array(
    //'path' => '/chipingo/show_publisher_logo/' . $record['chipingo_email'],
    'path' => '/chipingo/show_chipingo_logo.php?chipingo_email=' . $entity['chipingo_email'], 
    'alt' => 'Publisher Logo',
    'title' => 'Publisher Logo',
    'width' => '35px',
    'height' => '35px',
    'attributes' => array(),
  );
  $image = theme('image', $image_options);
    
  if (isset($entity['qtag'])) {
    $entity = $entity + _getQTag($entity['chipingo_email'], $entity['qtag'], $conn);	
    $form['values-chipingo-info-' . $id] = array(
      '#markup' =>  '<div style="text-align: center">' .                    
                    __getChipingoSourcePath($entity,'large') . '<br><br>' .
                    '</div>' .
                    '<strong> ' . t('Publisher') . ' : ' . '</strong> ' .
                    $entity['publisher'] . ' ' . $entity['chipingo'] . ' ' . 
                    ' (' . $entity['chipingo_email'] . ')' . '<br><br>' .
                    '<i><strong><font color=red>' . 
                    $entity['question'] . 
                    '</font></strong></i>' 
    );
    if (isset($entity['session_name'])) {
      $form = $form + ___getQTagFormOptions($id,
                                        $entity,
                                        $conn);
    }
    
  } else {
    $form['values-chipingo-info-' . $id] = array(
      '#markup' =>  $image .
                    '<strong> ' . $entity['chipingo'] . '</strong> ' . t('published by') . ' ' . 
                    '<strong> ' . $entity['publisher'] . '</strong> (' . $entity['chipingo_email'] . ')' . '<br><br>' .
                    '<i><strong><font color=red>' . t('No published question at the moment') . '</font></strong></i>'
    );
  }
  
  return $form;
}  

/**
 * 
 * @param text $id
 * @param array $entity
 */
function ___getButtonFormElements($id, $entity, $conn) {
  
  $name = 'submit-reply-' . $id;
  $form[ $name ] = array(
    '#type' => 'submit',
    '#name' => $name,
    '#disabled' => (! isset($entity['qtag'])),
    '#default_value' => t('Reply'),
    '#submit' => array('save_user_option'),
    '#chipingo_email' => $entity['chipingo_email'],
    '#id' => $id,
    '#session_name' => (isset($entity['session_name'])) ? $entity['session_name'] : '',
    '#qtag' => (isset($entity['qtag'])) ? $entity['qtag'] : '',
    '#prefix' => '<div style="text-align: right; margin-top: 5px;">',
    '#attributes' => array(
      'class' => array( 'btn btn-success btn-sm' )
    )
  );
  
  $name = 'delete-reply-' . $id;
  $form[ $name ] = array(
    '#type' => 'submit',
    '#name' => $name,
    '#disabled' => (!isset($entity['user_reply']) or is_null($entity['user_reply']) ),
    '#default_value' => t('Delete My Reply'),
    '#submit' => array('delete_user_option'),
    '#chipingo_email' => $entity['chipingo_email'],
    '#id' => $id,
    '#session_name' => (isset($entity['session_name'])) ? $entity['session_name'] : '',
    '#qtag' => (isset($entity['qtag'])) ? $entity['qtag'] : '',
    //'#prefix' => '<div style="text-align: right; margin-top: 5px;">',
    '#attributes' => array(
      'class' => array( 'btn btn-primary btn-sm' )
    )
  );

  $name = 'submit-unsubscribe-' . $id;
  $userFavoritesTable = new UserFavoritesTable($conn);
  $record = (array) $userFavoritesTable->getRecord($GLOBALS['user']->uid, $entity['chipingo_email']);
  $form[$name] = array(
		'#type' => 'submit',
    '#name' => $name,
		'#default_value' => (count($record)==1) ? t('Unsubscribe') : t('Subscribe'),
		'#submit' => (count($record)==1) ? array('unsubscribe_user') : array('subscribe_user'),
    '#chipingo_email' => $entity['chipingo_email'],
    '#id' => $id,
    '#suffix' => '</div>',
		'#attributes' => array(
			'class' => array( 'btn btn-danger btn-sm' )
		)
	);
  
  return $form;
}

/**
 * 
 * @param array $form
 * @param array $form_state
 */
function unsubscribe_user($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  _unSubscribeUser($GLOBALS['user']->uid,$form_state['clicked_button']['#chipingo_email'], $conn);
  Cassandra::disConnect($conn);
}

/**
 * 
 * @param array $form
 * @param array $form_state
 */
function subscribe_user($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  _subscribeUser($GLOBALS['user']->uid,$form_state['clicked_button']['#chipingo_email'], $conn);
  Cassandra::disConnect($conn);
}

/**
 * 
 * @param text $chipingo_email
 * @param text $qtag
 * @param text $session_name
 * @param Cassandra\Connection $conn
 */
function ___getQTagFormOptions($id, &$entity, $conn) {
  $options = [];
  $form = [];
  $publisher_options = [];
  $others_options = [];
  
  // reading options
  $active = (array) _getOptions($entity, $conn);
  foreach($active as $key => $value) {
    if ($value['option_owner_category'] == 'P') {
      $publisher_options[$value['option_timestamp']] = $value['option_long'];
    } else {
      $others_options[$value['option_timestamp']] = $value['option_long']; 
    }
  }
  
  // get publisher options
  $basename = 'options-publisher-';
  $radioboxnamePublisher = $basename . 'options-' . $id;
  if (count($publisher_options)>0) {    
    $form[$basename . 'label-' . $id] = [
      '#markup' =>  '<br><br>' .
                    '<font color="blue">' . 
                      t('Options provided by publisher') . 
                    //'<strong>' . $entity['publisher'] . '</strong>' . 
                    '</font><br><br>'
    ];     
    $form[$radioboxnamePublisher] = [
      '#type' => 'radios',
      //'#title' => t('Annotations will be deleted'),
      '#description' => t(''),
      '#options' => $publisher_options,
      '#validated' => 'TRUE',
    ];
  } else {
    $form[$basename . 'label-' . $id] = [
      '#markup' => '<br><br>' . 
                  t('There are no options provided by publisher') . 
                  ' <strong>' . 
                  $entity['publisher'] . 
                  '</strong><br><br>'
    ]; 
  } 
 
  
  // ***********************
  // get others' options
  $basename = 'options-others-';
  $radioboxnameOthers = $basename . 'options-' . $id;
  if (count($others_options)>0) {    
    $form[$basename . 'label-' . $id] = [
      '#markup' =>  '<font color="blue">' . 
                    t('Provided by other voters') . 
                    '</font><br><br>'
    ];     
    $form[$radioboxnameOthers] = [
      '#type' => 'radios',
      //'#title' => t('Annotations will be deleted'),
      '#description' => t(''),
      '#options' => $others_options,
      '#validated' => 'TRUE',
    ];
  } else {
    $form[$basename . 'label-' . $id] = [
      '#markup' => t('There are no options provided by other users') . '<br>'
    ];
  } 
  
  // read marked option from db and set the corresponding option
  $userReply = NULL;
  if (isset($form[$radioboxnamePublisher]) or isset($form[$radioboxnameOthers])) {
    $QTagRepliesTable = new QTagRepliesTable($conn);
    $userReply = $QTagRepliesTable->getUserReply($GLOBALS['user']->uid, $entity); 
    if (isset($form[$radioboxnamePublisher])) {
      if (!is_null($userReply)) {
        $form[$radioboxnamePublisher] = $form[$radioboxnamePublisher] + ['#default_value' => $userReply];
      }
    }
    if (isset($form[$radioboxnameOthers])) {
      if (!is_null($userReply)) {
        $form[$radioboxnameOthers] = $form[$radioboxnameOthers] + ['#default_value' => $userReply];
      }
    }
  }
  
  // if this question is not voted yet and if current user is not the owher of this question,
  // we are 
  if (is_null($userReply) and $entity['user_id'] != $GLOBALS['user']->uid) {
    $form[$basename . 'own-' . $id] = [
      '#type' => 'textfield',
      '#name' => $basename . 'own-' . $id,
      '#attributes' => array( 'placeholder' => 'Your own option' ),
      '#title' => 'Enter your own option',
      '#prefix' => '<br>'
    ]; 
  }
  $entity['user_reply'] = $userReply;
  return $form;
}