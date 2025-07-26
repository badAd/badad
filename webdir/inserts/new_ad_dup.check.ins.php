<?php

// This runs Dup checks for non-_POST submission, such as trying to checkout a dup ad or accessing an ad from another tab in the _SESSION where a Dup check may otherwise be skipped

// For storing registration errors:
if (!isset($reg_errors)) {$reg_errors = array();}

// Get the time
$timeNow = date("Y-m-d H:i:s");

// Heading Dup
if (isset($new_ad_heading)) {
  $new_ad_heading_check = mysqli_real_escape_string ($dbc, $new_ad_heading);
  if (isset($allowed_dup)) {
    $q = "SELECT id FROM ads WHERE (date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_hdng='$new_ad_heading_check' AND category_id='$catID' AND subcat_id='$subcatID') AND NOT (id='$allowed_dup' OR rerun_id='$allowed_dup')";
  } else {
    $q = "SELECT id FROM ads WHERE date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_hdng='$new_ad_heading_check' AND category_id='$catID' AND subcat_id='$subcatID'";
  }
  $row = mysqli_query($dbc, $q);
    if (mysqli_num_rows($row) > 0) {
      $reg_errors['hdng'] = "<br /><b>That heading is already in use for this ad category.</b> Change the heading or change the categories.";
    }
}

// Contact URL Dup
if (isset($new_ad_contactURL)) {
  $new_ad_contactURL_check = mysqli_real_escape_string ($dbc, $new_ad_contactURL);
  if (isset($allowed_dup)) {
    $q = "SELECT id FROM ads WHERE (date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_cntc='$new_ad_contactURL_check' AND category_id='$catID' AND subcat_id='$subcatID') AND NOT (id='$allowed_dup' OR rerun_id='$allowed_dup')";
  } else {
    $q = "SELECT id FROM ads WHERE date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_cntc='$new_ad_contactURL_check' AND category_id='$catID' AND subcat_id='$subcatID'";
  }
  $row = mysqli_query($dbc, $q);
    if (mysqli_num_rows($row) > 0) {
      $reg_errors['cntc'] = "<br /><b>That URL is already in use for this ad category.</b> <i>You have some options!</i><br /><br />You may:<ol><li>1. Send visitors somewhere else, even to a page with 85% identical content,</li><li>2. Try a different category or subcategory for your ad,</li><li>3. Run an ad for a webstore and run separate ads that each link to a unique corresponding webstore product category page.</li></ol><i>(Careful, don't play around with this rule. A duplicate URL workaround, such as forwarding multiple ads to the same destination or information, could result in account removal, ad removal, and fee forfeiture. Our goal is to avoid being 'spammy'. This doesn't need to be a problem for you and we want to help your needs, so you can get more clarity in our Terms of Service.)</i>";
    }
}

// Business listing Dup
if (isset($new_ad_content_bizn)) {
  if ($new_ad_biz_listing == 'biz') {
    $new_ad_content_bizn_check = mysqli_real_escape_string ($dbc, $new_ad_content_bizn);
    if (isset($allowed_dup)) {
      $q = "SELECT id FROM ads WHERE (date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_bizn='$new_ad_content_bizn_check' AND category_id='$catID' AND subcat_id='$subcatID') AND NOT (id='$allowed_dup' OR rerun_id='$allowed_dup')";
    } else {
      $q = "SELECT id FROM ads WHERE date_expires > '$timeNow' AND date_starts < '$timeNow' AND ad_content_bizn='$new_ad_content_bizn_check' AND category_id='$catID' AND subcat_id='$subcatID'";
    }
    $row = mysqli_query($dbc, $q);
      if (mysqli_num_rows($row) > 0) {
        $reg_errors['bizn'] = "<b>You or someone else already has that business name listed for this category.</b> A brand, trademark, or DBA can only be used once per ad category. \"Killing\" an old ad running a new ad <i>WILL NOT</i> free up the name! <i>You have some options!</i><br /><br />Consider:<ol><li>1. If you want to run more than one ad for your business, but, say, for a different product category in your online store, you may use this format to make your \"business name\" unique: <i>\"My Legal ABC Business Name: Product Category\"</i>.</li><li>2. If this is your business and you need to change information, your business ad can be modified once every 8 weeks.</li><li>3. If you want more than one business listing, you must have more than one business/trade name to list. This is about law and intellectual property rights.</li></ol>";
      }
    }
}

if (!empty($reg_errors)) { // If reg_errors exist

  $_SESSION['validAd'] = false;

}
