<?php

// $adID, must be set when including this script!

// Config
require_once ('includes/config.inc.php');
require_once (MYSQL);
require_once ('includes/config_srv.inc.php');
require_once (MYSQL_SRV);
require_once ('config_agg.inc.php');
require_once (MYSQL_AGG);

// See if the ad has already been listed
$q = "SELECT ad_id FROM listads WHERE ad_id='$adID'";
$r = mysqli_query($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows < 1) {
	echo "<p class=\"note_red\">Text ad not listed. This is strange. Pod ad unchanged.</p>";
	return; // Quit if there is a dup
}

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Get the info from the ad
$qr = "SELECT pub_status, date_created, date_expires, category_id, subcat_id, ad_lang, ad_weekslong FROM ads WHERE id='$adID'";
$cr = mysqli_query($dbc, $qr);
$list_ad_item = mysqli_fetch_array($cr, MYSQLI_NUM);
// Check if live
if (($list_ad_item[0] == 'live') && ($timeNow < $list_ad_item[2])) {
	// Assign variables
	$list_ad_pub_status = $list_ad_item[0];
	$list_ad_date_created = $list_ad_item[1];
	$list_ad_date_expires = $list_ad_item[2];
	$list_ad_cat_id = $list_ad_item[3];
	$list_ad_subcat_id = $list_ad_item[4];
	$list_ad_lang = $list_ad_item[5];
	$list_ad_weekslong  = $list_ad_item[6];

} else {
  echo "<p class=\"note_red\">Ad is expired and can't be listed.</p>";
  return;
}

// Convert "2022-05-14 14:26:26" format to epoch
$timeNowEpoch = strtotime($timeNow);
$staEpoch = $timeNowEpoch;
$creEpoch = strtotime($list_ad_date_created);
$expEpoch = $staEpoch + (60 * 60 * 24 * 7 * $list_ad_weekslong);
$expSQL = date("Y-m-d H:i:s", substr($expEpoch, 0, 10));
$resetEpoch = $timeNowEpoch + 604800;
$staSQL = $timeNow;

$q = "SELECT id, length, duration, rerun_pod_id FROM pod_ads WHERE ad_id='$adID'";
$r = mysqli_query ($dbc, $q);
while ($row = mysqli_fetch_array($r)) {
	$pod_ad_ID = "$row[0]";
	$length = "$row[1]";
	$duration = "$row[2]";
	$rerun_pod_id = "$row[3]";
}

// Get the global subcat ID
$q = "SELECT id FROM global_subcat_ids WHERE cat_id='$list_ad_cat_id' AND subcat_id='$list_ad_subcat_id'";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	echo "No global subs!";
	return; // Quit if there is a dup
}
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$global_subcat_id = "$row[0]";

// DEV not using keys, using serial no
// // Create the serial_key
// require_once ('./includes/string_functions.inc.php');
// $new_serial = longString(128);
//
// // Dup check
// $q = "SELECT serial_key FROM podcastads WHERE binary serial_key='$new_serial'"; // "binary" makes sure case and characters are exact
// $row = mysqli_query ($agg_dbc, $q);
//
// while (mysqli_num_rows($row) != 0) {
//   $new_serial = longString(128);
//   // Check again
//   $q = "SELECT test_pub_key FROM devkeys WHERE binary test_pub_key='$new_serial'"; // "binary" makes sure case and characters are exact
//   $row = mysqli_query ($agg_dbc, $q);
//   if (mysqli_num_rows($row) == 0) {
//     break;
//   }
// }

// Get the most recent serial number
$q = "SELECT serialno FROM listads ORDER BY id DESC LIMIT 1";
$r = mysqli_query($srv_dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$new_serial = "$row[0]";

// Process the incrementing serial number
// Thanks https://stackoverflow.com/a/8362760/10343144
function increment(&$string){
    $last_char=substr($string,-1);
    $rest=substr($string, 0, -1);
    switch ($last_char) {
    case '':
        $next= 'a';
        break;
    case 'z':
        $next= 'A';
        break;
    case 'Z':
        $next= '0';
        break;
    case '9':
        increment($rest);
        $next= 'a';
        break;
    default:
        $next= ++$last_char;
    }
    $string=$rest.$next;
}

// Increment the serial number
increment($new_serial);

// Prepare for entry
$new_serial_esc = mysqli_real_escape_string($agg_dbc, $new_serial);

// Duration
// SHELL SCRIPT HERE

// Enter
$q = "INSERT INTO podcastads (
	pod_ad_id,
	ad_id,
	ad_lang,
	global_subcat_id,
	serialno,
	duration,
	enclosure_aud_length,
	pub_status,
	epoch_wk_reset,
	epoch_created,
	epoch_starts,
	epoch_dead,
	rerun_pod_ad_id
) VALUES (
	'$pod_ad_ID',
	'$adID',
	'$list_ad_lang',
	'$global_subcat_id',
	'$new_serial_esc',
	'$duration',
	'$length',
	'$list_ad_pub_status',
	'$resetEpoch',
	'$creEpoch',
	'$staEpoch',
	'$expEpoch',
	'$rerun_pod_id'
)";
$r = mysqli_query ($agg_dbc, $q);
if (mysqli_affected_rows($agg_dbc) != 1) {
	sql_error($q, 'agg_dbc', "sqle_163");
} else {
	$q = "UPDATE pod_ads SET
	date_starts='$staSQL',
	date_expires='$expSQL'
	WHERE id='$adID'";
	$r = mysqli_query ($dbc, $q);
	if ($r === false) {
		sql_error($q, 'dbc', "sqle_164");
	} else {
		$pod_ad_listed = true;
	}
}
