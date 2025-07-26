<?php

// These must be set: $new_ad_weekslong, $sformaction
// This comes later to work with calculate_total.inc.php

// User ID
if (isset($_SESSION['user_id'])) {
	$userid = $_SESSION['user_id'];
} else {
	return;
}

// Require the database connection
require_once (MYSQL);

// Check to see if the user's email is confirmed
$q = "SELECT email, confirmed_email, join_rank FROM users WHERE id='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$email = "$row[0]";
$confirmed_email = "$row[1]";
$join_rank = "$row[2]";
if ($email == $confirmed_email) {
	$confirmedYN = "Confirmed";
} else {
	$confirmedYN = "Unconfirmed";
}
// Check to see if the user has made a purchase
$q = "SELECT COUNT(user_id) FROM ads WHERE user_id='$userid' AND price_total > 0";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array ($r, MYSQLI_NUM);
$countAds = $row[0];

if (($confirmedYN != "Confirmed") || ($join_rank == NULL)) {
  echo "<div class=\"inline left\"><p class=\"note_gray\">You must be registered, have your email address confirmed, and have purchased at least one ad before you may use credits to buy ads.";
  // email check
  if (($confirmedYN != "Confirmed") && ($join_rank != NULL)) {
    echo " <b><a class=\"note_gray\" title=\"Click to send a confirmation link to your email address\" href=\"confirm_email.php\">Send confirmation link</a></b>";
  }
  echo "</p></div>";
  return;
}

// See how many credits are available
$q = "SELECT creditcount FROM credits WHERE userid='$userid'";
$r = mysqli_query ($dbc, $q);
$row = mysqli_fetch_array($r, MYSQLI_NUM);
$creditsAvailable = "$row[0]";
$_SESSION['creditsAvailable'] = $creditsAvailable;

if ($creditsAvailable > 0) {
  // Can't use more credits than the ad itself
    if($creditsAvailable <= $new_ad_weekslong) {
      $maxCreditsUsable = $creditsAvailable;
  } else {
      $maxCreditsUsable = $new_ad_weekslong;
  }

  // Build the form for credits
  echo "<form action=\"$sformaction\" method=\"post\" accept-charset=\"utf-8\">";

    // Notify the user
    echo "<div class=\"info\">Credits available: <b>$creditsAvailable</b><br />";

    // Iterate the credits into a selector
    echo 'Credits used toward total: <select class="formselect" name="creditsUsing" required>';
      // Existing value?
      if (isset($_SESSION['creditsUsing'])) {
        $value = $_SESSION['creditsUsing'];
      } else {
        $value = '';
      }

      // Count to $creditsAvailable
      $count = 0;
      while ($count <= $maxCreditsUsable) {
        // Iterate each value as a select option
        if ($value == $count) {
          echo '<option value="'.$count.'" selected>'.$count.'</option>';
        } else {
          echo '<option value="'.$count.'">'.$count.'</option>';
        }

      $count++;
      }
    // End the select options
    echo '</select></div><br />';

		// Include the category slug so we don't trip the empty _POST check on new ads
		echo '<input type="hidden" name="ctgr" value="'.$cat.'" />';

	// Finish the form
  echo "<input type=\"submit\" name=\"submit_button\" class=\"formbutton_blue\" value=\"Apply &amp; recalculate &#x21ba;\" />
  </form>
  ";
} else {
  // Notify the user
  echo "<p>You have no ad credits available.</p>";

  // Referrals
  echo "<p>Complete at least one purchase, then <a title \"Visit your account information\" href=\"account_info.php\">refer friends</a> to earn some.</p>";

}
