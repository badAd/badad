<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the site ID
if ((isset($_POST['s'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_65', '_POST s', $_POST['s'], $IP);}
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['s']))
&& (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $site_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['s']);
} else {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Make sure the user is an Activated Partner
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // Not a partner
  header("Location: partner.php");
  exit(); // Quit the script
}
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$confirmed = "$row[0]";
// Redirect if not activated
if ($confirmed != 'Confirmed') {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Turn off the subdomains
$q = "UPDATE partnersites SET useable='off' WHERE id='$site_id'";
if ($r = mysqli_query ($srv_dbc, $q)) {

  // Return to Order History
  header("Location: partner.php");
  exit(); // Quit the script

} else {
  sql_error($q, 'srv_dbc', "sqle_29");
}
