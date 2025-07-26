<?php

// must me set AFTER stripe.inc.php: $userid, $catID, $subcatID, $roleID, $sql_new_ad_tagIDs, $sql_new_ad_nickname, $sql_new_ad_heading, $sql_new_ad_description, $sql_new_ad_info, $sql_new_ad_pricing, $sql_new_ad_contactURL, $new_ad_weekslong, $adPrice, $discount, $adPricePaying, $token

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
/* NO! This messes stuff up, confusing the number of original weeks for reruns, pending, etc
if (isset($_SESSION['new_ad_boosted_weekslong'])) {
  $new_ad_weeks_duration = $_SESSION['new_ad_boosted_weekslong'];
} else {
  $new_ad_weeks_duration = $new_ad_weekslong;
}
*/

// ad_weekslong
$new_ad_weeks_duration = $new_ad_weekslong;

// Finish a pending ad
if (isset($_SESSION['finish'])) {
  $finishing_ad_id = $_SESSION['finish'];
  // MySQL escape values
  $sql_new_ad_tagIDs = mysqli_real_escape_string($dbc, $new_ad_tagIDs);
  $sql_new_ad_nickname = mysqli_real_escape_string($dbc, $new_ad_nickname);
  $sql_new_ad_comment = mysqli_real_escape_string($dbc, $new_ad_comment);
  $sql_new_ad_heading = mysqli_real_escape_string($dbc, $new_ad_heading);
  $sql_new_ad_description = mysqli_real_escape_string($dbc, $new_ad_description);
  $sql_new_ad_info = mysqli_real_escape_string($dbc, $new_ad_info);
  $sql_new_ad_content_bizn = mysqli_real_escape_string($dbc, $new_ad_content_bizn);
  $sql_new_ad_pricing = mysqli_real_escape_string($dbc, $new_ad_pricing);
  $sql_new_ad_contactURL = mysqli_real_escape_string($dbc, $new_ad_contactURL);
  $sql_new_ad_receipt_email = mysqli_real_escape_string($dbc, $new_ad_receipt_email);
  $q = "UPDATE ads SET category_id='$catID', subcat_id='$subcatID', role_id='$roleID', tag_ids='$sql_new_ad_tagIDs', ad_nickname='$sql_new_ad_nickname',
   ad_comment='$sql_new_ad_comment', ad_content_hdng='$sql_new_ad_heading', ad_content_dscr='$sql_new_ad_description',  ad_content_info='$sql_new_ad_info',
 ad_content_pyrt='$sql_new_ad_pricing', ad_content_cntc='$sql_new_ad_contactURL', ad_content_bizn='$sql_new_ad_content_bizn', ad_biz_listing='$new_ad_biz_listing',
  ad_weekslong='$new_ad_weeks_duration', date_starts='$new_ad_date_starts', chase_ad_id=$new_chase_ad_id, rerun_id=$rerun_id, rerun_how='$rerun_how',
   base_price='$adPrice', discount='$discount', price_total='$adPricePaying', receipt_email='$sql_new_ad_receipt_email', transaction_id='$token'
    WHERE id='$finishing_ad_id'";
  $r = mysqli_query ($dbc, $q);
  if ((isset($new_ad_content_pdcst)) && ($new_ad_content_pdcst != NULL)) {
    $sql_new_ad_content_pdcst = mysqli_real_escape_string($dbc, $new_ad_content_pdcst);
    $qp = "UPDATE pod_ads SET original_manuscript='$sql_new_ad_content_pdcst', date_starts='$new_ad_date_starts'
      WHERE ad_id='$finishing_ad_id'";
    $rp = mysqli_query ($dbc, $qp);
  } else {
    $rp = true; // for the test below
  }
  if (($r) && ($rp)) { // DEV is this right? Not "mysqli_affected_rows" because there might be no changes, just check to see if it is okay
    $_SESSION['ad_pending_to_sql'] = true;
    unset ($_SESSION['finish']);
  } else {
    sql_error($q.' :: '.$qp, 'dbc :: dbc', "sqle_170");
  }
} else {

  // New ad
  // Remove any dup rerun
  $qc = "DELETE FROM ads WHERE pub_status='pending' AND rerun_id=$rerun_id AND date_starts='$new_ad_date_starts'";
  $rc = mysqli_query ($dbc, $qc);

  // MySQL escape values
    $sql_new_ad_tagIDs = mysqli_real_escape_string($dbc, $new_ad_tagIDs);
    $sql_new_ad_nickname = mysqli_real_escape_string($dbc, $new_ad_nickname);
    $sql_new_ad_comment = mysqli_real_escape_string($dbc, $new_ad_comment);
    $sql_new_ad_heading = mysqli_real_escape_string($dbc, $new_ad_heading);
    $sql_new_ad_description = mysqli_real_escape_string($dbc, $new_ad_description);
    $sql_new_ad_info = mysqli_real_escape_string($dbc, $new_ad_info);
    $sql_new_ad_content_bizn = mysqli_real_escape_string($dbc, $new_ad_content_bizn);
    $sql_new_ad_pricing = mysqli_real_escape_string($dbc, $new_ad_pricing);
    $sql_new_ad_contactURL = mysqli_real_escape_string($dbc, $new_ad_contactURL);
    $sql_new_ad_receipt_email = mysqli_real_escape_string($dbc, $new_ad_receipt_email);

  // Podcast ad?
  if ((isset($new_ad_content_pdcst)) && ($new_ad_content_pdcst != NULL)) {
    $sql_new_ad_content_pdcst = mysqli_real_escape_string($dbc, $new_ad_content_pdcst);
    if (isset($_SESSION['podcast_ad_rerun']) && (isset($_SESSION['podcast_rerun_id']))) {
      $podcast_rerun_id = $_SESSION['podcast_rerun_id'];
      $qo = "SELECT editor_user, voice_user, publisher_user, approved_manuscript, length, duration FROM pod_ads WHERE id='$podcast_rerun_id'";
      $ro = mysqli_query ($dbc, $qo);
      $rows = mysqli_num_rows($ro);
      if ($rows == 1) {
        $row = mysqli_fetch_array($ro, MYSQLI_NUM);
        $editor_user = "$row[0]";
        $voice_user = "$row[1]";
        $publisher_user = "$row[2]";
        $approved_manuscript = "$row[3]";
        $length = "$row[4]";
        $duration = "$row[5]";
      }
      $qp = "INSERT INTO pod_ads (status, customer_user, editor_user, voice_user, publisher_user, approved_manuscript, length, duration, date_starts, rerun_pod_id) VALUES ('approved', '$userid', '$editor_user', '$voice_user', '$publisher_user', '$sql_new_ad_content_pdcst', '$length', '$duration', '$new_ad_date_starts', '$podcast_rerun_id')";
    } else {
      $qp = "INSERT INTO pod_ads (status, customer_user, original_manuscript, date_starts) VALUES ('pending', '$userid', '$sql_new_ad_content_pdcst', '$new_ad_date_starts')";
    }
    $rp = mysqli_query ($dbc, $qp);
    if (mysqli_affected_rows($dbc) == 1) {
      $_SESSION['ad_pending_to_sql'] = true;
    } else {
      sql_error($qp, 'dbc', "sqle_139");
    }

    // Last ID
    $pod_ad_id = mysqli_insert_id($dbc);

  }

  // Normal/business ad
  $pod_ad_id = (isset($pod_ad_id)) ? $pod_ad_id : 0;
  $q = "INSERT INTO ads (pub_status, user_id, category_id, subcat_id, role_id, tag_ids, ad_nickname, ad_comment, ad_content_hdng, ad_content_dscr, ad_content_info, ad_content_pyrt, ad_content_cntc, ad_content_bizn, ad_biz_listing, ad_weekslong, date_starts, chase_ad_id, rerun_id, rerun_how, base_price, discount, price_total, receipt_email, transaction_id, podcast_ad)
   VALUES ('pending', '$userid', '$catID', '$subcatID', '$roleID', '$sql_new_ad_tagIDs', '$sql_new_ad_nickname', '$sql_new_ad_comment', '$sql_new_ad_heading', '$sql_new_ad_description', '$sql_new_ad_info', '$sql_new_ad_pricing', '$sql_new_ad_contactURL', '$sql_new_ad_content_bizn', '$new_ad_biz_listing', '$new_ad_weeks_duration', '$new_ad_date_starts',
   $new_chase_ad_id, $rerun_id, '$rerun_how', '$adPrice', '$discount', '$adPricePaying', '$sql_new_ad_receipt_email', '$token', '$pod_ad_id')";
  $r = mysqli_query ($dbc, $q);
  if (mysqli_affected_rows($dbc) == 1) {
    $_SESSION['ad_pending_to_sql'] = true;
  } else {
    sql_error($q, 'dbc', "sqle_89");
  }

  // Put the ads ID in the pod_ads table
  if ($pod_ad_id != 0) {

    // Last ID
    $ads_id = mysqli_insert_id($dbc);

    // Update
    $qu = "UPDATE pod_ads SET ad_id='$ads_id' WHERE id='$pod_ad_id'";
    $ru = mysqli_query ($dbc, $qu);
    if (mysqli_affected_rows($dbc) == 1) {
      $_SESSION['ad_paid_to_sql'] = true;
    } else {
      sql_error($qu, 'dbc', "sqle_140");
    }

  }
}
