<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/configuration/api' );

/**
 * Respond to "chipingo_cassandra_reinstall" submit button in 
 * "chipingo_configuration_form" form.
 * Reinstall drupal cassandra database objects and return configuration page
 *
 * @param $form
 *   
 * @param $form_state
 *
 * @return
 *   An array containing the title and any custom form elements to be displayed
 *   in the node editing form.
 */
function cassandra_drupal_reinstall_form($form, &$form_state) {
  _cassandra_drupal_reinstall();
  return NULL;
}

/**
 * Display cassandra node editing form.
 *
 * @param $form
 *   The node being added or edited.
 * @param $form_state
 *   The form state array.
 *
 * @return
 *   An array containing the title and any custom form elements to be displayed
 *   in the node editing form.
 */
function cassandra_node_configuration_form($form, &$form_state) {
  //$form = array();

  $form['fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Cassandra database nodes'),
  );
  
  for($i=0; $i < 5; $i++ ) {
    $form['fieldset']['cassandra_node_' . $i] = array(
      '#type' => 'textfield',
      '#title' => t('Node') . ': ' . $i,
    );
  }
  
  $form['action'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  
  return $form;
}

/**
 * Respond to "chipingo_cassandra_reinstall" submit button in 
 * "chipingo_configuration_form" form.
 * Reinstall drupal cassandra database objects and return configuration page
 *
 * @param $form
 *   
 * @param $form_state
 *
 * @return
 *   An array containing the title and any custom form elements to be displayed
 *   in the node editing form.
 */
function cassandra_chipingo_reinstall_form($form, &$form_state) {
  cassandra_chipingo_reinstall();
  return NULL;
}

/**
 * Creates ChipInGo db tables. Called by chipingo_install hook.
 * 
 * @param \Cassandra\Connection $conn
 */
function cassandra_chipingo_reinstall() {
  $shemaName = 'chipingo';
  
  // Get cassandra connection
  $conn = Cassandra::initializeCassandraSystem();
  
  // create cassandra chipingo keyspace
  Cassandra::createSchema($conn, $shemaName);
  
  //set cassandra default keyspace
  Cassandra::setDefaultShema($conn, $shemaName);
  
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
	drupal_set_message('ChipInGo cassandra database objects are being created...');
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
  
  _create_chipingo_database_objects($conn);
  
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
  drupal_set_message('ChipInGo cassandra database objects have been created successfully' );
  drupal_set_message('---------------------------------------------------------------------------------------------------------------------------');
 
  Cassandra::disconnect($conn); 
}