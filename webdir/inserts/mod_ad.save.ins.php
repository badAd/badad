<?php

// Save the ad if so set
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['modified'])) && ($_POST['modified'] == 'modified')) {

  // Update the old ad to expire at the new ad time
  $q = "UPDATE ads SET date_expires='$new_ad_date_starts', modified_yn='Modified' WHERE id='$rerun_id' AND user_id='$userid'";
  $r = mysqli_query ($dbc, $q);
  if ($r === false) { // Simple check for failure without requiring "affected rows"
    sql_error($q, 'dbc', "sqle_40");
  }

  // Get some user information
  $qu = "SELECT name, email, confirmed_email FROM users WHERE id='$userid'";
  $ru = mysqli_query ($dbc, $qu);
  $row = mysqli_fetch_array($ru, MYSQLI_NUM);
  $userName = "$row[0]";
  $userEmail = "$row[1]";
  $confirmed_email = "$row[2]";
  if (($confirmed_email == $userEmail) || ($confirmed_email == 'Unconfirmed')) {
    $new_ad_receipt_email = $confirmed_email;
    $toString = '"'.$userName.'" <'.$userEmail.'>';
  } else {
    $new_ad_receipt_email = $userEmail;
    $toString = '"'.$userName.'" <'.$userEmail.'>, "'.$userName.'" <'.$confirmed_email.'>';
  }

  // Get the time
  $timeNow = date("Y-m-d H:i:s");
  // Epoch week reset
  $timeNowEpoch = strtotime($timeNow);
  $resetEpoch = $timeNowEpoch + 604800;

  // MySQL escape values
  $new_ad_tagIDs = mysqli_real_escape_string($dbc, $new_ad_tagIDs);
  $new_ad_nickname = mysqli_real_escape_string($dbc, $new_ad_nickname);
  $new_ad_comment = mysqli_real_escape_string($dbc, $new_ad_comment);
  $new_ad_heading = mysqli_real_escape_string($dbc, $new_ad_heading);
  $new_ad_description = mysqli_real_escape_string($dbc, $new_ad_description);
  $new_ad_info = mysqli_real_escape_string($dbc, $new_ad_info);
  $new_ad_pricing = mysqli_real_escape_string($dbc, $new_ad_pricing);
  $new_ad_contactURL = mysqli_real_escape_string($dbc, $new_ad_contactURL);
  $new_ad_content_bizn = mysqli_real_escape_string($dbc, $new_ad_content_bizn);
  $new_ad_biz_listing = mysqli_real_escape_string($dbc, $new_ad_biz_listing);
  $new_ad_receipt_email = mysqli_real_escape_string($dbc, $new_ad_receipt_email);

  // Insert the new ad to expire at the new ad time
  $q = "INSERT INTO ads (pub_status, user_id, category_id, subcat_id, role_id, tag_ids, ad_nickname, ad_comment, ad_content_hdng,
  ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_weekslong, date_starts, date_expires, epoch_wk_reset, rerun_id, rerun_how, receipt_email)
  VALUES ('live', '$userid', '$catID', '$subcatID', '$roleID', '$new_ad_tagIDs', '$new_ad_nickname', '$new_ad_comment',
  '$new_ad_heading', '$new_ad_description', '$new_ad_info', '$new_ad_pricing', '$new_ad_contactURL', '$new_ad_content_bizn', '$new_ad_biz_listing', NULL,
  '$new_ad_date_starts', '$new_ad_date_expires', '$resetEpoch', '$rerun_id', '$rerun_how', '$new_ad_receipt_email')";
  $r = mysqli_query ($dbc, $q);
  if ($r === false) { // Simple check for failure without requiring "affected rows"
    sql_error($q, 'dbc', "sqle_41");
  }
  // Get the last id INSERTed, similar to SCOPE_IDENTITY() but with MySQLi
  $new_mod_ad_id = $dbc->insert_id;

  // Listing the ads needs the _SRV config
  require_once ('./includes/config_srv.inc.php');
  require_once (MYSQL_SRV);

  // Get the global subcat ID
  $q = "SELECT id FROM global_subcat_ids WHERE cat_id='$catID' AND subcat_id='$subcatID'";
  $r = mysqli_query ($srv_dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $global_subcat_ID = "$row[0]";

  // Epoch Times
  $endEpoch = strtotime($new_ad_date_expires);

  // Update the listed ad
	$ql = "UPDATE listads SET epoch_dead='$endEpoch' WHERE ad_id='$rerun_id'";
  $rl = mysqli_query ($srv_dbc, $ql);
  if ($rl !== false) { // Best check for "not fail", no affected rows required
    $save_message = "<p class=\"note_green\">Ad updated!</p><p class=\"note_gray\">The changes will be listed as the new ad \"$new_ad_nickname\" in <a title=\"Order History\" href=\"order_history.php\">Order History</a> and will go into effect: <i><b>$new_ad_date_expires</b></i>. You have not been charged.</p>";
    $save_success = true;

    // List the Modified ad
    $adID = $new_mod_ad_id;
    include ('./includes/list_ad.inc.php');
  } else {
    $save_message = "<p class=\"note_red\">Small database error updating your ad! Try again later.</p>";
    return; // Quit the script
  }
}

// Send an email
if ((isset($save_success)) && ($save_success == true)) {

  // Send the email
  $canned_email = "ad_changed"; // Slug from the "pantry" table to select the canned email
  $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
  $payload_content = "<p>Your ad \"$old_ad_nickname\" was modified as \"$new_ad_nickname\" and the changes go into effect $new_ad_date_starts Eastern.</p>"; // Middle of the Body, after the canned email and before the salutation
  $footer_link_content = ""; // After the salutation and before the unsubscribe footer
  include ('./includes/sendusrmail.inc.php');

  $save_message = $save_message."<p class=\"note_gray\">An email has been sent to you about this.</p>";
}
