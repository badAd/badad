<?php

// This action page should only be accessed from the "kill" action in Order History because it will redirect there once finished

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_80', '_POST a', $_POST['a'], $IP);}
if ((isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Get the ad info
$q = "SELECT user_id FROM ads WHERE id='$adID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$uID = "$row[0]";

// Redirect if it is another user's ad
if ($uID != $userid) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Kill the ad
// ads table
$qa = "UPDATE ads SET pub_status='dead' WHERE user_id='$userid' AND id='$adID'";
$ql = "UPDATE listads SET pub_status='dead' WHERE ad_id='$adID'";
if ($ra = mysqli_query ($dbc, $qa)) {
  if ($rl = mysqli_query ($srv_dbc, $ql)) {
    // Return to Order History
    header("Location: order_history.php");
    exit(); // Quit the script
  } else {
    sql_error($ql, 'srv_dbc', "sqle_2");
  }
} else  {
  sql_error($qa, 'dbc', "sqle_1");
}
