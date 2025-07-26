<?php

// If successful, this sets a message: $confirmationEmailSent, which can be echoed later in the page

// Set a variable for the userid, either from $_SESSION['user_id'] or $userid is already set
if (isset($_SESSION['user_id'])) {
  $userid = $_SESSION['user_id'];
} elseif (!isset($userid)) {
  return;
}

// Get email confirmation status from the dateabse, leave if confirmed or not joined
$q = "SELECT email, confirmed_email, join_rank FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$email = "$row[0]";
$confirmed_email = "$row[1]";
$join_rank = "$row[2]";
if (($email == $confirmed_email) || ($join_rank == NULL)) {
  return;
}

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Create the password link
$pstring = longDashScoreString(255);

// Check the string to be unique
$q = "SELECT temppass FROM confirmemail WHERE binary temppass='$pstring'";
$row = mysqli_query ($dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $pstring = longDashScoreString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $pstring = longDashScoreString(255);
  // Check again
  $q = "SELECT temppass FROM confirmemail WHERE binary temppass='$pstring'";
  $row = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Get user's email
$q = "SELECT email FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$email = $row[0];

// Add the link to the database
$q = "INSERT INTO confirmemail (userid, email, temppass, date_dead) VALUES ('$userid', '$email', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
$r = mysqli_query ($dbc, $q);

if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

	// Get the new user's name from the database for the email
  $q = "SELECT name FROM users WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array ($r, MYSQLI_NUM);
  $userName = $row[0];

	// Send the email
  $payloadlink = "https://$siteDomain/email_confirmed.php?p=$pstring";

  $confirm_email = true; // For confirmation email addressess
  $canned_email = "confirm_email"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p> <a href=\"$payloadlink\">This is the link to confirm your email</a> and it will expire after 40 minutes.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  include ('./includes/sendusrmail.inc.php');
  unset($confirm_email); // We don't want to trip other snares

	// Print a message and wrap up
	$confirmationEmailSent = '<h3 class="note_yellow">Confirmation email sent</h3><p>An email has been sent to your address ('.$email.') with a confirmation link. <b>Hurry up! That link will expire in about 42 minutes.</b></p>';

} else {
  sql_error($q, 'dbc', "sqle_44");
}
