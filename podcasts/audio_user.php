<?php

//In case you want to show errors
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// This links from podcastads.serialno via, without counting downloads or access
// https://podcasts.badad.one/audio/badad-sOm3l0NgStr1nG.mp3

// Configs
require_once ('./config.inc.php');
require_once (MYSQL);
require_once ('./config_agg.inc.php');
require_once (MYSQL_AGG);
require_once ('./config_srv.inc.php');
require_once (MYSQL_SRV);

// If the user isn't logged in, redirect them
redirect_invalid_user();
$userID = $_SESSION['user_id'];

// _GET the ad ID
if ((isset($_GET['a'])) && (filter_var($_GET['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $serialno = preg_replace("/[^A-Za-z0-9]/","", $_GET['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Escape globally
$esc_serialno = mysqli_real_escape_string($agg_dbc, $serialno);

// Select the info from episode_ad_keys and render from there
// Fetch the ad
$q = "SELECT pod_ad_id, rerun_pod_ad_id FROM podcastads WHERE serialno='$esc_serialno'";
$row = mysqli_query($agg_dbc, $q);
$ad_item = mysqli_fetch_array($row);
	$pod_ad_id = "$ad_item[0]";
  $orig_pod_ad_id = "$ad_item[1]";
  $orig_pod_ad_id = ($orig_pod_ad_id == 0) ? $pod_ad_id : $orig_pod_ad_id;

// Make sure this user owns the ad
$q = "SELECT customer_user FROM pod_ads WHERE ad_id='$pod_ad_id' AND customer_user='$userID'";
$row = mysqli_query ($srv_dbc, $q);
if (mysqli_num_rows($row) == 0) {
  header("Location: index.php");
  exit(); // Quit the script
}

// Supply a link to the audio file
$file = "$badadsrvdir/media/ba-{$orig_pod_ad_id}.mp3";

// Serve the file under the key name that brought us here
if (file_exists($file)) {
  header('Content-Description: File Transfer');
  header("Content-Transfer-Encoding: binary");
  header('Content-Type: audio/mpeg');
  //header('Content-Disposition: attachment; filename="badad-'.$serialno.'.mp3"');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: '.filesize($file));
  readfile($file);
  exit;
}

?>
