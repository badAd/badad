<?php

// This action page should only be accessed from a logged-in user via _POST

// OPTION: set $only_confirmed_email to only generate a key for the confirmed_email
// OPTION: set $confirming_email to remove all other emailwrongunsubscribe keys because the new email is all that exists

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// Get the user's email
$q = "SELECT email, confirmed_email FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$userEmail = "$row[0]";
$userConfirmedEmail = "$row[1]";

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Create the delkey string
$delstring = longString(255);
// Dup check
$q = "SELECT delkey FROM emailwrongunsubscribe WHERE binary delkey='$delstring'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $delstring = longString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $delstring = longString(255);
  // Check again
  $q = "SELECT delkey FROM emailwrongunsubscribe WHERE binary delkey='$delstring'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Remove any older keys
if ((isset($confirming_email)) || ($userConfirmedEmail == $userEmail)) {
  $q = "UPDATE emailwrongunsubscribe SET date_done=CURRENT_TIMESTAMP, useable='dead' WHERE userid='$userid' AND useable='live'"; // Remove all keys (for confirmed email)
} elseif (isset($only_confirmed_email)) {
  $q = "UPDATE emailwrongunsubscribe SET date_done=CURRENT_TIMESTAMP, useable='dead' WHERE userid='$userid' AND email='$userConfirmedEmail' AND useable='live'"; // Remove any previous keys for this email address (new, unconfirmed email)
} else {
  $q = "UPDATE emailwrongunsubscribe SET date_done=CURRENT_TIMESTAMP, useable='dead' WHERE userid='$userid' AND email='$userEmail' AND useable='live'"; // Remove any previous keys for this email address (new, unconfirmed email)
}
$r = mysqli_query ($dbc, $q);
//if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
if (!$r) { // If it didn't run okay
  sql_error($q, 'dbc', "sqle_56");
}

// Add the email to the subscription database
if ((isset($only_confirmed_email)) && ($userConfirmedEmail != "Unconfirmed")) { // If this is being run only for a confirmed_email, such as sendusrmail.inc
  $q = "INSERT INTO emailwrongunsubscribe (userid, email, delkey) VALUES ('$userid', '$userConfirmedEmail', '$delstring')";
} elseif ($userEmail != "UNSUBSCRIBED") { // If this is being used for most normal email purposes
  $q = "INSERT INTO emailwrongunsubscribe (userid, email, delkey) VALUES ('$userid', '$userEmail', '$delstring')";
} else {

}
$r = mysqli_query ($dbc, $q);

//if (mysqli_affected_rows($dbc) != 1) { // If it had a problem
if (!$r) { // If it didn't run okay
  sql_error($q, 'dbc', "sqle_57");
}
