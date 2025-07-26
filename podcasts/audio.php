<?php

//In case you want to show errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// This links from episode_ad_keys.serial_key via, counting downloads for both the ad and the feed it appears in via this key
// https://podcasts.badad.one/badad-sOm3l0NgStr1nG.mp3

// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// _GET the ad ID
if (isset($_GET['a'])) {
  $serial_key = preg_replace("/[^A-Za-z0-9]/","", $_GET['a']);
} else {
  header("Location: https://$siteDomain");
  exit(); // Quit the script
}

// Escape globally
$esc_serial_key = mysqli_real_escape_string($agg_dbc, $serial_key);

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);

// Select the info from episode_ad_keys and render from there
// Fetch the ad
$q = "SELECT pod_ad_id, orig_pod_ad_id, feed_pid FROM episode_ad_keys WHERE serial_key='$esc_serial_key'";
// AND user_id='$userID'
$row = mysqli_query($agg_dbc, $q);
$ad_item = mysqli_fetch_array($row);
	$pod_ad_id = "$ad_item[0]";
  $orig_pod_ad_id = "$ad_item[1]";
	$feed_pid = "$ad_item[2]";
  $orig_pod_ad_id = ($orig_pod_ad_id == 0) ? $pod_ad_id : $orig_pod_ad_id;


// Pod ad expired?
$q = "SELECT id FROM podcastads WHERE pod_ad_id='$pod_ad_id' AND epoch_dead > $timeNowEpoch";
$r = mysqli_query($agg_dbc, $q);
  if (mysqli_num_rows($r) == 1) {

  // Update podcastads count
  $q = "SELECT ad_download_count, list_wk_count FROM podcastads WHERE pod_ad_id='$pod_ad_id'";
  $row = mysqli_query($agg_dbc, $q);
  $ad_item = mysqli_fetch_array($row);
  	$ad_download_count = "$ad_item[0]";
    $list_wk_count = "$ad_item[1]";
  $ad_download_count ++;
  $list_wk_count ++;
  $q = "UPDATE podcastads SET ad_download_count='$ad_download_count', list_wk_count='$list_wk_count' WHERE pod_ad_id='$pod_ad_id'";
  $r = mysqli_query ($agg_dbc, $q);
  if ($r === false) { // Simple check for failure without requiring "affected rows"
    sql_error($q, 'agg_dbc', "sqle_168");
  }

  // Update feeds count
  $q = "SELECT ad_download_count FROM feeds WHERE project_id='$feed_pid'";
  $row = mysqli_query($agg_dbc, $q);
  $ad_item = mysqli_fetch_array($row);
  	$ad_download_count = "$ad_item[0]";
  $ad_download_count ++;
  $q = "UPDATE feeds SET ad_download_count='$ad_download_count' WHERE project_id='$feed_pid'";
  $r = mysqli_query ($agg_dbc, $q);
  if ($r === false) { // Simple check for failure without requiring "affected rows"
    sql_error($q, 'agg_dbc', "sqle_169");
  }

  // Update the analytics
  $q = "INSERT INTO downloaded_ad_analytics (ad_id, time_date, time_epoch) VALUES ('$pod_ad_id', '$timeNow', '$timeNowEpoch')";
  $r = mysqli_query ($agg_dbc, $q);
  if (mysqli_affected_rows($agg_dbc) != 1) { // INSERT should affect rows
    sql_error($q, 'agg_dbc', "sqle_172");
  }

} else { // Pod ad expired?
  $orig_pod_ad_id = 0;
}

// Supply a link to the audio file
$file = "$badadsrvdir/media/ba-{$orig_pod_ad_id}.mp3";

// Serve the file under the key name that brought us here
if (file_exists($file)) {
  header('Content-Description: File Transfer');
  header("Content-Transfer-Encoding: binary");
  header('Content-Type: audio/mpeg3');
  //header('Content-Disposition: attachment; filename="badad-'.$serial_key.'.mp3"'); // This will make it ask for a download
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: '.filesize($file));
  readfile($file);
  exit;
}

?>
