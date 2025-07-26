<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// Require the database connection
require (MYSQL);

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_EMAIL))) {
  $unsubscribeme_email = $_POST['a'];
} elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['a']))) {
  $IP = get_ip_addr(); script_kiddy('sk_69', '_POST a', $_POST['a'], $IP);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Get the user's email
$q = "SELECT email FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$userEmail = "$row[0]";

// Redirect if it is not the user's email
if ($unsubscribeme_email != $userEmail) {
  // Destroy the session
  $_SESSION = array(); // Destroy the variables
  session_destroy(); // Destroy the session itself
  setcookie (session_name(), '', time()-300); // Destroy the cookie
  // Get out of here
  header("Location: index.php");
  exit(); // Quit the script
}

// Check to see if the subscription exists
$q = "SELECT email FROM emailsubscriptions WHERE email='$unsubscribeme_email'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows != 0) { // Already subscribed
  header("Location: account_info.php");
  exit(); // Quit the script
}

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Create the delkey string
$delstring = longString(255);

// Add the email to the subscription database
$q = "INSERT INTO emailsubscriptions (email, delkey) VALUES ('$unsubscribeme_email', '$delstring')";
$r = mysqli_query ($dbc, $q);

if (mysqli_affected_rows($dbc) == 1) { // If it ran OK
  // Return to Account Information
  header("Location: account_info.php");
  exit(); // Quit the script
} else { // If it did not run OK
	sql_error($q, 'dbc', "sqle_59");
}

?>
