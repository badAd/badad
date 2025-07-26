<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_78', '_POST a', $_POST['a'], $IP);}
if ((isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the clone
include ('./includes/clone_ad.inc.php');

$new_ad_comment = "Rerun from $adID";
$_SESSION['new_ad_comment'] = $new_ad_comment;

// Make sure its a business ad
if ($new_ad_biz_listing != 'biz') {
  // Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');
  // Redirect to the proper place
  header("Location: index.php");
  exit(); // Quit the script
}

// Special mod settings
$rerun_how = 'Modified';
$_SESSION['rerun_how'] = $rerun_how;
$rerun_id = $adID;
$_SESSION['rerun_id'] = $rerun_id;
$new_ad_weekslong = NULL;
$_SESSION['new_ad_weekslong'] = $new_ad_weekslong;

// See if it has been 8 weeks since the ad started
$epochNow = strtotime($timeNow);
$epochOldStart = strtotime($old_ad_date_starts);
$epochOldExpire = strtotime($old_ad_date_expires);
$epochOldChangable = ($epochOldStart + 4838400); // Add 8 weeks to the start epoch
$epochOldLastChange = ($epochOldExpire - 604800); // One week before ad expires

if (($epochOldLastChange < $epochNow) || ($epochOldLastChange < $epochOldChangable) || ($modified_yn == 'Modified')) { // Quit if one week before expiry
  // Include the header
  $page_title = "Cannot Modify Your Ad :: $siteTitle";
  include ('includes/header.html');

  // In-page title
  echo "<h3>Cannot Modify Your Ad:</h3>";

  // Message
  echo "<p class=\"note_red\">Cannot modify an ad one week before it expires.</p>";

	// Include the HTML footer
	include ('includes/footer.html');
	// Unset _SESSION values
	include ('./includes/ad_values_unset.inc.php');
	exit();
}

$dateOldChangable = (new DateTime("@$epochOldChangable"))->format('Y-m-d H:i:s'); // Convert 8 weeks added epoch to date


if ($epochOldChangable < $epochNow) {
  $new_ad_date_starts = $timeNow;
} else {
  $new_ad_date_starts = $dateOldChangable;
}
$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;

// Prepare our $pretty_after_ad_date_to_start
if ($old_ad_date_expires < $timeNow) {
  $cloned_status = 'expired';
} else {
  $cloned_status = 'running';
}

// Let everyone know what we're doing
$_SESSION['mod_ad'] = true;

// Redirect to the proper place
header("Location: mod_ad.php");
exit(); // Quit the script

?>
