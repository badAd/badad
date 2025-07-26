<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// Require the database connection
require (MYSQL);

// If accessed via _POST
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['e'])) && (filter_var($_POST['e'], FILTER_VALIDATE_EMAIL))) {
  $unsubscribeme_email = $_POST['e'];
  // Check to see if the subscription exists
  $q = "SELECT email FROM emailsubscriptions WHERE email='$unsubscribeme_email'";
  $r = mysqli_query ($dbc, $q);
  $rows = mysqli_num_rows($r);
  if ($rows != 1) { // No subscription, we shouldn't be here
    header("Location: index.php");
    exit(); // Quit the script
  }

  // Unsubscribe
  $q = "DELETE FROM emailsubscriptions WHERE email='$unsubscribeme_email'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) {
    header("Location: account_info.php");
    exit(); // Quit the script
  } else {
    sql_error($q, 'dbc', "sqle_60");
  }

} elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['e']))) {
  $IP = get_ip_addr(); script_kiddy('sk_70', '_POST e', $_POST['e'], $IP);
} else { // No _POST, we shouldn't be here
  header("Location: index.php");
  exit(); // Quit the script
}

?>
