<?php

// Must be set: $canned_email $subject_suffix $payload_content $footer_link_content
// Either must be set: $emailUserID OR _SESSION['user_id']

// For EMAIL CONFIRMATION: simply set the variable: $confirm_email before including this file (to avoid new address confirmation being sent to the old-confirmed address)

/* To use, insert this cluster:
// Send canned email (DO NOT $concatenate . $variables!! "Include $variables in quotes." This avoides DKIM fails for "body changed" tests.)
//$confirm_change = true; // For confirmation change to account
//$confirm_email = true; // For confirmation email addressess
//$emailUserID = $userid; // For non-logged-in users, viz password_reset
//$mass_log_no_bcc = true; // For mass-user system emails
$canned_email = "feedback"; // Slug from the "pantry" table to select the canned email
$subject_suffix = " - Subject Suffix"; // Appends the canned email Subject
$payload_content = "<p>I am the payload. This might be an important link: <a href=\"http://$siteDomain\">Click here to visit PDA</a>.</p>"; // Middle of the Body, after the canned email and before the salutation
$footer_link_content = "<p>Full links here can break DKIM, usually just use empty quotes"; // After the salutation and before the unsubscribe footer
include ('/srv/www/badad/webdir/includes/sendusrmail.inc.php');
*/

if ((isset($_SESSION['user_id'])) && (!isset($mass_log_no_bcc))) {
  $emailUserID = $_SESSION['user_id']; // Set the $emailUserID variable for the email fetching and logging system
} elseif (!isset($emailUserID)) {
  return;
}

// Sending a system email needs the _SRV config
require_once ('/srv/www/badad/webdir/includes/config_eml.inc.php');
require_once (MYSQL_EML);

// Get the user's info to populate the form
$qu = "SELECT sec_key, name, email, confirmed_email FROM users WHERE id='$emailUserID'";
$ru = mysqli_query ($dbc, $qu);
$row = mysqli_fetch_array($ru, MYSQLI_NUM);
$eml_sec_key = "$row[0]";
$eml_name = "$row[1]";
$eml_email = "$row[2]";
$eml_confirmed_email = "$row[3]";

