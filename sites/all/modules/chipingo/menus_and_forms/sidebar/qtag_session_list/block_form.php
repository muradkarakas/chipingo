<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/sidebar/qtag_session_list/api' );

/**
 *  block view function
 */
function quick_session_list_form($form, &$form_state) {
  
  if (_getViewMode() == 'S') {
    return;
  }
  
  $current_path = current_path();
  if ( strpos($current_path, 'yourqtags/edit/') === FALSE  ) {
      return NULL;
  }
  $path_array = explode('/',$current_path);
  $chipingo_email = $path_array[2];
  $qtag           = $path_array[3];
  if (isset($path_array[4])) {
    $session_name   = $path_array[4];
  } else {
    $session_name = '';
  }
 
  $form['div-start'] = array(
    '#markup' => '<div id="<?php print $block_html_id; ?>" class="chipingo-block">',
  );
    
  $form['div-header'] = array(
    '#markup' => '<div class="chipingo-block-header">Session List</div>',
  );
  
  $form['div-body-start'] = array(
    '#markup' => '<div class="chipingo-block-body" style="text-align: center;">',
  );
  $conn = Cassandra::initializeCassandraSystem();
  $resultSet = _getSessions($_SESSION['chipingo_email'], $_SESSION['qtag'], $conn);
  Cassandra::disConnect($conn);
  
	foreach($resultSet as $record) {
    $sessionid = __convertToHTMLId($record['session_name']);
    $form['div-session-buttons-start-' . __convertToHTMLId($record['session_name']) ] = array(
      '#type' => 'submit',
      '#name' =>  $sessionid,
      '#session_name' =>  $record['session_name'],
      '#default_value' =>  $record['session_name'],
      '#attributes' => array(
          'class' => (  $record['session_name']==$_SESSION['session_name'] 
                        ? array( 'btn btn-info btn-sm') : 
                          array( 'btn btn-warning btn-sm')
                     ),
          'style' => 'min-width: 150px; margin-bottom: 4px; '
      ),
      '#prefix' =>  '<div class="btn-group" style="display: inline-block;">',
     /* '#suffix' =>  ' <button type="button" ' .
                    '    class="btn btn-danger dropdown-toggle btn-sm" ' .
                    '    data-toggle="dropdown" aria-expanded="false"> ' .
                    '    <span class="caret"></span> ' .
                    '   <span class="sr-only">Toggle Dropdown</span> ' .
                    ' </button>' .
                    ' <ul class="dropdown-menu" role="menu">' */
    );
    /*
    $form['div-session-button-delete-' . __convertToHTMLId($record['session_name']) ] = array(
      '#type' => 'submit',
      '#name' =>  'delete-' . $sessionid,
      '#submit' => ['quick_session_list_form_submit_delete'],
      '#default_value' => '<i class="fa fa-cog fa-spin fa-1x fa-lg"></i> ' . 
                          t('D e l e t e'),
      '#session_name' =>  $record['session_name'],
      '#attributes' => array(
          'class' => array( 'btn btn-danger btn-sm'),
          'style' => 'min-width: 150px; margin-bottom: 4px; '
      ),
      '#prefix' => '  <li class="divider"></li>'.
                   '     <li style="text-align: center;">' ,
      '#suffix' => '  </li> '
    );    
    $form['div-session-button-publish-' . __convertToHTMLId($record['session_name']) ] = array(
      '#type' => 'submit',
      '#name' =>  'publish-' . $sessionid,
      '#submit' => ['quick_session_list_form_submit_publish'],
      '#default_value' => '<i class="fa fa-cog fa-spin fa-1x fa-lg"></i> ' . 
                          t('P u b l i s h'),
      '#session_name' =>  $record['session_name'],
      '#attributes' => array(
          'class' => (  $record['session_name']==$_SESSION['session_name'] 
                        ? array( 'btn btn-info btn-sm') : 
                          array( 'btn btn-warning btn-sm')
                     ),
          'style' => 'min-width: 150px; margin-bottom: 4px; '
      ),
      '#prefix' => '   <li class="divider"></li>'.
                   '     <li style="text-align: center;">' ,
      '#suffix' => '     </li> '.
                   '   </li>'
    );*/
    
    $form['div-session-buttons-end-' . __convertToHTMLId($record['session_name']) ] = array(
      '#markup' => ' </ul>' .
                   '</div>',
    );
  }
  
  $form['div-body-end'] = array(
    '#markup' => '</div>',
   );
  
  $form['add-new-session'] = array(
      '#type' => 'submit',
      '#default_value' => 'Add a new sesson',
      '#prefix' => '<div style="text-align: right; margin-top: 5px;">',
      '#suffix' => '</div>',
      '#submit' => array('create_session_form_submit'),
      '#attributes' => array(
        'style' => array( 'margin: 1px; position: relative; top: -2px;' ),
        'class' => array( 'btn btn-primary btn-sm' ),
      ),
  );
  
  $form['div-end'] = array(
    '#markup' => '</div>',
  );
  
  return $form;
}

/**
 * Redirect user to create a new session form
 * 
 * @param type $form
 * @param type $form_state
 */
function create_session_form_submit($form, &$form_state) {
  $conn = Cassandra::initializeCassandraSystem();
  $path = __getSessionCreatePath($_SESSION['chipingo_email'], $_SESSION['qtag']);
  Cassandra::disConnect($conn);
  drupal_goto( $path );
}

/**
 * 
 * @param type $form
 * @param type $form_state
 * @return type
 */
function quick_session_list_form_submit_delete($form, &$form_state) {
  
  if ( ! isset($form_state['clicked_button']['#session_name']) ) {
    return NULL;
  }
  $path = __getSessionDeletePath(
      $_SESSION['chipingo_email'], 
      $_SESSION['qtag'], 
      $form_state['clicked_button']['#session_name']
  );
  drupal_goto( $path );
}

/**
 *  block view function
 */
function quick_session_list_form_submit($form, &$form_state) {
  
  if ( ! isset($form_state['clicked_button']['#session_name']) ) {
    return NULL;
  }
  $path = __getQTagEditPath(
      $_SESSION['chipingo_email'], 
      $_SESSION['qtag'], 
      $form_state['clicked_button']['#session_name']
  );
  drupal_goto( $path );
}



