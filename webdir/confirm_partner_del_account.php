<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Make sure we're not here on accident
if (!isset($_POST['clicked_delete_partner_account'])) {
  header("Location: partner.php");
  exit(); // Quit the script
} elseif ($_POST['clicked_delete_partner_account'] != $userid) {
  header("Location: partner.php");
  exit(); // Quit the script
}

// We need database connection
require (MYSQL);

// Include the header file
$page_title = "Delete Partner Account :: $siteTitle";
include ('./includes/header.html');

////// Just in case: In theory, this page should not be possible to reach if this query returns true
// Get Partner's email and check that it is verified
$qe = "SELECT email, email_confirmed FROM partners WHERE user_id='$userid'";
$re = mysqli_query ($dbc, $qe);
$rowe = mysqli_fetch_array ($re, MYSQLI_NUM);
$partnerEmail = "$rowe[0]";
$email_confirmed = "$rowe[1]";
if ($email_confirmed != "Confirmed") {
  echo '<h3 class="note_yellow">Confirm your Partner email first</h3>
  <p>You cannot delete your Partner account when your current Partner email address has not been confirmed.</p>';
  // Process the email confirmation if changed
  include ('includes/confirm_partner_email.inc.php');
  // Include the HTML footer
  include ('./includes/footer.html');
  exit();
}
//////

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');
// Create the password link
$pstring = longDashScoreString(255);
$cstring = longDashScoreString(255);
// Check the string to be unique
$q = "SELECT confirmkey FROM confirmchange WHERE binary confirmkey='$cstring'";
$row = mysqli_query ($dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $cstring = longDashScoreString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $cstring = longDashScoreString(255);
  // Check again
  $q = "SELECT confirmkey FROM confirmchange WHERE binary confirmkey='$cstring'";
  $row = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Check the string to be unique
$q = "SELECT temppass FROM confirmchange WHERE binary temppass='$pstring'";
$row = mysqli_query ($dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $pstring = longDashScoreString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $pstring = longDashScoreString(255);
  // Check again
  $q = "SELECT temppass FROM confirmchange WHERE binary temppass='$pstring'";
  $row = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Add the link to the database:
$q = "INSERT INTO confirmchange (userid, confirmkey, temppass, date_dead) VALUES ('$userid', '$cstring', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

  // Get the user's info from the database for the email
  $q = "SELECT name FROM users WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $userName = "$row[0]";

  // Send an email
  $payloadlinkyes = "https://$siteDomain/partner_del_account_confirmed.php?c=$cstring";
  $payloadlinkno = "https://$siteDomain/info_repair.php?p=$pstring";

  $canned_email = "confirm_partner_del_account"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p><a href=\"$payloadlinkyes\">Yes, I made this request.</a><br /><br /><a href=\"$payloadlinkno\">No, I didn't!</a> (Click to change password NOW.)</p><p>If this wasn't you, let us know now so as to keep your account secure.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  include ('./includes/sendusrmail.inc.php');

  // Notify the user
  echo '<h3>Email sent!</h3><p>The email with the link to the button to delete your Partner account should be on its way. <i>You will need to already be logged in</i> with the browser used to open the link.</p><p>Hurry up! That link will expire in about 42 minutes.</p>';


} else { // If it did not run OK
  sql_error($q, 'dbc', "sqle_69");
}

// Include the HTML footer
include ('./includes/footer.html');
?>
