<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the site ID
if ((isset($_POST['i'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['i']))) {$IP = get_ip_addr(); script_kiddy('sk_77', '_POST i', $_POST['i'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['i']))
&& (filter_var($_POST['i'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $key_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['i']);
} else {
  header("Location: account_info.php");
  exit(); // Quit the script
}

// We need database connection
require (MYSQL);

// Destroy any persistent login cookies (small steps to be sure)
$q = "DELETE FROM rememberme WHERE id='$key_id' AND userid='$userid'"; // "binary" makes sure case and characters are exact
$r = mysqli_query ($dbc, $q);

header("Location: account_info.php"); // We can't set $_SESSION['logged_out'] on the same page we destroyed the session
exit(); // Quit the script


?>
