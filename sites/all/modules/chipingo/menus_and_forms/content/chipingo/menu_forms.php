<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/api_common' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/chipingo/api' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/qtag/api' );

/**
 * $file
 * 
 *  Form file. 
 *  Create drupal callback function here.
 *  For business logic functions use api.php
 *  Do not access Cassandra from this file. Use functions defined in api.php
 *  file instead
 * 
 * @see menu_forms.php
 * @see api.php
 * @see menu_items.php
 */
// *****************************************************************************************

/**
 * Show publisher list in main content
 * 
 * @param type $form
 * @param type $form_state
 * @return array
 */
function publisher_chipingo_settings_block_form_wrapper( $form, &$form_state ) {
  
  $form['form-container-element-id-start'] = [
    '#markup' => '<div id="form-container-element-id">'
  ];
    
  $form['values-chipingo-block-start'] = array(
		'#markup' => 	'<div class="chipingo-block">' 
	);
	
	$form['values-chipingo-block-header'] = array(
		'#markup' => 	'	<div class="chipingo-block-header"> '.
              t('Publisher') . ' & ' .
						'		<font style="color:#F90">Chip</font> '.
						'		<font style="color:#093">In</font> '.
						'		<font style="color:#F00">Go</font> '.
              t('List') .
						'	</div>'
	);

	$form['values-chipingo-block-legend'] = array(
		'#markup' =>	'	<div class="chipingo-block-body"> ' .
                  '		<div class="panel-body"> '.
                  '			<p> '.
                          t('Publisher and ChipInGos are listed here. You cannot delete those in use.' ) .
                  '			</p> '.
                  '			<i style="" class="fa fa-thumbs-down"></i> : ' . t('Not verified') . '<br> '.
                  '			<i style="" class="fa fa-cog fa-spin"></i> : ' . t('Has published QTag') . '<br> '.
                  '			<i style="" class="fa fa-cog"></i> : ' . t('Verified, has not published QTag') . '<br> '.
                  '		</div> ' .						
                  '	</div> ' 
	);
	
  $form = $form + _getChipingoRows($form, $form_state);
	
	$form['values-chipingo-block-new-record-start'] = array(
		'#markup' =>	'	<div class="chipingo-block-body">'
	);
	
	$form['values-chipingo-block-new-record-chipingo'] = array(
		'#type' => 'textfield',
		'#maxlength' => 50,
		'#attributes' => array(
			'placeholder' => 'Chipingo',
			'class' => array( 'form-control' ),
			'style' => 'margin-top: 12px; max-width: 150px; text-align: left; display: inline-block;'
		),
		'#prefix' => '<table><tr>'. 
						'<td style="text-align: right;margin: unset !important; padding: unset !important;  padding-right: 0px; margin-right: 0px">',
		'#suffix' => '</td>'
	);
  
  $form['values-chipingo-block-new-record-publisher'] = array(
		'#type' => 'textfield',
		'#maxlength' => 50,
		'#attributes' => array(
			'placeholder' => t('Publisher'),
			'class' => array( 'form-control' ),
			'style' => 'margin-top: 12px; max-width: 150px; text-align: left; display: inline-block;'
		),
		'#prefix' => '<td style="text-align: right;margin: unset !important; padding: unset !important;  padding-right: 0px; margin-right: 0px">',
		'#suffix' => '</td>',
	);
	
	$form['values-chipingo-block-new-record-chipingo-email'] = array(
		'#type' => 'textfield',
		'#maxlength' => 50,
		'#attributes' => array(
			'placeholder' => t('e-mail'),
			'class' => array( 'form-control' ),
			'style' => 'margin-top: 12px; ',
			'aria-describedby' => 'sizing-addon3'
		),
		'#prefix' =>  	'<td style="width: 5%; margin: unset !important; padding: unset !important;style="text-align: left; margin-right: 0px; padding-right: 0px; padding-left: 0px; margin-left: 0px">	'.				
						/*'	<div class="input-group input-group-xs" style="" id="sizing-addon3"> '.
						'		<span class="input-group-addon">@</span>' .
						'	</div>' .*/
						'</td>' . 
						'	<td style="margin: unset !important; padding: unset !important; padding-right: 0px; padding-left: 0px; margin-left: 0px">',
    '#suffix' => 	'	'.
						'	</td>'
	);
	
	$form['values-chipingo-block-new-record-add-button'] = array(
		'#type' => 'submit',
		'#value' => t( 'Add' ),
		'#attributes' => array(
			'class' => array( 'btn btn-primary btn-sm' ),
      'style' => 'min-width: 75px',
		),
		'#prefix' =>  	'<td style="margin: unset !important; padding: unset !important;">',
		'#suffix' => 	'</td> </tr>	</table>',
		'#ajax' => [
        'callback' => 'add_chipingo_form_callback',
        'wrapper' => 'replace_textfield_div', //ajaxblok',
        //'method' => 'replace',
    ]
	);					
	
	$form['values-chipingo-block-new-record-end'] = array(
		'#markup' =>	'	</div>' 
	);
	
	$form['values-chipingo-block-end'] = array(
		'#markup' => 	'</div>'
	);  
  
  $form['form-container-element-id-end'] = [
    '#markup' => '</div>',
  ];
  
  $form['#attached'] = array(
    'js' => array(
        drupal_get_path('module', 'chipingo') . '/js/chipingo.js' => array(),
      ),
  );
  
	return $form;
}


