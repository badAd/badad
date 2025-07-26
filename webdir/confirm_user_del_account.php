<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userid = $_SESSION['user_id'];

// Make sure we're not here on accident
if (!isset($_POST['clicked_delete_user_account'])) {
  header("Location: account_info.php");
  exit(); // Quit the script
} elseif ($_POST['clicked_delete_user_account'] != $userid) {
  header("Location: account_info.php");
  exit(); // Quit the script
}

// We need database connection
require (MYSQL);

// Include the header file
$page_title = "Delete User Account :: $siteTitle";
include ('./includes/header.html');

// Check if a Partner account has been activated
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows == 1) {
  echo '<h3 class="note_yellow">Remove your Partner account first</h3>
  <p>You have a Partner account, even if it is dormant. To prevent abuse, you cannot delete your user account until you delete your <a title="Partner Center" href="partner.php"><b>Partner account</b></a> first.</p>';
  // Include the HTML footer
  include ('./includes/footer.html');
  exit();
}

// Check if any ads are still active
$q = "SELECT id FROM ads WHERE user_id='$userid' AND pub_status='live' AND date_expires>CURRENT_TIMESTAMP";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$rows = mysqli_num_rows($r);
if ($rows > 0) {
  echo '<h3 class="note_yellow">Remove your ads first</h3>
  <p>You have some ads still live. You cannot delete your user account until your ads either finish their run or you click to "kill" them in your <a title="Order History" href="order_history.php"><b>Order History</b></a>.</p>';
  // Include the HTML footer
  include ('./includes/footer.html');
  exit();
}

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
  $q = "SELECT name, email, confirmed_email FROM users WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $userName = "$row[0]";
  $userEmail = "$row[1]";
  $userEmailConfirmed = "$row[2]";

  // Where do we send the change email?
  if (($userEmail == $userEmailConfirmed) || ($userEmailConfirmed == "Unconfirmed")) {
    $toString = '"'.$userName.'" <'.$userEmail.'>';
  } else {
    $toString = '"'.$userName.'" <'.$userEmail.'>, "'.$userName.'" <'.$userEmailConfirmed.'>';
  }

  // Send an email
  $payloadlinkyes = "https://$siteDomain/user_del_account_confirmed.php?c=$cstring";
  $payloadlinkno = "https://$siteDomain/info_repair.php?p=$pstring";

  $canned_email = "confirm_user_del_account"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p><a href=\"$payloadlinkyes\">Yes, I made this request.</a><br /><br /><a href=\"$payloadlinkno\">No, I didn't!</a> (Click to change password NOW.)</p><p>If this wasn't you, let us know now so as to keep your account secure.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  include ('./includes/sendusrmail.inc.php');

  // Notify the user
  echo '<h3>Email sent!</h3><p>The email with the link to the button to delete your account should be on its way. <i>You will need to already be logged in</i> with the browser used to open the link.</p><p>Hurry up! That link will expire in about 42 minutes.</p>';


} else { // If it did not run OK
  sql_error($q, 'dbc', "sqle_68");
}

// Include the HTML footer
include ('./includes/footer.html');
?>