// Determine how to use emails
if (($eml_email != "UNSUBSCRIBED") && (($eml_email == $eml_confirmed_email) || ($eml_confirmed_email == "Unconfirmed") || (isset($confirm_email)))) {
  $sending_email_string = '"'.$eml_name.'" <'.$eml_email.'>';
  // Unsubscribe key
  $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_email' AND useable='live' ORDER BY id DESC LIMIT 1";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($r) == 0) { // Test for dead keys, re-do
    include ('includes/emailwrong_create.inc.php');
    $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_email' AND useable='live' ORDER BY id DESC LIMIT 1";
    $r = mysqli_query ($dbc, $q);
  }
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $eml_email_delkey = "$row[0]";
  $unsubscribe_url = "https://$siteDomain/$eml_email_delkey/$eml_sec_key/unsubscribe.html";
  $unsubscribe_footer = '<br /><br /><br /><table width="100%" bgcolor="#000" border="0" cellspacing="0" cellpadding="3"><tr align="center"><td style="color: #fff"><a style="color: #fff; text-decoration: none" title="'.$siteDomain.'" href="https://'.$siteDomain.'">'.$siteTitle.'</a> | <a style="color: #fff; text-decoration: none" title="Unsubscribe immediately" href="'.$unsubscribe_url.'">Unsubscribe</a></td></tr></table>';
  // Count
  $email_addr_count = 1;
} elseif (($eml_confirmed_email != "Unconfirmed") && ((isset($confirm_change)) || ($eml_email == "UNSUBSCRIBED"))) {
  $sending_email_string = '"'.$eml_name.'" <'.$eml_confirmed_email.'>';
  // Unsubscribe key
  $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_confirmed_email' AND useable='live' ORDER BY id DESC LIMIT 1";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($r) == 0) { // Test for dead keys, re-do
    $only_confirmed_email = true;
    include ('includes/emailwrong_create.inc.php');
    unset($only_confirmed_email);
    $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_confirmed_email' AND useable='live' ORDER BY id DESC LIMIT 1";
    $r = mysqli_query ($dbc, $q);
  }
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $eml_email_delkey = "$row[0]";
  $unsubscribe_url = "https://$siteDomain/$eml_email_delkey/$eml_sec_key/unsubscribe.html";
  $unsubscribe_footer = '<br /><br /><br /><table width="100%" bgcolor="#000" border="0" cellspacing="0" cellpadding="3"><tr align="center"><td style="color: #fff"><a style="color: #fff; text-decoration: none" title="'.$siteDomain.'" href="https://'.$siteDomain.'">'.$siteTitle.'</a> | <a style="color: #fff; text-decoration: none" title="Unsubscribe immediately" href="'.$unsubscribe_url.'">Unsubscribe</a></td></tr></table>';
  // Count
  $email_addr_count = 1;
} elseif (($eml_email != "UNSUBSCRIBED") && ($eml_confirmed_email != "Unconfirmed")) {
  // New Email
  $sending_new_email_string = '"'.$eml_name.'" <'.$eml_email.'>';
  // Unsubscribe key
  $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_email' AND useable='live' ORDER BY id DESC LIMIT 1";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($r) == 0) { // Test for dead keys, re-do
    include ('includes/emailwrong_create.inc.php');
    $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_email' AND useable='live' ORDER BY id DESC LIMIT 1";
    $r = mysqli_query ($dbc, $q);
  }
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $eml_new_email_delkey = "$row[0]";
  $unsubscribe_new_email_url = "https://$siteDomain/$eml_new_email_delkey/$eml_sec_key/unsubscribe.html";
  $unsubscribe_new_email_footer = '<br /><br /><br /><table width="100%" bgcolor="#000" border="0" cellspacing="0" cellpadding="3"><tr align="center"><td style="color: #fff"><a style="color: #fff; text-decoration: none" title="'.$siteDomain.'" href="https://'.$siteDomain.'">'.$siteTitle.'</a> | <a style="color: #fff; text-decoration: none" title="Unsubscribe immediately" href="'.$unsubscribe_new_email_url.'">Unsubscribe</a></td></tr></table>';
  // Confirmed Email
  $sending_confirmed_email_string = '"'.$eml_name.'" <'.$eml_confirmed_email.'>';
  // Unsubscribe key
  $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_confirmed_email' AND useable='live' ORDER BY id DESC LIMIT 1";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($r) == 0) { // Test for dead keys, re-do
    $only_confirmed_email = true;
    include ('includes/emailwrong_create.inc.php');
    unset($only_confirmed_email);
    $q = "SELECT delkey FROM emailwrongunsubscribe WHERE userid='$emailUserID' AND email='$eml_confirmed_email' AND useable='live' ORDER BY id DESC LIMIT 1";
    $r = mysqli_query ($dbc, $q);
  }
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $eml_confirmed_email_delkey = "$row[0]";
  $unsubscribe_confirmed_email_url = "https://$siteDomain/$eml_confirmed_email_delkey/$eml_sec_key/unsubscribe.html";
  $unsubscribe_confirmed_email_footer = '<br /><br /><br /><table width="100%" bgcolor="#000" border="0" cellspacing="0" cellpadding="3"><tr align="center"><td style="color: #fff"><a style="color: #fff; text-decoration: none" title="'.$siteDomain.'" href="https://'.$siteDomain.'">'.$siteTitle.'</a> | <a style="color: #fff; text-decoration: none" title="Unsubscribe immediately" href="'.$unsubscribe_confirmed_email_url.'">Unsubscribe</a></td></tr></table>';
  // Count
  $email_addr_count = 2;
} else {
  echo "Catastrophic error! No email on file for user!";
  sql_error($qu,dbc,NO_EMAIL_FOR_USR);
}

