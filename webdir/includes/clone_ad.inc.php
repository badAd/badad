<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require_once ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_11', '_POST a', $_POST['a'], $IP);}
if ((isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} elseif ((isset($_SESSION['rerun_ad'])) && (isset($_SESSION['rerun_id']))) {
  $adID = $_SESSION['rerun_id'];
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Require the database connection
require (MYSQL);

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Get the ad
$adID = mysqli_real_escape_string ($dbc, $adID);
$q = "SELECT category_id, subcat_id, role_id, tag_ids, ad_comment, ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_weekslong, date_starts, date_expires, ad_lang, rerun_id, rerun_how, modified_yn, podcast_ad FROM ads WHERE id='$adID' AND user_id='$userid' LIMIT 1";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$catID = "$row[0]";
$subcatID = "$row[1]";
$roleID = "$row[2]";
$new_ad_tagIDs = "$row[3]";
$new_ad_comment = "$row[4]";
$new_ad_nickname = "$row[5]";
$new_ad_heading = "$row[6]";
$new_ad_description = "$row[7]";
$new_ad_info = "$row[8]";
$new_ad_pricing = "$row[9]";
$new_ad_contactURL = "$row[10]";
$new_ad_content_bizn = "$row[11]";
$new_ad_biz_listing = "$row[12]";
$new_ad_weekslong = "$row[13]";
$new_ad_date_starts = "$row[14]";
$new_ad_date_expires = "$row[15]";
$new_ad_lang = "$row[16]";
$old_rerun_id = "$row[17]";
$old_rerun_how = "$row[18]";
$modified_yn = "$row[19]";
$podcast_ad = "$row[20]";
// A few flexible variables
$old_ad_date_starts = $new_ad_date_starts;
$old_ad_date_expires = $new_ad_date_expires;
$old_ad_nickname = $new_ad_nickname;
if ($old_rerun_id == "") {$old_rerun_id = $adID;}

// If already cloned, just in case
$qrr = "SELECT ad_nickname, date_expires FROM ads WHERE rerun_id='$old_rerun_id' AND user_id='$userid' ORDER BY date_expires DESC LIMIT 1";
$rrr = mysqli_query ($dbc, $qrr);
$rows = mysqli_num_rows($rrr);
if ($rows == 1) {
  $row = mysqli_fetch_array($rrr, MYSQLI_NUM);
  $new_ad_nickname = "$row[0]";
  $old_ad_date_expires = "$row[1]";
}

// Podcast ad?
if ($podcast_ad != 0) {
  $qp = "SELECT approved_manuscript FROM pod_ads WHERE id='$podcast_ad' AND ad_id='$adID' AND customer_user='$userid'";
  $rp = mysqli_query ($dbc, $qp);
  $rows = mysqli_num_rows($rp);
  if ($rows == 1) {
    $row = mysqli_fetch_array($rp, MYSQLI_NUM);
    $approved_manuscript = "$row[0]";
    $new_ad_pod_listing = 'pod';
    $podcast_ad_rerun = true;
  }
}

// Get the time to check expired ads
$timeNow = date("Y-m-d H:i:s");
$timeNowEpoch = strtotime($timeNow);
$weekLaterEpoch = $timeNowEpoch + (60 * 60 * 24 * 7);

// Put our new start date into the future
$old_ad_date_expires_epoch = strtotime($old_ad_date_expires);
$new_ad_date_starts_epoch = $old_ad_date_expires_epoch + (60 * 60 * 24 * 7);
if ($new_ad_date_starts_epoch > $weekLaterEpoch) {
  $new_ad_date_starts = date("Y-m-d H:i:s", substr($new_ad_date_starts_epoch, 0, 10));
} else {
  $new_ad_date_starts = $weekLaterEpoch;
}

// Tags
$arrayTags = array();
$arrayTagIDs = array();
$arrayTagIDs = explode(',', $new_ad_tagIDs);
  foreach($arrayTagIDs as $tagID){
    // Get the tag from its ID
    $tq = "SELECT tag FROM tags WHERE id='$tagID'";
    $tr = mysqli_query ($dbc, $tq);
    $trow = mysqli_fetch_array($tr, MYSQLI_NUM);
    $tag = "$trow[0]";

    $arrayTags[] = $tag;
  }
$new_ad_tagList = implode(", ", $arrayTags);

// Category
$q = "SELECT slug FROM categories WHERE id='$catID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$new_ad_subcat="$row[0]";
$cat = "$row[0]";

// Role
$q = "SELECT slug FROM roles WHERE id='$roleID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$new_ad_subcat="$row[0]";
$rol = "$row[0]";

// Subcategory
$q = "SELECT slug FROM sub_$cat WHERE id='$subcatID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$new_ad_subcat = "$row[0]";

// Start after
$after_ad_date_to_start = 'cloned';

// Set the _SESSION
$_SESSION['cat'] = $cat;
$_SESSION['rol'] = $rol;
$_SESSION['catID'] = $catID;
$_SESSION['subcatID'] = $subcatID;
$_SESSION['roleID'] = $roleID;
$_SESSION['new_ad_subcat'] = $new_ad_subcat;
$_SESSION['new_ad_comment'] = $new_ad_comment;
$_SESSION['new_ad_nickname'] = $new_ad_nickname;
$_SESSION['old_ad_nickname'] = $old_ad_nickname;
$_SESSION['new_ad_heading'] = $new_ad_heading;
$_SESSION['new_ad_description'] = $new_ad_description;
$_SESSION['new_ad_info'] = $new_ad_info;
$_SESSION['new_ad_pricing'] = $new_ad_pricing;
$_SESSION['new_ad_contactURL'] = $new_ad_contactURL;
$_SESSION['new_ad_content_bizn'] = $new_ad_content_bizn;
$_SESSION['new_ad_biz_listing'] = $new_ad_biz_listing;
$_SESSION['new_ad_pod_listing'] = $new_ad_pod_listing;
$_SESSION['new_ad_content_pdcst'] = $new_ad_content_pdcst;
$_SESSION['podcast_ad_rerun'] = $podcast_ad_rerun;
$_SESSION['podcast_rerun_id'] = $podcast_ad;
$_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
$_SESSION['new_ad_tagList'] = $new_ad_tagList;
$_SESSION['old_ad_date_expires'] = $old_ad_date_expires;
$_SESSION['old_ad_date_starts'] = $old_ad_date_starts;
$_SESSION['new_ad_date_expires'] = $new_ad_date_expires;
$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;
$_SESSION['new_ad_weekslong'] = $new_ad_weekslong;
$_SESSION['new_ad_lang'] = $new_ad_lang;
$_SESSION['after_ad_date_to_start'] = $after_ad_date_to_start;
$_SESSION['modified_yn'] = $modified_yn;
