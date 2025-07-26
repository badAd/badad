<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Make sure we're not here on accident
if ((!isset($_POST['uid'])) || (!isset($_SESSION['user_id']))) {
  header("Location: index.php");
  exit(); // Quit the script
} elseif ($_POST['uid'] != $_SESSION['user_id']) {
  header("Location: index.php");
  exit(); // Quit the script
} else {
  $userid = $_SESSION['user_id'];
}

// Disable all temporary confirmation passwords
$qt = "UPDATE confirmchange SET useable='dead' WHERE userid='$userid'";
if ($rt = mysqli_query ($dbc, $qt)) { // If it ran OK.

  // Kill any loginonce and logincode keys
  include_once ('./includes/clear_keys.inc.php');

  // Redirect to Account Information
  $_SESSION['all_confirmed'] = true;
  unset($_SESSION['user_must_verify']);
  header("Location: account_info.php");
  exit(); // Quit the script
} else {
  sql_error($qt, 'dbc', "sqle_37");
}

?>
