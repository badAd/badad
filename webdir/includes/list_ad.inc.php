<?php

// $adID, must be set when including this script!

// Listing the ads needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// See if the ad has already been listed
$q = "SELECT ad_id FROM listads WHERE ad_id='$adID'";
$r = mysqli_query($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows > 0) {
	return; // Quit if there is a dup
}

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Get the info from the ad
$qr = "SELECT pub_status, date_created, date_starts, date_expires, category_id, subcat_id, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_lang, epoch_wk_reset FROM ads WHERE id='$adID'";
$cr = mysqli_query($dbc, $qr);
$list_ad_item = mysqli_fetch_array($cr, MYSQLI_NUM);
		// Check if live
		if (($list_ad_item[0] == 'live') && ($timeNow < $list_ad_item[3])) {
			// Assign variables
			$list_ad_pub_status = $list_ad_item[0];
			$list_ad_date_created = $list_ad_item[1];
			$list_ad_date_starts = $list_ad_item[2];
			$list_ad_date_expires = $list_ad_item[3];
			$list_ad_cat_id = $list_ad_item[4];
			$list_ad_subcat_id = $list_ad_item[5];
			$list_ad_hdng = mysqli_real_escape_string($srv_dbc, $list_ad_item[6]);
			$list_ad_dscr = mysqli_real_escape_string($srv_dbc, $list_ad_item[7]);
			$list_ad_info = mysqli_real_escape_string($srv_dbc, $list_ad_item[8]);
			$list_ad_pyrt = mysqli_real_escape_string($srv_dbc, $list_ad_item[9]);
			$list_ad_cntc = mysqli_real_escape_string($srv_dbc, $list_ad_item[10]);
			$list_ad_bizn = mysqli_real_escape_string($srv_dbc, $list_ad_item[11]);
			$list_ad_biz_listing = $list_ad_item[12];
			$list_ad_lang = $list_ad_item[13];
			$list_epoch_wk_reset = $list_ad_item[14];

    } else {
      echo "<p class=\"note_red\">Ad is expired and can't be listed.</p>";
      return;
    }

// Get the most recent serial number
$q = "SELECT serialno FROM listads ORDER BY id DESC LIMIT 1";
$r = mysqli_query($srv_dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$Serial = "$row[0]";

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
increment($Serial);

// Get the ad's expiration date
//$q = "SELECT date_expires, date_created FROM ads WHERE id='$adID'";
//$r = mysqli_query ($dbc, $q);
//$row = mysqli_fetch_array($r, MYSQLI_NUM);
//$expString = "$row[0]";
//$creString = "$row[1]";

// Convert "2022-05-14 14:26:26" format to epoch
$expEpoch = strtotime($list_ad_date_expires);
$staEpoch = strtotime($list_ad_date_starts);
$creEpoch = strtotime($list_ad_date_created);

// Get the global subcat ID
$q = "SELECT id FROM global_subcat_ids WHERE cat_id='$list_ad_cat_id' AND subcat_id='$list_ad_subcat_id'";
$r = mysqli_query ($srv_dbc, $q);
$rows = mysqli_num_rows($r);
if ($rows == 0) {
	echo "No global subs!";
	return; // Quit if there is a dup
}
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$global_subcat_ID = "$row[0]";

$q = "INSERT INTO listads (ad_id, global_subcat_id, serialno, pub_status, epoch_wk_reset, epoch_created, epoch_starts, epoch_dead, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_lang)
VALUES ('$adID', '$global_subcat_ID', '$Serial', '$list_ad_pub_status', '$list_epoch_wk_reset', '$creEpoch', '$staEpoch', '$expEpoch',
'$list_ad_hdng', '$list_ad_dscr', '$list_ad_info', '$list_ad_pyrt', '$list_ad_cntc', '$list_ad_bizn', '$list_ad_biz_listing', '$list_ad_lang')";
$r = mysqli_query ($srv_dbc, $q);
if (mysqli_affected_rows($srv_dbc) != 1) { // SQL listads success

	sql_error($q, 'srv_dbc', "sqle_155");

} // Successful SQL listads entry