function _getChipingoRow(&$form, $id, $record, $conn) {
    
    $publishedQTag = __getPublishedQTagCount($id, $conn);
		
		$form['rp'][ 'publisher_chipingo_row_start' . $id ] = array(
			'#type' => 'markup',
			'#prefix' => '<tr>',
		);
		
		// CHIPINGO
		$form['rp'][ 'publisher_chipingo_publisher_chipingo' . $id ] = array(
			'#type' => 'markup',
			'#markup' => $record['chipingo']  ,
			'#prefix' => 	'<td style="text-align: right; vertical-align: middle; padding-top: 0px;">' . 
                    '	<h4 style="margin: 0px;">' .
                    '		<span class="label label-default ">',
			'#suffix' => 	'		</span>'.
                    '	</h4>'.
                    '</td>',
		);
      
    // PUBLISHER
		$form['rp'][ 'publisher_chipingo_publisher_publisher' . $id ] = array(
			'#type' => 'markup',
			'#markup' => $record['publisher']  ,
			'#prefix' => 	'<td style="min-width: 60px; text-align: right">' . 
                      '<a href="/chipingo/upload_chipingo_logo_image_file/' . $record['chipingo_email'] . '">' . 
                        __getChipingoSourcePath($record) .
                      '</a>&nbsp;'.
                      '<a href="/chipingo/upload_publisher_logo_image_file/' . $record['chipingo_email'] . '">' . 
                        __getPublisherSourcePath($record['chipingo_email']) .
                      '</a>'.
                    '</td>'.                    
                    '<td style="text-align: left; vertical-align: middle; padding-top: 0px;">' . 
                    '	<h4 style="margin: 0px;">' .
                    '		<span class="label label-default ">',
			'#suffix' => 	'		</span>'.
                    '	</h4>'. 
                    '</td>',                    
		);
    
    // EMAIL
		$form['rp'][ 'chipingo-email' . $id ] = array(
			'#type' => 'markup',
			'#markup' => $record['chipingo_email']  ,
			'#prefix' => 	'<td style="text-align: center; vertical-align: middle;">',
			'#suffix' => 	'</td>',
		);
    
		// STATUS ICON
		$form['rp'][ 'publisher_chipingo_status' . $id ] = array(
			'#type' => 'markup',
			'#markup' => __getChipingoIconHtml($record['chipingo_status'], count($publishedQTag), 2),
			'#prefix' => 	'<td style="text-align: center; vertical-align: middle;">',
			'#suffix' => 	'</td>',
		);
		
		// QTAG COUNT
		/*$form['rp'][ 'publisher_chipingo_qtag_count' . $id ] = array(
			'#type' => 'markup',
			'#markup' => '<span class="badge">' . $total_qtag_count . '</span>',
			'#prefix' => 	'<td style="text-align: center; vertical-align: middle;"><strong>',
			'#suffix' => 	'</strong>	</td>',
		);*/
		
    $name_and_id = 'publisher_chipingo_delete_' . $id;
    
		// DELETE BUTTON
		$form['rp'][ 'publisher_chipingo_delete' . $id ] = array(
			'#type' => 'submit',
			'#chipingo_id' => $record['chipingo_email'],
      '#name' => $name_and_id,
			'#value' => t('Delete'),
			'#attributes' => array( 
				'class' => array( 'btn btn-primary btn-sm' ),  // ajax-processed
        'style' => 'min-width: 75px',
        //'#id' => $name_and_id,
			),
			'#prefix' =>  ' </td><td>' .
                    ' <div class="btn-group" style="display: inline-block;">',
      '#suffix' =>  '   <button type="button" ' .
                    '      class="btn btn-primary dropdown-toggle btn-sm" ' .
                    '      data-toggle="dropdown" aria-expanded="false"> ' .
                    '      <span class="caret"></span> ' .
                    '      <span class="sr-only">Toggle Dropdown</span> ' .
                    '  </button>' .
                    '  <ul class="dropdown-menu" role="menu">'.
                    '   <li style="text-align: center;">'.
                    '     <a href="#">Validate @todo</a>'.
                    '   </li>' .
                    '  </ul>' .
                    ' </div>' .
                    '</td>',
      '#ajax' => [
        'callback' => 'delete_chipingo_form_callback',
        'wrapper' => 'replace_textfield_div', 
        //'method' => 'replace',
      ],
      
		);
		
		$form['rp'][ 'publisher_chipingo_row_end' . $id ] = array(
			'#type' => 'markup',
			'#suffix' => '</tr>',
		);
    
    return $form;
    
}

