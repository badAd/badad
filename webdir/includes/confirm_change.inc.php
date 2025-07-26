<?php

// $userid OR $_SESSION['user_id'] must be set before including this!

// Set a variable for the userid
if (!isset($userid)) {
  if (isset($_SESSION['user_id'])) {
    $userid = $_SESSION['user_id'];
  } else {
    return;
  }
}

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Create the password link
$pstring = longDashScoreString(255);
$cstring = longDashScoreString(255);
// Dup check
$q = "SELECT confirmkey FROM confirmchange WHERE binary confirmkey='$cstring'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($dbc, $q);
while ($dup = mysqli_fetch_array($row)) {
  $cstring = longDashScoreString(255);
}
// Dup check
$q = "SELECT temppass FROM confirmchange WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($dbc, $q);
while ($dup = mysqli_fetch_array($row)) {
  $pstring = longDashScoreString(255);
}

// Add the link to the database:
$q = "INSERT INTO confirmchange (userid, confirmkey, temppass, date_dead) VALUES ('$userid', '$cstring', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -45 DAY))";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) > 0) { // If it ran OK

  // Send an email
  $payloadlinkyes = "https://$siteDomain/info_confirmed.php?c=$cstring";
  $payloadlinkno = "https://$siteDomain/info_repair.php?p=$pstring";

  $canned_email = "confirm_change"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p><a href=\"$payloadlinkyes\">Yes, I made this request.</a><br /><br /><a href=\"$payloadlinkno\">No, I didn't!</a> (Click to change password NOW.)</p><p>Answering helps keep your account secure.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  $confirm_change = true;
  $emailUserID = $userid; // For non-logged-in users, viz password_reset
  include ('./includes/sendusrmail.inc.php');

  // Done
  return; // Stop the script

} else { // If it did not run OK
  sql_error($q, 'dbc', "sqle_45");
}
