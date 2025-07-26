<?php
// This receives a "Contact" link, counts analytics, grants a referral credit if the user has it turned on, then the redirects to the main site
// site.php/html and resite.php/html are identical, site comes through the embedded ads, resite was sent through the Dev API and used by developers

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// Require the database connection
require_once (MYSQL);

// Validate the serial number: a-zA-Z0-9
if (!isset($_GET['l'])) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Validate the serial number: a-zA-Z0-9
if ((isset($_GET['l'])) && (preg_match ('/[a-zA-Z0-9]$/i', $_GET['l']))) {
  $pdaref_serial = preg_replace("/[^A-Za-z0-9-_]/","", $_GET['l']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// $pdaref_serial must be set and escaped globally
$esc_pdaref_serial = mysqli_real_escape_string($srv_dbc, $pdaref_serial);

  // Get the settings from the partnersites table
  $q = "SELECT id, user_id, clicked_badad_count FROM partnersites WHERE binary badadref_no='$esc_pdaref_serial'";
  $r = mysqli_query($srv_dbc, $q);
  	// Get the number of rows returned & exit if none
  	$rows = mysqli_num_rows($r);
  	if ($rows == 0) {exit();}
  // Site variables
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $partnersite_id = "$row[0]";
  $refUserID = "$row[1]";
  $clicked_badad_count = "$row[2]";

  // Increment the list
  $newClickCount = $clicked_badad_count +1;

  // Update the new viewcount
  $qu = "UPDATE partnersites SET clicked_badad_count='$newClickCount' WHERE binary badadref_no='$esc_pdaref_serial'";
  $ru = mysqli_query ($srv_dbc, $qu);
  // If partnersite count ran okay
  //if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Partner site click not counted."; }

  // Update the analytics
  $aq = "INSERT INTO clicked_badad_analytics (partnersite_id, time_date, time_epoch) VALUES ('$partnersite_id', '$timeNow', '$timeNowEpoch')";
  $ar = mysqli_query ($srv_dbc, $aq);
  //if (!mysqli_affected_rows($srv_dbc) == 1) { echo "Ad not counted."; }


// Referred?
// Check the slug against the database
$q = "SELECT reflink FROM referrallinks WHERE userid='$refUserID'";
$r = mysqli_query ($dbc, $q);
// Get the number of rows returned
$rows = mysqli_num_rows($r);
if ($rows == 1) { // Link exists
	$row = mysqli_fetch_array($r, MYSQLI_NUM);
	$rSlug = "$row[0]";
	$_SESSION['refUserID'] = $refUserID;
	$_SESSION['rSlug'] = $rSlug;
}

// Done, redirect to where we are going
header("Location: /help_videos.php");
exit(); // Quit the script

?>
