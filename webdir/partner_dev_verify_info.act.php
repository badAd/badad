<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

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
$qt = "UPDATE confirmdevappchange SET useable='dead' WHERE userid='$userid'";
$rt = mysqli_query ($srv_dbc, $qt);
if ($rt) { // If it ran OK.

  // Kill any loginonce and logincode keys
  include_once ('./includes/clear_keys.inc.php');

  // Redirect to Partner Center
  $_SESSION['partner_all_confirmed'] = true;
  unset($_SESSION['partner_dev_must_verify']);
  header("Location: partner.php");
  exit(); // Quit the script
} else {
  sql_error($qt, 'srv_dbc', "sqle_115");
}

?>