// Get the email info
$q = "SELECT id, ver, subject, body, footer FROM pantry WHERE slug='$canned_email' ORDER BY created DESC LIMIT 1";
$r = mysqli_query($eml_dbc, $q);
if (mysqli_num_rows($r) == 0) {sql_error($q, 'eml_dbc', "sqle_sendusrmail_pantry");}
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$can_id = $row[0];
$can_ver = $row[1];
$can_subject = $row[2];
$can_body = $row[3];
$can_footer = $row[4];

// Generate the Subject
$sending_subject = $can_subject . $subject_suffix;

// Generate the Body
if ($can_footer == NULL) { // "footer" is optional in the pantry
  $sending_body = "<p>$eml_name,</p>\n$can_body\n$payload_content\n$site_email_footer\n$footer_link_content"; // Must use "quotes" and \n new line, not $concatenated . $variables, otherwise a "body altered" DKIM test fails
} else {
  $sending_body = "<p>$eml_name,</p>\n$can_body\n$payload_content\n$can_footer\n$site_email_footer\n$footer_link_content"; // Must use "quotes" and \n new line, not $concatenated . $variables, otherwise a "body altered" DKIM test fails
}
// Send the email
//ini_set( 'display_errors', 1 ); // for debugging
//error_reporting( E_ALL ); // for debugging

// HTML email requirements
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
// Common
$from = '"'.$site_from_email_name.'" <'.$site_from_email.'>';
$subject = $sending_subject;
$headers .= "From: " . $from . "\r\n";
if (!isset($mass_log_no_bcc)) {
  $headers .= "Bcc: " . $site_bcc_email;
}
// Confirmed or single email
if ($email_addr_count == 1) {
  $to = $sending_email_string;
  $message = $sending_body . $unsubscribe_footer;
  //DEV for testing what the email looks like
  // echo htmlentities("from: $from<br><br>mail($to,$subject,$message,$headers);");
  // exit();
  // ini_set( 'display_errors', 1 );
  // error_reporting( E_ALL );
  //DEV//
  mail($to,$subject,$message,$headers);
    // Log the email
    $q = "INSERT INTO sent_log (user_id, user_email, can_id, can_ver) VALUES ('$emailUserID', '$sending_email_string', '$can_id', '$can_ver')";
    $r = mysqli_query ($eml_dbc, $q);
    if (mysqli_affected_rows($eml_dbc) != 1) {sql_error($q, 'eml_dbc', "sqle_sendusrmail_log_one");}
// Changed unconfirmed email
} elseif ($email_addr_count == 2) {
  // New email
  $to = $sending_new_email_string;
  $message = $sending_body . $unsubscribe_new_email_footer;
  mail($to,$subject,$message,$headers);
    // Log the email
    $q = "INSERT INTO sent_log (user_id, user_email, can_id, can_ver) VALUES ('$emailUserID', '$sending_new_email_string', '$can_id', '$can_ver')";
    $r = mysqli_query ($eml_dbc, $q);
    if (mysqli_affected_rows($eml_dbc) != 1) {sql_error($q, 'eml_dbc', "sqle_sendusrmail_log_new");}
  // Confirmed email
  $to = $sending_confirmed_email_string;
  $message = $sending_body . $unsubscribe_confirmed_email_footer;
  mail($to,$subject,$message,$headers);
    // Log the email
    $q = "INSERT INTO sent_log (user_id, user_email, can_id, can_ver) VALUES ('$emailUserID', '$sending_confirmed_email_string', '$can_id', '$can_ver')";
    $r = mysqli_query ($eml_dbc, $q);
    if (mysqli_affected_rows($eml_dbc) != 1) {sql_error($q, 'eml_dbc', "sqle_sendusrmail_log_cnf");}
}