/**
 *  Generates the dynamic part of the chipingo block for ajax response.
 * 
 * @param type $form
 */
function _getChipingoRows($form, &$form_state) {
    
  $form['rp'] = [
    '#type' => 'item',
    '#prefix' => '<div id="replace_textfield_div">',
    '#suffix' => '</div>',
  ];
    
	$form['rp']['values-chipingo-block-table'] = array(	
		'#markup' =>	'	<div class="chipingo-block-body">' .
                  '		<table class="table" > ' .
                  '			<thead> ' .
                  '				<td class="table-header-font" style="text-align: right">' .
                  '					<strong>' . t('ChipInGo') . '</strong>' .
                  '				</td>' .
                  '				<td class="table-header-font" style="text-align: center">' .
                  '					<strong>' . t('Logo') . '</strong>' .
                  '				</td>' .   
                  '				<td class="table-header-font" style="text-align: right">' .
                  '					<strong>' . t('Publisher') . '</strong>' .
                  '				</td>' .
                  '				<td class="table-header-font" style="text-align: center">' .
                  '					<strong>' . t('E-Mail') . '</strong>' .
                  '				</td>' .                   
                  '				<td class="table-header-font">' .
                  '					<strong>' . t('Status') . '</strong>' .
                  '				</td>' .
                  /*'				<td class="table-header-font">' .
                  '					<strong>' . t('QTAG COUNT') . '</strong>' .
                  '				</td>' .*/
                  '				<td class="table-header-font" style="">' .
                  '					<strong>' . t('Operations') . '</strong>' .
                  '				</td>' .
                  '			</thead> '.
                  '			<tbody>' 
	);
	$conn = Cassandra::initializeCassandraSystem();
  $result = _getUsersChipingos(NULL, $conn);
  
  foreach( $result as $record ) {
  		
    $id = $record['chipingo_email']; 
		//$total_qtag_count = _getChipingoQtagCount($GLOBALS['user']->uid, $id);
		_getChipingoRow($form, $id, $record, $conn);
	}
  
	$form['rp']['values-chipingo-block-table-end'] = array(	
		'#markup' =>	'			</tbody> '.
                  '			</table> ' .
                  '	</div> ' 
	);  
  
  Cassandra::disConnect($conn);
  
  return $form;
}


