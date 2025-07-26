<?php

// Clear any old SESSION form values & get out if we don't belong here
if ((!isset($_SESSION['rerun_id'])) || (!isset($_SESSION['user_id'])) || (!isset($_SESSION['new_ad_date_starts']))) {
  // Clear any old SESSION form values
  include ('./includes/ad_values_unset.inc.php');

  // Get out of Dodge
  header("Location: index.php");
	exit(); // Quit the script
} else {

  // Role
  $q = "SELECT role FROM roles WHERE id='$roleID'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $roleName = "$row[0]"; // $_POST['ctgr']

  // Retrieve the Category ID, price & name
  $catID = $_SESSION['catID'];
  $q = "SELECT category, price, bizn_price, pdcst_renew FROM categories WHERE id='$catID'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $categoryName = "$row[0]";
  $categoryPrice = "$row[1]";
  $bizPerWeekPrice = "$row[2]";
  $podRenewPrice = "$row[3]";

  $_SESSION['categoryName'] = $categoryName;
  $_SESSION['categoryPrice'] = $categoryPrice;
  $_SESSION['bizPerWeekPrice'] = $bizPerWeekPrice;
  $_SESSION['podRenewPrice'] = $podRenewPrice;

  // SubCat ID & pretty name
  $q = "SELECT id, subcat FROM sub_$cat WHERE slug='$new_ad_subcat'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $subcatID = "$row[0]";
  $subcatName = "$row[1]";
  $_SESSION['subcatID'] = $subcatID;
  $_SESSION['subcatName'] = $subcatName;

  $new_ad_biz_listing = $_SESSION['new_ad_biz_listing'];
  $new_ad_pod_listing = $_SESSION['new_ad_pod_listing'];
  $new_ad_content_pdcst = $_SESSION['new_ad_content_pdcst'];
    if (($new_ad_pod_listing == 'pod') || ($new_ad_biz_listing == 'biz')) {
   $adPricePerWeek = ($new_ad_pod_listing == 'pod') ? $podRenewPrice : $bizPerWeekPrice;
   $adPodcastPrice = 0;
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
  if ((isset($_POST['weekslong'])) && (preg_match ('/[^a-zA-Z0-9]$/i', $_POST['weekslong']))) {$IP = get_ip_addr(); script_kiddy('sk_14', '_POST weekslong', $_POST['weekslong'], $IP);}
  if ((isset($_POST['weekslong'])) && (filter_var($_POST['weekslong'], FILTER_VALIDATE_INT, array('min_range' => 1)))) {
    $new_ad_weekslong = preg_replace("/[^A-Za-z0-9]/","", $_POST['weekslong']);
    $_SESSION['new_ad_weekslong'] = $new_ad_weekslong;
  }

  // Price
  $wkly_price = $adPricePerWeek;
  $adCorePrice = abs($new_ad_weekslong*$wkly_price);
    // Check if decimals were removed
    if (strpos($adCorePrice, ".") == false) { $adCorePrice = $adCorePrice.'.00'; }
  $adPrice = $adCorePrice;
  $_SESSION['adPrice'] = $adPrice;

}
