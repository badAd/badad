<?php

/* Use this way:
$canned_email = "confirm_partner_dev_change"; // Slug from the "pantry" table to select the canned email
$payload_content = "<p>You made this change: <b>$pdomain</b></p>"; // Start custom content to be appended by the canned email body content.
include ('./includes/confirm_partner_dev_change.inc.php');
*/

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Set a variable for the userid
$userid = $_SESSION['user_id'];

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Create the password link
$pstring = longDashScoreString(255);
$cstring = longDashScoreString(255);
// Dup check
$q = "SELECT confirmkey FROM confirmdevappchange WHERE binary confirmkey='$cstring'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($srv_dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $cstring = longDashScoreString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $cstring = longDashScoreString(255);
  // Check again
  $q = "SELECT confirmkey FROM confirmdevappchange WHERE binary confirmkey='$cstring'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}
// Dup check
$q = "SELECT temppass FROM confirmdevappchange WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($srv_dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $pstring = longDashScoreString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $pstring = longDashScoreString(255);
  // Check again
  $q = "SELECT temppass FROM confirmdevappchange WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Add the link to the database:
$q = "INSERT INTO confirmdevappchange (userid, confirmkey, temppass, date_dead) VALUES ('$userid', '$cstring', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -45 DAY))";
$r = mysqli_query ($srv_dbc, $q);
if (mysqli_affected_rows($srv_dbc) > 0) { // If it ran OK

  // Send an email
  $payloadlinkyes = "https://$siteDomain/partner_dev_info_confirmed.php?c=$cstring";
  $payloadlinkno = "https://$siteDomain/partner_dev_info_repair.php?p=$pstring";

  if (!isset($canned_email)) {$canned_email = "confirm_partner_dev_change";} // Slug from the "pantry" table to select the canned email
  if (!isset($payload_content)) {$payload_content = "";} // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content .= "<p><a href=\"$payloadlinkyes\">Yes, I made this request.</a><br /><br /><a href=\"$payloadlinkno\">No, I didn't!</a> (Click to change password NOW.)</p><p>Answering helps keep your account secure.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  $confirm_change = true;
  include ('./includes/sendusrmail.inc.php');

  // Done
  return; // Stop the script

} else { // If it did not run OK
  sql_error($q, 'srv_dbc', "sqle_107");
}
