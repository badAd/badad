<?php

// This is intended to be run by cron
require_once ('/srv/www/badad/webdir/includes/config.inc.php');
require_once (MYSQL);
// Listing the ads needs the _SRV config
require_once ('/srv/www/badad/webdir/includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Delete old keys
$q = "DELETE FROM loginonce WHERE date_dead <= (NOW() - INTERVAL 90 DAY)";
$r = mysqli_query ($dbc, $q);
$q = "DELETE FROM logincode WHERE date_dead <= (NOW() - INTERVAL 90 DAY)";
$r = mysqli_query ($dbc, $q);
$q = "DELETE FROM rememberme WHERE date_expires <= (NOW() - INTERVAL 90 DAY)";
$r = mysqli_query ($dbc, $q);
$q = "DELETE FROM partnersites WHERE type='app' AND date_created <= (NOW() - INTERVAL 5 DAY) AND NOT papp_key='connected'";
$r = mysqli_query ($srv_dbc, $q);
$q = "UPDATE devkeys SET old_pub_key=NULL, old_sec_key=NULL WHERE date_newkeys <= (NOW() - INTERVAL 5 DAY) AND NOT (old_pub_key='NULL' OR old_sec_key='NULL')";
$r = mysqli_query ($srv_dbc, $q);
?>
