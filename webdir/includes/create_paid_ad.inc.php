<?php

// This updates an ad created by create_pending_ad.inc.php to "live" status with current times

// Get the time
$timeNow = date("Y-m-d H:i:s");
// Set the latest date possible
if ($new_ad_date_starts < $timeNow) {
  $new_ad_date_starts = $timeNow;
}
$timeNowEpoch = strtotime($timeNow);
$resetEpoch = $timeNowEpoch + 604800;

// Beta boost?
if (isset($_SESSION['new_ad_boosted_weekslong'])) {
  $new_ad_weeks_duration = $_SESSION['new_ad_boosted_weekslong'];
} else {
  $new_ad_weeks_duration = $new_ad_weekslong;
}

// Calculate the $new_ad_date_expires
//$days = $new_ad_weeks_duration * 7; // depreciated with old SQL: date_expires=DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -$days DAY)
$new_ad_date_starts_epoch = strtotime($new_ad_date_starts);
$new_ad_date_expires_epoch = $new_ad_date_starts_epoch + (60 * 60 * 24 * 7 * $new_ad_weeks_duration);
$new_ad_date_expires = date("Y-m-d H:i:s", substr($new_ad_date_expires_epoch, 0, 10));

// Update the pending ad
$q = "UPDATE ads SET pub_status='live', date_created=NOW(), date_starts='$new_ad_date_starts', date_expires='$new_ad_date_expires', epoch_wk_reset='$resetEpoch', receipt_email='$new_ad_receipt_email', statement_description='$stripeDescription', receipt_url='$stripeReceiptURL', transaction_id='$stripePaymentID', paid_amount='$stripePaymentAmt', payment_status='PAID', payment_date_time=CURRENT_TIMESTAMP WHERE id='$adID'";
$r = mysqli_query ($dbc, $q);
if (mysqli_affected_rows($dbc) == 1) {
  $_SESSION['ad_paid_to_sql'] = true;
} else {
  sql_error($q, 'dbc', "sqle_90");
}

// Update any podcast ads
$q = "UPDATE pod_ads SET status='inreview', date_modified=CURRENT_TIMESTAMP WHERE status='pending' AND ad_id='$adID'";
if ($r = mysqli_query ($dbc, $q)) {

  if (mysqli_affected_rows($dbc) == 1) {
        // Send an email to team
        // Get the new user's ID from the database for the email
        $q = "SELECT id FROM users WHERE email='$email'";
        $r = mysqli_query ($dbc, $q);
        $row = mysqli_fetch_array($r, MYSQLI_NUM);
        $userid = "$row[0]";

        // Send an email
        $payloadlink = "https://$siteDomain/editor.php";
        $emailUserID = $userid;
        $canned_email = "podad_new"; // Slug from the "pantry" table to select the canned email
        $subject_suffix = ": $siteTitle"; // Appends the canned email Subject
        $payload_content = "<p><a href=\"$payloadlink\">View manuscript queue in \"Editing &amp; Review\"</a>.</p>"; // Middle of the Body, after the canned email and before the salutation
        $footer_link_content = ""; // After the salutation and before the unsubscribe footer
        include ('./includes/sendusrmail.inc.php');
  }

} else {
  sql_error($q, 'dbc', "sqle_141");
}

// Remove any dup pending
$qd = "DELETE FROM ads WHERE (pub_status='pending' AND user_id='$userid') AND (ad_content_hdng='$sql_new_ad_heading' OR ad_content_cntc='$sql_new_ad_contactURL' OR ad_content_bizn='$sql_new_ad_content_bizn')";
$rd = mysqli_query ($dbc, $qd);
