<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// _GET the subscription ID
if ((isset($_POST['l'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['l']))) {$IP = get_ip_addr(); script_kiddy('sk_85', '_POST l', $_POST['l'], $IP);}
if (isset($_POST['l'])) {
  $unsubscribeme_key = preg_replace("/[^A-Za-z0-9]/","", $_POST['l']);
  if (preg_match('/[^a-zA-Z0-9]/', $unsubscribeme_key)) {
    header("Location: /index.php");
    exit(); // Quit the script}
  }
} else {
  header("Location: /index.php");
  exit(); // Quit the script
}
if ((isset($_POST['e'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['e']))) {$IP = get_ip_addr(); script_kiddy('sk_86', '_POST e', $_POST['e'], $IP);}
if (isset($_POST['e'])) {
  if (preg_match ('/^[a-zA-Z0-9]{64}$/i', $_POST['e'])) {
    $unsubscribeme_get_sec_key = preg_replace("/[^A-Za-z0-9]/","", $_POST['e']);
  }
} else {
  header("Location: /index.php");
  exit(); // Quit the script
}

// Verify that the key is real
$q = "SELECT userid, email FROM emailwrongunsubscribe WHERE delkey='$unsubscribeme_key' AND useable='live'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // No key
  header("Location: /index.php");
  exit(); // Quit the script
} else {
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $userid = $row[0];
  $unsubscribeme_email = $row[1];
}

// Find out which email we are unsubscribing
$q = "SELECT sec_key, email, confirmed_email FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$user_sec_key = $row[0];
$user_email = $row[1];
$user_confirmed_email = $row[2];

// Verify that the email is correct
if ($unsubscribeme_get_sec_key != $user_sec_key) {
  header("Location: /index.php");
  exit(); // Quit the script
}

// Check to see if a subscription exists
$q = "SELECT email FROM emailsubscriptions WHERE email='$unsubscribeme_email'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows > 0) { // Subscription present
  // Unsubscribe from newsletter
  $qs = "DELETE FROM emailsubscriptions WHERE email='$unsubscribeme_email'";
  $rs = mysqli_query ($dbc, $qs);
  //if (mysqli_affected_rows($dbc) != 1) { // If it had a problem
  if (!$rs) { // If it had a problem
  	sql_error($qs, 'dbc', "sqle_50");
  }
}

// Set the _SESSION for the message page
$_SESSION['newsletter_unsubscribe'] = true;