/**
 * Call drupal entity delete function in order to delete related records
 * from cassandra database
 *  
 * @param array $form
 * @param array $form_state
 */
function delete_chipingo_form_callback( $form, &$form_state ) {
  $conn = Cassandra::initializeCassandraSystem();
  $chipingo_email = $form_state['clicked_button']['#chipingo_id'];
  $entity = _getChipingoByChipingo($chipingo_email, $conn);  
  if ( $entity['default_chipingo'] == 1 ) {
    Cassandra::disConnect($conn);
    $ajaxErrorMessageCommand = getFullAjaxAlertCommandArray(t('Default ChipInGo cannot be deleted'));
    return $ajaxErrorMessageCommand;
  }
  if (isset($chipingo_email)) {
    _deleteChipingo([ 'chipingo_email' => $chipingo_email ], $conn); 
    Cassandra::disConnect($conn);
    $ajaxCommands = array(
      '#type' => 'ajax', 
      '#commands' => []
    );
    
    // refreshing forms
    $chipingoFormCommand = reBuiltChipingoFormForAjax($form_state, 'publisher_chipingo_settings_block_form_wrapper', '#form-container-element-id');
    $ajaxCommands['#commands'][] = $chipingoFormCommand;    
    
    $chipingoQuickListFormCommand = builtChipingoFormForAjax('quick_chipingo_list_form', '#quick-chipingo-list-ajax-div');
    $ajaxCommands['#commands'][] = $chipingoQuickListFormCommand;
    
    $ajaxCommands['#commands'][] = [
      'command' => 'showMessage',
      'message' => t('Deleted') . ': ' . $chipingo_email
    ];
    
    return $ajaxCommands;     
  }
}

/**
 * Creates a new "chipingo" record and its related records
 * 
 * @param array $form
 * @param array $form_state
 */
function add_chipingo_form_callback($form, &$form_state) {
 
  $chipingo = $form_state['values']['values-chipingo-block-new-record-chipingo'];
  $publisher = $form_state['values']['values-chipingo-block-new-record-publisher'];
  $chipingo_email = $form_state['values']['values-chipingo-block-new-record-chipingo-email'];
  
  try {
    $conn = Cassandra::initializeCassandraSystem();
    _addNewChipingo($chipingo, $publisher, $chipingo_email, $GLOBALS['user']->uid, 1, 0, $conn);
    Cassandra::disConnect($conn);  
    
    $ajaxCommands = array(
      '#type' => 'ajax', 
      '#commands' => []
    );
    
    // refreshing forms
    $chipingoFormCommand = reBuiltChipingoFormForAjax($form_state, 'publisher_chipingo_settings_block_form_wrapper', '#form-container-element-id');
    $ajaxCommands['#commands'][] = $chipingoFormCommand;
    
    $chipingoQuickListFormCommand = builtChipingoFormForAjax('quick_chipingo_list_form', '#quick-chipingo-list-ajax-div');
    $ajaxCommands['#commands'][] = $chipingoQuickListFormCommand;
    
    $ajaxCommands['#commands'][] = [
      'command' => 'showMessage',
      'message' => t('Added') . ': ' . $chipingo . ' ' . $publisher
    ];
    Cassandra::disConnect($conn);
    
    return $ajaxCommands;     
    
  } catch (Exception $ex) {
      $page = getFullAjaxAlertCommandArray($ex->getMessage());
      if (isset($conn) and !is_null($conn)) {
        Cassandra::disConnect($conn);
      }
      return $page; 
  }    
}