<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);


// _GET the subscription ID for _GET e and _GET l
if ((isset($_GET['l'])) && (isset($_GET['e'])) && (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['l'])) && (preg_match ('/^[a-zA-Z0-9]{64}$/i', $_GET['e']))) {
    $unsubscribeme_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
    $unsubscribeme_get_sec_key = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['e']);
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
  	sql_error($qs, 'dbc', "sqle_46");
  }
}

// Update the user status
if (($unsubscribeme_email == $user_email) && ($user_confirmed_email != "Unconfirmed")) {$q = "UPDATE users SET email='UNSUBSCRIBED', status='emailwrong' WHERE id='$userid'";}
elseif (($unsubscribeme_email == $user_email) && ($user_confirmed_email == "Unconfirmed")) {$q = "UPDATE users SET status='emailwrong' WHERE id='$userid'";}
elseif ($unsubscribeme_email == $user_confirmed_email) {$q = "UPDATE users SET confirmed_email='Unconfirmed' WHERE id='$userid'";}
else {
  header("Location: /index.php");
  exit(); // Quit the script
}
$r = mysqli_query ($dbc, $q);
if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
  sql_error($q, 'dbc', "sqle_47");
}

// Mark the key as used
$q = "UPDATE emailwrongunsubscribe SET date_done=CURRENT_TIMESTAMP, useable='used' WHERE delkey='$unsubscribeme_key'"; // Mark this key as used
$r = mysqli_query ($dbc, $q);
//if (mysqli_affected_rows($dbc) != 1) { // If it didn't run okay
if (!$r) { // If it didn't run okay
  sql_error($q, 'dbc', "sqle_48");
}
// Kill all other live keys for this user
$q = "UPDATE emailwrongunsubscribe SET date_done=CURRENT_TIMESTAMP, useable='dead' WHERE userid='$userid' AND useable='live'"; // Remove all other emailwrongunsubscribe keys
$r = mysqli_query ($dbc, $q);
if (!$r) { // If it didn't run okay
  sql_error($q, 'dbc', "sqle_49");
}

// Set the _SESSION for the message page
$_SESSION['unsubscribed'] = true;

if (isset($_SESSION['user_id'])) {$userid = $_SESSION['user_id'];}

// Get the user's info to populate the form
$q = "SELECT status FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$user_status = "$row[0]";
if ($user_status == "emailwrong") {$_SESSION['no_status'] = true;}
