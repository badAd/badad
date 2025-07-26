<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the site ID
if ((isset($_POST['POSTNAME'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['s']))) {$IP = get_ip_addr(); script_kiddy('sk_60', '_POST s', $_POST['s'], $IP);}
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['s']))
&& (filter_var($_POST['s'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $dev_app_id = preg_replace("/[^A-Za-z0-9]/","", $_POST['s']);
} else {
  header("Location: partner.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

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

// Get the SQL info
$qd="SELECT test_pub_key, test_sec_key, live_pub_key, live_sec_key, domain, name FROM devkeys WHERE id='$dev_app_id'";
$rd = mysqli_query ($srv_dbc, $qd);
if (mysqli_num_rows($rd) == 1) {
  // Assign the values
  $row = mysqli_fetch_array($rd);
  $old_test_pub_key = "$row[0]";
  $old_test_sec_key = "$row[1]";
  $old_live_pub_key = "$row[2]";
  $old_live_sec_key = "$row[3]";
  $dapp_domain = "$row[4]";
  $dapp_name = "$row[5]";
} else {
  sql_error($qd, 'srv_dbc', "sqle_116");
}


// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');

// Create the test_pub_key
$dev_test_pub_key = longString(64);
$dev_test_pub_key = "test_pub_$dev_test_pub_key";

// Dup check
$q = "SELECT test_pub_key FROM devkeys WHERE binary test_pub_key='$dev_test_pub_key'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($srv_dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $dev_test_pub_key = longString(64);
//   $dev_test_pub_key = "test_pub_$dev_test_pub_key";
// }
while (mysqli_num_rows($row) != 0) {
  $dev_test_pub_key = longString(64);
  $dev_test_pub_key = "test_pub_$dev_test_pub_key";
  // Check again
  $q = "SELECT test_pub_key FROM devkeys WHERE binary test_pub_key='$dev_test_pub_key'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Create the test_sec_key
$dev_test_sec_key = longString(64);
$dev_test_sec_key = "test_sec_$dev_test_sec_key";

// Dup check
$q = "SELECT test_sec_key FROM devkeys WHERE binary test_sec_key='$dev_test_sec_key'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($srv_dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $dev_test_sec_key = longString(64);
//   $dev_test_sec_key = "test_sec_$dev_test_sec_key";
// }
while (mysqli_num_rows($row) != 0) {
  $dev_test_sec_key = longString(64);
  $dev_test_sec_key = "test_sec_$dev_test_sec_key";
  // Check again
  $q = "SELECT test_sec_key FROM devkeys WHERE binary test_sec_key='$dev_test_sec_key'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Create the live_pub_key
$dev_live_pub_key = longString(64);
$dev_live_pub_key = "live_pub_$dev_live_pub_key";

// Dup check
$q = "SELECT live_pub_key FROM devkeys WHERE binary live_pub_key='$dev_live_pub_key'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($srv_dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $dev_live_pub_key = longString(64);
//   $dev_live_pub_key = "live_pub_$dev_live_pub_key";
// }
while (mysqli_num_rows($row) != 0) {
  $dev_live_pub_key = longString(64);
  $dev_live_pub_key = "live_pub_$dev_live_pub_key";
  // Check again
  $q = "SELECT live_pub_key FROM devkeys WHERE binary live_pub_key='$dev_live_pub_key'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Create the live_sec_key
$dev_live_sec_key = longString(64);
$dev_live_sec_key = "live_sec_$dev_live_sec_key";

// Dup check
$q = "SELECT live_sec_key FROM devkeys WHERE binary live_sec_key='$dev_live_sec_key'"; // "binary" makes sure case and characters are exact
$row = mysqli_query ($srv_dbc, $q);
// while ($dup = mysqli_fetch_array($row)) {
//   $dev_live_sec_key = longString(64);
//   $dev_live_sec_key = "live_sec_$dev_live_sec_key";
// }
while (mysqli_num_rows($row) != 0) {
  $dev_live_sec_key = longString(64);
  $dev_live_sec_key = "live_sec_$dev_live_sec_key";
  // Check again
  $q = "SELECT live_sec_key FROM devkeys WHERE binary live_sec_key='$dev_live_sec_key'"; // "binary" makes sure case and characters are exact
  $row = mysqli_query ($srv_dbc, $q);
  if (mysqli_num_rows($row) == 0) {
    break;
  }
}

// Add old test keys to the database
$qi = "INSERT INTO olddevkeys (devkey_id, old_pub_key, old_sec_key)
VALUES ('$dev_app_id', '$old_test_pub_key', '$old_test_sec_key')";
$ri = mysqli_query ($srv_dbc, $qi);
if (mysqli_affected_rows($srv_dbc) != 1) {
  sql_error($qi, 'srv_dbc', "sqle_117");
}

// Add old live keys to the database
$qi = "INSERT INTO olddevkeys (devkey_id, old_pub_key, old_sec_key)
VALUES ('$dev_app_id', '$old_live_pub_key', '$old_live_sec_key')";
$ri = mysqli_query ($srv_dbc, $qi);
if (mysqli_affected_rows($srv_dbc) != 1) {
  sql_error($qi, 'srv_dbc', "sqle_118");
} else {
  $_SESSION['new_key_success'] = $dapp_name;

  // Send the confirmation email
  $canned_email = "confirm_partner_dev_change"; // Slug from the "pantry" table to select the canned email
  $payload_content = "<p>You got new keys for a Dev App in the Developer Center.</p>
  <p>Name: <b>$dapp_name</b><br />
  Domain: <b>$dapp_domain</b><br />";
  include ('./includes/confirm_partner_dev_change.inc.php');
}


// Set the Dev App to live
$qu = "UPDATE devkeys SET test_pub_key='$dev_test_pub_key', test_sec_key='$dev_test_sec_key', live_pub_key='$dev_live_pub_key', live_sec_key='$dev_live_sec_key', old_pub_key='$old_live_pub_key', old_sec_key='$old_live_sec_key', date_newkeys=NOW() WHERE id='$dev_app_id'";
$ru = mysqli_query ($srv_dbc, $qu);
if (mysqli_affected_rows($srv_dbc) != 1) {
  sql_error($qi, 'srv_dbc', "sqle_119");
} else {
  $key_update_success = true;
}

// Final check
if ((isset($_SESSION['new_key_success'])) && (isset($key_update_success))) {

  echo "
  <form id=\"jsGoForm\" action=\"partner_dev.php\" method=\"post\">
    <input type=\"hidden\" name=\"partner_dev\" value=\"$userid\">
  </form>
  <script type=\"text/javascript\">
      document.getElementById('jsGoForm').submit();
  </script>";

  exit(); // Quit the script

}
