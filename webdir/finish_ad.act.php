<?php

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect
redirect_invalid_user();

// User ID
$userid = $_SESSION['user_id'];

// _POST the ad ID
if ((isset($_POST['a'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['a']))) {$IP = get_ip_addr(); script_kiddy('sk_9', '_POST a', $_POST['a'], $IP);}
if ((isset($_POST['a'])) && (filter_var($_POST['a'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $adID = preg_replace("/[^A-Za-z0-9]/","", $_POST['a']);
} else {
  header("Location: index.php");
  exit(); // Quit the script
}

// Set the _SESSION as a finishing ad
$_SESSION['finish'] = $adID;

// Require the database connection
require (MYSQL);

// Get the ad
$adID = mysqli_real_escape_string ($dbc, $adID);
$q = "SELECT category_id, subcat_id, role_id, tag_ids, ad_nickname, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_weekslong, date_starts, chase_ad_id, podcast_ad FROM ads WHERE id='$adID' AND pub_status='pending'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$catID = "$row[0]";
$subcatID = "$row[1]";
$roleID = "$row[2]";
$new_ad_tagIDs = "$row[3]";
$new_ad_nickname = "$row[4]";
$new_ad_heading = "$row[5]";
$new_ad_description = "$row[6]";
$new_ad_info = "$row[7]";
$new_ad_pricing = "$row[8]";
$new_ad_contactURL = "$row[9]";
$new_ad_content_bizn = "$row[10]";
$new_ad_biz_listing = "$row[11]";
$new_ad_weekslong = "$row[12]";
$new_ad_date_starts = "$row[13]";
$new_chase_ad_id = "$row[14]";
$new_podcast_ad = "$row[15]";

// Chasing?
if ($new_chase_ad_id == NULL) {
  $after_ad_date_to_start = 'right_now';
} else {
  $after_ad_date_to_start = $new_chase_ad_id;
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


// Role
$q = "SELECT slug FROM roles WHERE id='$roleID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$rol = "$row[0]";

// Category, slug, price & name
$q = "SELECT category, slug, price, bizn_price, pdcst_price, pdcst_renew FROM categories WHERE id='$catID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$categoryName = "$row[0]";
$cat = "$row[1]";
$categoryPrice = "$row[2]";
$bizPerWeekPrice = "$row[3]";
$podNewAdPrice = "$row[4]";
$podRenewPrice = "$row[5]";

$_SESSION['categoryName'] = $categoryName;
$_SESSION['categoryPrice'] = $categoryPrice;
$_SESSION['bizPerWeekPrice'] = $bizPerWeekPrice;
$_SESSION['podNewAdPrice'] = $podNewAdPrice;
$_SESSION['podRenewPrice'] = $podRenewPrice;

// Podcast
if ($new_podcast_ad != 0) {
  $q = "SELECT original_manuscript FROM pod_ads WHERE id='$new_podcast_ad' AND ad_id='$adID'";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_num_rows($r) == 1) {

    $row = mysqli_fetch_array($r, MYSQLI_NUM);
    $new_ad_content_pdcst = "$row[0]";
    $_SESSION['new_ad_content_pdcst'] = $new_ad_content_pdcst;
    $new_ad_pod_listing = 'pod';
    
  } else {
    $new_ad_pod_listing = 'not';
  }
} else {
  $new_ad_pod_listing = 'not';
}

if (($new_ad_pod_listing == 'pod') || ($new_ad_biz_listing == 'biz')) {
  $adPricePerWeek = ($new_ad_pod_listing == 'pod') ? $podRenewPrice : $bizPerWeekPrice;
  $adPodcastPrice = ($new_ad_pod_listing == 'pod') ? $podNewAdPrice : 0;
} elseif ($new_ad_biz_listing == 'non') {
  $adPricePerWeek = $categoryPrice;
}

$_SESSION['adPricePerWeek'] = $adPricePerWeek;
$_SESSION['adPodcastPrice'] = $adPodcastPrice;

// Cleanup prices
if (strpos($categoryPrice, ".") == false) { $categoryPrice = $categoryPrice.'.00'; }
if (strpos($bizPerWeekPrice, ".") == false) { $bizPerWeekPrice = $bizPerWeekPrice.'.00'; }
if (strpos($podNewAdPrice, ".") == false) { $podNewAdPrice = $podNewAdPrice.'.00'; }
if (strpos($podRenewPrice, ".") == false) { $podRenewPrice = $podRenewPrice.'.00'; }
$podcstFirstWeek = number_format(((($podNewAdPrice * 100) + ($podRenewPrice * 100)) / 100), 2, '.', '');

// Weeks Long
if ((isset($_POST['weekslong'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['weekslong']))) {$IP = get_ip_addr(); script_kiddy('sk_10', '_POST weekslong', $_POST['weekslong'], $IP);}
if ((isset($_POST['weekslong'])) && (filter_var($_POST['weekslong'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
  $new_ad_weekslong = preg_replace("/[^A-Za-z0-9]/","", $_POST['weekslong']);
  $_SESSION['new_ad_weekslong'] = $new_ad_weekslong;
}

// Price
$wkly_price = $adPricePerWeek;
$base_price = $podNewAdPrice;
$adCorePrice = (($new_ad_pod_listing == 'pod') && ($base_price != 0)) ? ($base_price + abs($new_ad_weekslong*$wkly_price)) : abs($new_ad_weekslong*$wkly_price);

$adCorePrice = abs($new_ad_weekslong*$wkly_price);
  // Check if decimals were removed
  if (strpos($adCorePrice, ".") == false) { $adCorePrice = $adCorePrice.'.00'; }
$adPrice = $adCorePrice;
$_SESSION['adPrice'] = $adPrice;

// Subcategory
$cat = mysqli_real_escape_string ($dbc, $cat);
$q = "SELECT slug FROM sub_$cat WHERE id='$subcatID'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$new_ad_subcat = "$row[0]";

$_SESSION['cat'] = $cat;
$_SESSION['rol'] = $rol;
$_SESSION['catID'] = $catID;
$_SESSION['roleID'] = $roleID;
$_SESSION['roleName'] = $rol;
$_SESSION['subcatID'] = $subcatID;
$_SESSION['subcatName'] = $new_ad_subcat;
$_SESSION['new_ad_subcat'] = $new_ad_subcat;
$_SESSION['new_ad_nickname'] = $new_ad_nickname;
$_SESSION['new_ad_heading'] = $new_ad_heading;
$_SESSION['new_ad_description'] = $new_ad_description;
$_SESSION['new_ad_info'] = $new_ad_info;
$_SESSION['new_ad_pricing'] = $new_ad_pricing;
$_SESSION['new_ad_contactURL'] = $new_ad_contactURL;
$_SESSION['new_ad_tagIDs'] = $new_ad_tagIDs;
$_SESSION['new_ad_tagList'] = $new_ad_tagList;
$_SESSION['new_ad_content_bizn'] = $new_ad_content_bizn;
$_SESSION['new_ad_biz_listing'] = $new_ad_biz_listing;
$_SESSION['new_ad_weekslong'] = $new_ad_weekslong;
$_SESSION['new_ad_date_starts'] = $new_ad_date_starts;
$_SESSION['after_ad_date_to_start'] = $after_ad_date_to_start;
$_SESSION['new_ad_pod_listing'] = $new_ad_pod_listing;
$_SESSION['validAd'] = true;

header("Location: new_ad_cart.php");
exit(); // Quit the script

?>
