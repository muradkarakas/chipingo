<?php

$s = microtime();

set_include_path('.;C:\Calismalarim\wamp\www\chipingo\sites\all\modules\chipingo\cassandra\\');

require_once 'php-cassandra_includes.php';
require_once 'cassandra_interface.php';

$conn = Cassandra::initializeCassandraSystem();
$searchPathsTable = new SearchPathsTable();
$result = $searchPathsTable->getWhole($_GET["term"]);
$result[] = [ 'id' => 'timeframe3', 
              'label' => (microtime() - $s)
            ];
print json_encode($result);
Cassandra::disConnect($conn);