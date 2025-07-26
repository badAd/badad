<?php

// _POST the ad ID
if ((!isset($_POST['a'])) && (!isset($_SESSION['rerun_ad'])) && (!isset($_SESSION['rerun_id']))) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Include the clone
include ('./includes/clone_ad.inc.php');

// Don't inherit previous weekslong
$new_ad_weekslong = 1;
$_SESSION['new_ad_weekslong'] = $new_ad_weekslong;

// Special clone settings
$rerun_how = 'Rerun';
$_SESSION['rerun_how'] = $rerun_how;
$rerun_id = $adID;
$_SESSION['rerun_id'] = $rerun_id;
$new_ad_nickname = $new_ad_nickname.' RERUN';
$new_ad_nickname = str_replace("RERUN RERUN","RERUN",$new_ad_nickname);
$_SESSION['new_ad_nickname'] = $new_ad_nickname;

// Set the time for the rerun ad to start (if it is in the past, this will reset to the current time when the ad goes live)
$new_ad_date_starts = $_SESSION['old_ad_date_expires'];
$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;

// Prepare our $pretty_after_ad_date_to_start
if ($old_ad_date_expires < $timeNow) {
  $cloned_status = 'expired';
} else {
  $cloned_status = 'running';
}
$_SESSION['cloned_status'] = $cloned_status;

// Let everyone know what we're doing
$_SESSION['rerun_ad'] = true;

// Redirect to the proper place
header("Location: rerun_ad_cart.php");
exit(); // Quit the script

?>
