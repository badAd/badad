<?php

// This may be redundant and not necessary
// $List_ad_ID must be set

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);

// Update the status
$q = "UPDATE listads SET pub_status='dead' WHERE ad_id='$List_ad_ID'";
$r = mysqli_query ($srv_dbc, $q);

if (!$r) { // If it didn't run okay
	sql_error($q, 'srv_dbc', "sqle_175");
}

// Update the status
$q = "UPDATE podcastads SET pub_status='dead' WHERE ad_id='$List_ad_ID'";
$r = mysqli_query ($agg_dbc, $q);

if (!$r) { // If it didn't run okay
	sql_error($q, 'agg_dbc', "sqle_176");
}
