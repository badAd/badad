<?php

// DEPRECIATED

/* Uncomment this if setting as a non- .inc. page to stand alone, such as for a cron task
// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session
// Require the database connection
require_once (MYSQL);
*/

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Listed ads
$ql = "SELECT ad_id, epoch_wk_reset, epoch_dead FROM listads WHERE pub_status='live'";
$rl = mysqli_query ($srv_dbc, $ql);
while ($row = mysqli_fetch_array($rl)) {
  $List_ad_id = $row[0];
  $List_epoch_wk_reset = $row[1];
  $List_epoch_dead = $row[2];

  // Reset old week counts
  if ($timeNowEpoch >= $List_epoch_wk_reset) {
    // Loop until it is in the future
    $resetEpoch = ($List_epoch_wk_reset + 604800);
    while ($timeNowEpoch >= $resetEpoch) {
      $resetEpoch = ($resetEpoch + 604800);
    }
    // Update the status
    $q = "UPDATE listads SET epoch_wk_reset='$resetEpoch' WHERE ad_id='$List_ad_id'";
    $r = mysqli_query ($srv_dbc, $q);
    if (!mysqli_affected_rows($srv_dbc) > 0) { // If it didn't run okay
      echo "Week reset cound not be updated in the database for \"$List_ad_id\".<br />";
    }
  }

  // Kill expired ads
  if ($timeNowEpoch >= $List_epoch_dead) {
    // Update the status
    $q = "UPDATE listads SET pub_status='expired' WHERE ad_id='$List_ad_id'";
    $r = mysqli_query ($srv_dbc, $q);
    if (!mysqli_affected_rows($srv_dbc) > 0) { // If it didn't run okay
      echo "Status cound not be updated in the database for \"$List_ad_id\".<br />";
    }
  }
}

// Ads
$qa = "SELECT id, epoch_wk_reset, date_expires FROM ads WHERE pub_status='live'";
$ra = mysqli_query ($dbc, $qa);
while ($row = mysqli_fetch_array($ra)) {
  $ad_id = $row[0];
  $ad_epoch_wk_reset = $row[1];
  $ad_date_expires = $row[2];

  // Reset old week counts
  if ($timeNowEpoch >= $ad_epoch_wk_reset) {
    // Loop until it is in the future
    $resetEpoch = ($ad_epoch_wk_reset + 604800);
    while ($timeNowEpoch >= $resetEpoch) {
      $resetEpoch = ($resetEpoch + 604800);
    }
    // Update the status
    $q = "UPDATE ads SET epoch_wk_reset='$resetEpoch', week_view_count=0, week_cat_count=0, week_tag_count=0, week_search_count=0 WHERE ad_id='$ad_id'";
    $r = mysqli_query ($dbc, $q);
    if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
      echo "Status cound not be updated in the database for \"$ad_id\".<br />";
    }
    continue;
  }

  // Kill expired ads
  $ad_epoch_dead = strtotime($ad_date_expires);
  if ($timeNowEpoch >= $ad_epoch_dead) {
    // Update the status
    $q = "UPDATE ads SET pub_status='expired' WHERE id='$ad_id'";
    $r = mysqli_query ($dbc, $q);
    if (!mysqli_affected_rows($dbc) > 0) { // If it didn't run okay
      echo "Status cound not be updated in the database for \"$ad_id\".<br />";
    }
    continue;
  }
}
