<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the site ID
if ((isset($_POST['del_site'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['del_site']))) {$IP = get_ip_addr(); script_kiddy('sk_55', '_POST del_site', $_POST['del_site'], $IP);}
if ((isset($_POST['del_site'])) && (filter_var($_POST['del_site'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $site_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['del_site']);
} else {
  header("Location: partner_del_project.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// Validate domain
if ((isset($_POST['del_site_domain'])) && (preg_match ('/[^a-zA-Z0-9-.]$/i', $_POST['del_site_domain']))) {$IP = get_ip_addr(); script_kiddy('sk_53', '_POST del_site_domain', $_POST['del_site_domain'], $IP);}
if ((isset($_POST['del_site_domain_check'])) && (preg_match ('/[^a-zA-Z0-9-.]$/i', $_POST['del_site_domain_check']))) {$IP = get_ip_addr(); script_kiddy('sk_54', '_POST del_site_domain_check', $_POST['del_site_domain_check'], $IP);}

if ((isset($_POST['del_site_domain'])) && (isset($_POST['del_site_domain_check']))) { $deldomain = $_POST['del_site_domain']; } else { header("Location: partner.php"); exit(); }
$deldomain = str_replace('www.','',$deldomain);
if ($_POST['del_site_domain'] != $_POST['del_site_domain_check']) {
  // Include the header file
  $page_title = "Delete Fail :: $siteTitle";
  include ('./includes/header.html');
  echo "<p class=\"note_red\">You typed the wrong domain. Try again.</p>";
  include ('./includes/footer.html');
  exit();
} else {
  $deldomain = strtolower($deldomain);
  $deldomain = mysqli_real_escape_string ($dbc, $deldomain);
}

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Make sure the user is an Activated Partner
$q = "SELECT email_confirmed FROM partners WHERE user_id='$userid'";
$r = mysqli_query ($dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) { // Not a partner
  header("Location: partner.php");
  exit(); // Quit the script
}
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$confirmed = "$row[0]";
// Redirect if not activated
if ($confirmed != 'Confirmed') {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Create the delete key
// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');
// Create the password link
$cstring = longDashScoreString(255);
// Check the string to be unique
$qd = "SELECT confirmkey FROM confirmdelsite WHERE binary confirmkey='$cstring'";
$rowd = mysqli_query ($srv_dbc, $qd);
$qc = "SELECT confirmkey FROM confirmpartnerchange WHERE binary confirmkey='$cstring'";
$rowc = mysqli_query ($srv_dbc, $qc);
while (($dupd = mysqli_fetch_array($rowd)) || ($dupc = mysqli_fetch_array($rowc))) {
  $cstring = longDashScoreString(255);
}

// Add the link to the database:
$q = "INSERT INTO confirmpartnerchange (userid, confirmkey, date_dead) VALUES ('$userid', '$cstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
$r = mysqli_query ($srv_dbc, $q);
if (mysqli_affected_rows($srv_dbc) != 1) {sql_error($q, 'srv_dbc', "sqle_6");}
$qp = "INSERT INTO confirmdelsite (userid, siteid, confirmkey, date_dead) VALUES ('$userid', '$site_id', '$cstring', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -42 MINUTE))";
$rp = mysqli_query ($srv_dbc, $qp);
if (mysqli_affected_rows($srv_dbc) != 1) {sql_error($qr, 'srv_dbc', "sqle_7");}

// Set the site for deletion
$q = "UPDATE partnersites SET useable='deleted' WHERE id='$site_id' AND user_id='$userid' AND (domain='$deldomain' OR serial_no='$deldomain') AND useable='off'";
$r = mysqli_query ($srv_dbc, $q);
if (mysqli_affected_rows($srv_dbc) == 1) {
  $_SESSION['del_domain'] = "$deldomain (ID #$site_id)";
  // Send the Partner account change email
  $payloadlinkyes = "https://$siteDomain/partner_del_project_confirmed.php?c=$cstring";
  $payloadlinkno = "https://$siteDomain/partner_del_project_repair.php?c=$cstring";
  $canned_email = "partner_site_delete"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p>Do you want to delete the domain: <b>$deldomain</b> (ID #$site_id)</p><p><a href=\"$payloadlinkyes\">Yes, confirm. Delete this site project forever!</a><br /><br /><a href=\"$payloadlinkno\">No, cancel this request!</a> (Just cancel, NO password change.)</p><p>Answering helps keep your account secure.</p>";
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  $confirm_change = true;
  include ('./includes/sendusrmail.inc.php');

  // Redirect via Javascript wtih _POST set for security
  // Thanks https://stackoverflow.com/a/5576700/10343144
  echo "
  <form id=\"jsGoForm\" action=\"partner_del_project.php\" method=\"post\">
    <input type=\"hidden\" name=\"del_partner_site_page\" value=\"$userid\">
  </form>
  <script type=\"text/javascript\">
      document.getElementById('jsGoForm').submit();
  </script>";

  exit(); // Quit the script

} else { // If typed domain does not match
  sql_error($qr, 'srv_dbc', "sqle_8");
}
