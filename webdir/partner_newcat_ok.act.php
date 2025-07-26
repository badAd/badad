<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);


// Make sure we're not here on accident
if ((!isset($_POST['uid'])) || (!isset($_SESSION['user_id'])) || (!isset($_SESSION['partner_need_see_new_categories']))) {
  header("Location: index.php");
  exit(); // Quit the script
} elseif ($_POST['uid'] != $_SESSION['user_id']) {
  header("Location: index.php");
  exit(); // Quit the script
} else {
  $userid = $_SESSION['user_id'];
}

// Update the database
$q = "UPDATE partners SET need_see_new_categories=false WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
if ($r) {
  // Redirect to Partner Center
  unset($_SESSION['partner_need_see_new_categories']);
  header("Location: partner.php");
  exit(); // Quit the script
} else {
  sql_error($q, 'dbc', "sqle_86");
}

?>
