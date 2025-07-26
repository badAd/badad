<?php

// must me set AFTER stripe.inc.php: $userID, $catID, $subcatID, $roleID, $new_ad_tagIDs, $new_ad_nickname, $new_ad_heading, $new_ad_description, $new_ad_info, $new_ad_pricing, $new_ad_contactURL, $new_ad_weekslong, $adPrice, $discount, $adPricePaying, $token

// Get the time
$timeNow = date("Y-m-d H:i:s");
// Set the latest date possible
if ($new_ad_date_starts < $timeNow) {
  $new_ad_date_starts = $timeNow;
}

// Clones?
if ((!isset($_SESSION['rerun_how'])) && (!isset($_SESSION['rerun_id']))) {
  $rerun_how = 'Original';
  $rerun_id = 'NULL'; // We will use this variable in the sql query without quotes because it is INT
} else {
  $rerun_how = $_SESSION['rerun_how'];
  $rerun_id = $_SESSION['rerun_id'];
}

// Chasing?
if (isset($_SESSION['new_chase_ad_id'])) {
  $new_chase_ad_id = $_SESSION['new_chase_ad_id'];
} else {
  $new_chase_ad_id = 'NULL'; // We will use this variable in the sql query without quotes because it is INT
}

// Beta boost?
if (isset($_SESSION['new_ad_boosted_weekslong'])) {
  $new_ad_weeks_duration = $_SESSION['new_ad_boosted_weekslong'];
} else {
  $new_ad_weeks_duration = $new_ad_weekslong;
}

// Finish a pending ad
if (isset($_SESSION['finish'])) {
  $finishing_ad_id = $_SESSION['finish'];
  // MySQL escape values
  $new_ad_tagIDs = mysqli_real_escape_string($dbc, $new_ad_tagIDs);
  $new_ad_nickname = mysqli_real_escape_string($dbc, $new_ad_nickname);
  $new_ad_comment = mysqli_real_escape_string($dbc, $new_ad_comment);
  $new_ad_heading = mysqli_real_escape_string($dbc, $new_ad_heading);
  $new_ad_description = mysqli_real_escape_string($dbc, $new_ad_description);
  $new_ad_info = mysqli_real_escape_string($dbc, $new_ad_info);
  $new_ad_content_bizn = mysqli_real_escape_string($dbc, $new_ad_content_bizn);
  $new_ad_pricing = mysqli_real_escape_string($dbc, $new_ad_pricing);
  $new_ad_contactURL = mysqli_real_escape_string($dbc, $new_ad_contactURL);
  $new_ad_receipt_email = mysqli_real_escape_string($dbc, $new_ad_receipt_email);
  $q = "UPDATE ads SET category_id='$catID', subcat_id='$subcatID', role_id='$roleID', tag_ids='$new_ad_tagIDs', ad_nickname='$new_ad_nickname',
   ad_comment='$new_ad_comment', ad_content_hdng='$new_ad_heading', ad_content_dscr='$new_ad_description',  ad_content_info='$new_ad_info',
 ad_content_pyrt='$new_ad_pricing', ad_content_cntc='$new_ad_contactURL', ad_content_bizn='$new_ad_content_bizn', ad_biz_listing='$new_ad_biz_listing',
  ad_weekslong='$new_ad_weeks_duration', date_starts='$new_ad_date_starts', chase_ad_id=$new_chase_ad_id, rerun_id=$rerun_id, rerun_how='$rerun_how',
   base_price='$adPrice', discount='$discount', price_total='$adPricePaying', receipt_email='$new_ad_receipt_email', transaction_id='$token'
    WHERE id='$finishing_ad_id'";
  $r = mysqli_query ($dbc, $q);
  if ($r) { // DEV is this right? Not "mysqli_affected_rows" because there might be no changes, just check to see if it is okay
    $_SESSION['ad_pending_to_sql'] = true;
    unset ($_SESSION['finish']);
  }
} else {

  // New ad
  // Remove any dup rerun
  $qc = "DELETE FROM ads WHERE pub_status='pending' AND rerun_id=$rerun_id AND date_starts='$new_ad_date_starts'";
  $rc = mysqli_query ($dbc, $qc);

  // MySQL escape values
    $new_ad_tagIDs = mysqli_real_escape_string($dbc, $new_ad_tagIDs);
    $new_ad_nickname = mysqli_real_escape_string($dbc, $new_ad_nickname);
    $new_ad_comment = mysqli_real_escape_string($dbc, $new_ad_comment);
    $new_ad_heading = mysqli_real_escape_string($dbc, $new_ad_heading);
    $new_ad_description = mysqli_real_escape_string($dbc, $new_ad_description);
    $new_ad_info = mysqli_real_escape_string($dbc, $new_ad_info);
    $new_ad_content_bizn = mysqli_real_escape_string($dbc, $new_ad_content_bizn);
    $new_ad_pricing = mysqli_real_escape_string($dbc, $new_ad_pricing);
    $new_ad_contactURL = mysqli_real_escape_string($dbc, $new_ad_contactURL);
    $new_ad_receipt_email = mysqli_real_escape_string($dbc, $new_ad_receipt_email);
  $q = "INSERT INTO ads (pub_status, user_id, category_id, subcat_id, role_id, tag_ids, ad_nickname, ad_comment, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_weekslong, date_starts, chase_ad_id, rerun_id, rerun_how, base_price, discount, price_total, receipt_email, transaction_id)
   VALUES ('pending', '$userID', '$catID', '$subcatID', '$roleID', '$new_ad_tagIDs', '$new_ad_nickname', '$new_ad_comment', '$new_ad_heading', '$new_ad_description', '$new_ad_info', '$new_ad_pricing', '$new_ad_contactURL', '$new_ad_content_bizn', '$new_ad_biz_listing', '$new_ad_weeks_duration', '$new_ad_date_starts',
   $new_chase_ad_id, $rerun_id, '$rerun_how', '$adPrice', '$discount', '$adPricePaying', '$new_ad_receipt_email', '$token')";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) {
    $_SESSION['ad_pending_to_sql'] = true;
  } else {
    sql_error($q, 'dbc', "sqle_89");
  }
}
