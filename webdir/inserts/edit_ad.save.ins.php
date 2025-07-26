<?php

// Save the ad if so set
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['edited'])) && ($_POST['edited'] == 'edited')) {

  // MySQL escape values
  $new_ad_tagIDs = mysqli_real_escape_string($dbc, $new_ad_tagIDs);
  $new_ad_nickname = mysqli_real_escape_string($dbc, $new_ad_nickname);
  $new_ad_comment = mysqli_real_escape_string($dbc, $new_ad_comment);
  $new_ad_heading = mysqli_real_escape_string($dbc, $new_ad_heading);
  $new_ad_description = mysqli_real_escape_string($dbc, $new_ad_description);
  $new_ad_info = mysqli_real_escape_string($dbc, $new_ad_info);
  $new_ad_content_bizn = mysqli_real_escape_string($dbc, $new_ad_content_bizn);
  $new_ad_biz_listing = mysqli_real_escape_string($dbc, $new_ad_biz_listing);
  $new_ad_pricing = mysqli_real_escape_string($dbc, $new_ad_pricing);
  $new_ad_contactURL = mysqli_real_escape_string($dbc, $new_ad_contactURL);

  // Update the current ad
  $q = "UPDATE ads SET category_id='$catID', subcat_id='$subcatID', role_id='$roleID', tag_ids='$new_ad_tagIDs', ad_lang='$new_ad_lang', ad_nickname='$new_ad_nickname',
  ad_comment='$new_ad_comment', ad_content_hdng='$new_ad_heading', ad_content_dscr='$new_ad_description',  ad_content_info='$new_ad_info', ad_content_pyrt='$new_ad_pricing',
  ad_content_cntc='$new_ad_contactURL', ad_content_bizn='$new_ad_content_bizn', ad_biz_listing='$new_ad_biz_listing', date_starts='$new_ad_date_starts' WHERE id='$adID' AND user_id='$userid'";
  $r = mysqli_query ($dbc, $q);
  if ($r === false) { // Simple check for failure without requiring "affected rows"
    sql_error($q, 'dbc', "sqle_38");
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

  // Listing the ads needs the _SRV config
  require_once ('./includes/config_srv.inc.php');
  require_once (MYSQL_SRV);

  // Get the global subcat ID
  $q = "SELECT id FROM global_subcat_ids WHERE cat_id='$catID' AND subcat_id='$subcatID'";
  $r = mysqli_query ($srv_dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $global_subcat_ID = "$row[0]";

  // Epoch Times
  $staEpoch = strtotime($new_ad_date_starts);

  // Update the listed ad
  //if ($new_ad_biz_listing == 'non') { // Maybe these checks aren't necessary because we are inserting the empty values anyway
		//$q = "UPDATE listads global_subcat_id='$global_subcat_ID', epoch_starts='$staEpoch', ad_lang='$new_ad_lang', ad_content_hdng='$new_ad_heading', ad_content_dscr='$new_ad_description', ad_content_info='$new_ad_info', ad_content_pyrt='$new_ad_pricing', ad_content_cntc='$new_ad_contactURL' WHERE ad_id='$adID'";
	//} elseif ($new_ad_biz_listing == 'biz') {
		$ql = "UPDATE listads SET global_subcat_id='$global_subcat_ID', epoch_starts='$staEpoch', ad_lang='$new_ad_lang', ad_content_hdng='$new_ad_heading', ad_content_dscr='$new_ad_description', ad_content_info='$new_ad_info', ad_content_pyrt='$new_ad_pricing', ad_content_cntc='$new_ad_contactURL', ad_content_bizn='$new_ad_content_bizn', ad_biz_listing='$new_ad_biz_listing' WHERE ad_id='$adID'";
	//}
  $rl = mysqli_query ($srv_dbc, $ql);
  if ($rl !== false) { // Best check for "not fail", no affected rows required
    $save_message = "<p class=\"note_green\">Ad updated!</p><p class=\"note_gray\">You may make further changes or return to <a title=\"Order History\" href=\"order_history.php\">Order History</a>.</p>";
    $save_success = true;
  } else {
    sql_error($ql, 'srv_dbc', "sqle_39");
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
