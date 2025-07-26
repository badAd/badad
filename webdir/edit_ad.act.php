<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_83', '_POST a', $_POST['a'], $IP);}
if ((isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the clone
include ('./includes/clone_ad.inc.php');

$_SESSION['editing_id'] = $adID;
$new_ad_comment = "Mod from $adID";
$_SESSION['new_ad_comment'] = $new_ad_comment;

// Make sure the ad hasn't started yet
if ($new_ad_date_starts < $timeNow) {
  header("Location: index.php");
  exit(); // Quit the script
} else {
  $_SESSION['editing_status'] = "waiting";
}

// Redirect to the proper place
if ($new_ad_biz_listing == 'biz') {
  header("Location: edit_ad.php?c=$cat&b");
  exit(); // Quit the script
} elseif ($new_ad_biz_listing == 'non') {
  header("Location: edit_ad.php?c=$cat");
  exit(); // Quit the script
}

?>
