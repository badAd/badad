<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// We need database connection
require (MYSQL);

// Listing the ads & first purchases needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Include the header file
$page_title = "Checkout :: $siteTitle";
include ('./includes/header.html');

$userid = 1;

// First purchase?
$q = "SELECT join_rank FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$join_rank = "$row[0]";
if ($join_rank == NULL) {
  // See if the user was already in the join list
  $q = "SELECT userid FROM join_rank WHERE userid='$userid'";
  $r = mysqli_query ($srv_dbc, $q);
  $rows = mysqli_num_rows($r);
  if ($rows == 0) { // Join the user if not
    $qt = "INSERT INTO join_rank (userid) VALUES ('$userid')";
    $rt = mysqli_query ($srv_dbc, $qt);
    if (mysqli_affected_rows($srv_dbc) == 0) { // If didn't run okay
      echo "Couldn't note your joining in the main database.";
    }
  }
  // Get the new join_rank
  $q = "SELECT id FROM join_rank WHERE userid='$userid'";
  $r = mysqli_query($srv_dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $join_rank = "$row[0]";
  // Add the join_rank to the users table
  $q = "UPDATE users SET join_rank='$join_rank' WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) { // If it ran okay
    // Process the email confirmation
		include ('includes/confirm_email.inc.php');
  } else {
    echo "User has a rank, but due to a technical difficulty it could not be updated in the user's database.";
  } // Added user's join_rank
} else {
  echo "User could not receive an official joined membership! Note and contact us about this ASAP!";
} // Created user's join_rank



// Include the HTML footer
include ('./includes/footer.html');
?>
