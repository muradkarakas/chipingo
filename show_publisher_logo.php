<?php

// DRUPAL bootstrap
define('DRUPAL_ROOT', getcwd());
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

if ( !isset($_GET['chipingo_email']) ) {
    exit("parameter required");
}

set_include_path('.;C:\Calismalarim\wamp\www\chipingo\sites\all\modules\chipingo\cassandra\\');

require_once 'php-cassandra_includes.php';
require_once 'cassandra_interface.php';

  $conn = Cassandra::initializeCassandraSystem();  
  $ChipingoTable = new ChipingoTable($conn);
 
  $result = $ChipingoTable->readAllLogos($_GET['chipingo_email']);
  $content = hex2bin($result['publisher_logo_content']);
  Cassandra::disConnect($conn);

header("Content-type: image/png");
if (strlen($content) == 0) {
  $imgFilePath = 'C:\\Calismalarim\\wamp\\www\\chipingo\\sites\\all\\themes\\chipingo\\images\\home.png';
  $handle = fopen( $imgFilePath, "r" )  or die("Unable to open file!");
  $content = fread($handle,filesize($imgFilePath));
  fclose($handle);
}

print $content;