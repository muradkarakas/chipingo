<?php

function get_options_form($form, $node, &$form_state) {
	
	$index = 0;
	
	$form['options_information'] = array(
		'#type' => 'fieldset',
		'#title' => t('QTag Options'),
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,
		'#group' => 'additional_settings',
		'#weight' => -400
	 );
	 
	if ( isset($node->nid) ) {
			
		$result = db_select( 'qtag_options', 'a')
							->fields('a',array('nid','hash','opt'))
							->condition('nid', $node->nid, '=')
							->execute();
				
		if ( isset($result) ) {
			
			foreach( $result as $row ) {    
	
				$form['options_information'][ 'option' . $index ] = array(
					'#type' => 'textfield',
					'#title' => 'Option '. ($index+1) . ':',
					'#default_value' => $row->opt,
					'#required' => FALSE,
					'#title_display' => 'before',
					'#prefix' => '<table style="width: 100%; border: 2px none;"><tr><td style="width: 50%; vertical-align:bottom">',
					'#suffix' => '</td>',
				);

				$form['options_information']['delete' . $index] = array(
					'#type' => 'submit',
					'#value' => t('Delete'),
					'#hash' => $row->hash,
					'#nid' => $row->nid,
					'#option' => $row->opt,
					'#submit' => array('qtaq_delete_option'),
					'#name' => 'google',
					'#prefix' => '<td style="vertical-align:bottom">',
					//#suffix' => '</td></tr></table>'
				  );
  
				// Invisible
				$form['options_information'][ 'hash' . $index ] = array(
					'#type' => 'textfield',
					'#title' => 'Hash '. ($index+1) . ':',
					'#default_value' => $row->hash,
					'#required' => FALSE,
					'#title_display' => 'before',
					'#prefix' => '<!--',
					'#suffix' => '--></td></tr></table>',
				);
				
				$index++;
			};	
		}
	}
	
	$form['options_information']['option' . $index] = array(
		'#type' => 'textfield',
		'#title' => 'New Option :',
		'#default_value' => '',
		'#required' => FALSE,
		'#prefix' => '<table style="width: 100%; border: 2px none;"><tr><td style="width: 50%;vertical-align:bottom">',
		'#suffix' => '</td><td style="vertical-align:bottom"></td></tr></table>'
	);
	
	return $form;	
}

function qtaq_delete_option($form, $form_state) {
  
	if ( ! isset($form_state['clicked_button']['#hash']) && 
	     ! isset($form_state['clicked_button']['#nid'])
	   )
		 return;
	
	$num_deleted = db_delete('qtag_options')
	  ->condition('nid', $form_state['clicked_button']['#nid'] )
	  ->condition('hash', $form_state['clicked_button']['#hash'] )
	  ->execute();
	
	drupal_set_message( $num_deleted . ' row(s) deleted' );
}

/**
*  Add options of the question $nid to the $form parameter as a form api element
*
*/
function get_options( $nid, $form ) {
	
	// delete this function
	return;
	
	$index = 0;
	
	if ( isset($nid) && $nid != -1) {
			
		$result = db_select( 'qtag_options', 'a')
							->fields('a',array('nid','hash','opt'))
							->condition('nid', $nid, '=')
							->execute();
				
		if ( isset($result) ) {
			
			foreach( $result as $row ) {    
	
				$form['options_information'][ 'option' . $index ] = array(
					'#type' => 'textfield',
					'#title' => 'Option '. ($index+1) . ':',
					'#default_value' => $row->opt,
					'#required' => FALSE,
					'#title_display' => 'before',
					'#prefix' => '<table style="width: 100%; border: 2px none;"><tr><td style="width: 50%; vertical-align:bottom">',
					'#suffix' => '</td>',
				);

				$form['options_information']['delete' . $index] = array(
					'#type' => 'submit',
					'#value' => t('Delete'),
					'#hash' => $row->hash,
					'#nid' => $row->nid,
					'#option' => $row->opt,
					'#submit' => array('qtaq_delete_option'),
					'#name' => 'google',
					'#prefix' => '<td style="vertical-align:bottom">',
					//#suffix' => '</td></tr></table>'
				  );
  
				// Invisible
				$form['options_information'][ 'hash' . $index ] = array(
					'#type' => 'textfield',
					'#title' => 'Hash '. ($index+1) . ':',
					'#default_value' => $row->hash,
					'#required' => FALSE,
					'#title_display' => 'before',
					'#prefix' => '<!--',
					'#suffix' => '--></td></tr></table>',
				);
				
				$index++;
			};	
		}
	}
	
	$form['options_information']['option' . $index] = array(
		'#type' => 'textfield',
		'#title' => 'New Option :',
		'#default_value' => '',
		'#required' => FALSE,
		'#prefix' => '<table style="width: 100%; border: 2px none;"><tr><td style="width: 50%;vertical-align:bottom">',
		'#suffix' => '</td><td style="vertical-align:bottom"></td></tr></table>'
	);
	
	return $form;		
}


