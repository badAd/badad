<?php

// This is intended to be run by cron
require ('/srv/www/badad/webdir/includes/config.inc.php');
require (MYSQL);

$q = "INSERT INTO test_log (string) VALUES ('entry')";
$r = mysqli_query ($dbc, $q);

?>
