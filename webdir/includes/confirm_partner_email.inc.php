<?php

// Set a variable for the userid
$userid = $_SESSION['user_id'];

// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');
// Create the password link
$pstring = longDashScoreString(255);
// Dup check
$q = "SELECT temppass FROM confirmemail WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $pstring = longDashScoreString(255);
// }
while (mysqli_num_rows($row) != 0) {
  $pstring = longDashScoreString(255);
  // Check again
  $q = "SELECT temppass FROM confirmemail WHERE binary temppass='$pstring'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Get Partner's email
$q = "SELECT email FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$email = $row[0];

// Add the link to the database
$q = "INSERT INTO confirmemail (userid, email, temppass, date_dead) VALUES ('$userid', '$email', '$pstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
$r = mysqli_query ($dbc, $q);

if (mysqli_affected_rows($dbc) == 1) { // If it ran OK

	// Get the new partner's name from the database for the email
  $q = "SELECT name FROM users WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array ($r, MYSQLI_NUM);
  $userName = $row[0];

	// Send the email
  $payloadlink = "https://$siteDomain/email_partner_confirmed.php?p=$pstring";

  $canned_email = "confirm_partner_email"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p><a href=\"$payloadlink\">This is the link to confirm your email address</a> and activate your Partner Account. It will expire after 40 minutes.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  include ('./includes/sendusrmail.inc.php');

	// Print a message and wrap up
	echo '<h3 class="note_yellow">Activation email sent</h3><p>An email has been sent to your address with a confirmation link to finish activating your partner account. <b>Hurry up! That link will expire in about 42 minutes.</b></p>';

} else {
  sql_error($q, 'dbc', "sqle_73");
}
